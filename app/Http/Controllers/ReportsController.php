<?php


namespace App\Http\Controllers;


use App\Models\AdAccount;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    const CUSTOMERS_TABLE = 'customers';
    const SUBSCRIPTIONS_TABLE = 'subscriptions';
    const ADS_ACCOUNTS_TABLE = 'ads_accounts';
    const FB_CAMPAIGNS_TABLE = 'facebook_campaigns';
    const AW_CAMPAIGNS_TABLE = 'adwords_campaigns';
    const FB_CAMPAIGNS_REPORTS_TABLE = 'facebook_campaigns_reports';
    const AW_CAMPAIGNS_REPORTS_TABLE = 'adwords_campaigns_reports';
    const BALANCE_TRANSACTIONS_TABLE = 'balance_transactions';
    const MARGIN = 1.3;

    public function multiReports(Request $request)
    {
        $start_date = $request->has('start_date') ? $request->get('start_date') : date("Y-m-d", strtotime("last week monday"));
        $end_date = $request->has('end_date') ? $request->get('end_date') : date("Y-m-d", strtotime("last sunday"));
        $page_title = '';
        $page_description = '';
        $customers_with_no_campaigns = $this->getCustomersWithNoCampaigns();
        $customers_with_paused_campaigns = $this->getCustomersWithPausedCampaigns();
        $campaigns_performance = $this->getCampaignsPerformance($start_date, $end_date);
        $last_week = $this->getLastWeek();

        return view('pages.datatables', compact(
            'page_title',
            'page_description',
            'customers_with_no_campaigns',
            'customers_with_paused_campaigns',
            'campaigns_performance',
            'last_week'
        ));
    }

    private function getCustomersWithNoCampaigns()
    {
        /*SELECT `di`.`user_id`, `di`.`name`, bl.balance FROM dealer_info di
                                            INNER JOIN users u ON u.id = di.user_id
                                            INNER JOIN v_rmk_balance bl ON bl.user_id = di.user_id
                                            WHERE bl.balance IS NOT NULL AND bl.balance > 0 AND u.status = 'active' AND u.free = 'no' AND u.tester = 'no'
                                            AND di.user_id NOT IN (SELECT user_id FROM user_outside_accounts WHERE service_name  IN ('ADWORDS','facebook_ads') AND is_active = '1'  GROUP BY user_id)*/
        $customers_with_no_campaigns_html = "NO DATA IN DATABASE";
        $reports = DB::select("SELECT u.id AS customer_id, CONCAT(u.first_name, u.last_name) AS name, u.balance FROM " . self::CUSTOMERS_TABLE . " u
                                    WHERE u.balance IS NOT NULL AND u.balance > 0 AND u.status = 'active'
                                    AND u.free = 'no' AND u.tester = 'no'
                                    AND u.id NOT IN (
                                    SELECT customer_id FROM " . self::ADS_ACCOUNTS_TABLE . "
                                    WHERE ad_service  IN ('" . AdAccount::SERVICE_ADWORDS . "','" . AdAccount::SERVICE_FACEBOOK_ADS . "')
                                    AND is_active = '1'
                                    GROUP BY customer_id
                                    )")->get();
        if (count($reports) > 0) {
            $balance = 0;
            foreach ($reports as $report) {
                $customers_with_no_campaigns_html = "<tr>";
                $customers_with_no_campaigns_html .= "<td>{$report->customer_id}</td>";
                $customers_with_no_campaigns_html .= "<td>{$report->name}</td>";
                $customers_with_no_campaigns_html .= "<td>$" . number_format($report->balance, 2) . "</td>";
                $customers_with_no_campaigns_html .= "<td width='50%' contenteditable='true' style='cursor: text' data-user='{$report->customer_id}' data-type='no-campaign' title='Click to add a note'></td>";
                $customers_with_no_campaigns_html .= "</tr>";

                $balance += $report->balance;
            }
            $customers_with_no_campaigns_html .= '<tfoot style="color: #fff; background-color: #0099CC"> <tr> ';
            $customers_with_no_campaigns_html .= "<td colspan='2'><strong>TOTALS (" . count($reports) . ")</strong></td>";
            $customers_with_no_campaigns_html .= "<td><strong>$" . number_format($balance, 2) . "</strong></td>";
            $customers_with_no_campaigns_html .= '<td></td>';
            $customers_with_no_campaigns_html .= "</tr> </tfoot>";
        }
        return $customers_with_no_campaigns_html;
    }

    private function getCustomersWithPausedCampaigns()
    {
        /*$pdo->query("SELECT user_id, name, balance, status, GROUP_CONCAT(DISTINCT etat) AS etat, network FROM (


        SELECT `di`.`user_id`, `di`.`name`, bl.balance, u.status, gc.status AS etat, 'Google' AS network
        FROM dealer_info di
        INNER JOIN users u ON u.id = di.user_id
        INNER JOIN v_rmk_balance bl ON bl.user_id = di.user_id
        INNER JOIN ad360_rem_google_campaigns gc ON gc.user_id = u.id
        WHERE gc.user_id
        NOT IN (SELECT user_id FROM ad360_rem_google_campaigns WHERE status = 'ENABLED')
        AND u.id != 711 AND bl.balance IS NOT NULL AND bl.balance > 20 AND u.status = 'active' AND u.free = 'no' AND u.tester = 'no'

        UNION ALL

        SELECT `di`.`user_id`, `di`.`name`, bl.balance, u.status, fc.status AS etat, 'Facebook' AS network
        FROM dealer_info di
        INNER JOIN users u ON u.id = di.user_id
        INNER JOIN v_rmk_balance bl ON bl.user_id = di.user_id
        INNER JOIN ad360_rem_facebook_campaigns fc ON fc.user_id = u.id
        WHERE fc.user_id
        NOT IN (SELECT user_id FROM ad360_rem_facebook_campaigns WHERE status = 'ACTIVE')
        AND u.id != 711 AND bl.balance IS NOT NULL AND bl.balance > 20 AND u.status = 'active' AND u.free = 'no' AND u.tester = 'no'

        ) campaigns GROUP BY user_id
                                            ")->fetchAll(PDO::FETCH_ASSOC);*/
        $customers_with_paused_campaigns_html = "NO DATA IN DATABASE";

        $reports = DB::select("SELECT customer_id, name, balance, status, GROUP_CONCAT(DISTINCT campaign_status) AS campaign_status, network, budget FROM (
SELECT u.id, CONCAT(u.first_name, u.last_name), u.balance, u.status, awc.status AS campaign_status, 'Adwords' AS network, sub.price
FROM " . self::CUSTOMERS_TABLE . " u
INNER JOIN " . self::SUBSCRIPTIONS_TABLE . " sub ON sub.customer_id = u.id
INNER JOIN " . self::ADS_ACCOUNTS_TABLE . " aw ON aw.customer_id = u.id
INNER JOIN " . self::AW_CAMPAIGNS_TABLE . " awc ON awc.ad_account_id = aw.id
WHERE awc.status <> 'ENABLED'
AND sub.type = 'budget'
AND u.balance IS NOT NULL AND u.balance > 20 AND u.status = 'active' AND u.free = 'no' AND u.tester = 'no'

UNION ALL

SELECT u.id, CONCAT(u.first_name, u.last_name), u.balance, u.status, fbc.status AS campaign_status, 'Facebook' AS network, sub.price
FROM " . self::CUSTOMERS_TABLE . " u
INNER JOIN " . self::SUBSCRIPTIONS_TABLE . " sub ON sub.customer_id = u.id
INNER JOIN " . self::ADS_ACCOUNTS_TABLE . " fb ON fb.customer_id = u.id
INNER JOIN " . self::FB_CAMPAIGNS_TABLE . " fbc ON fbc.ad_account_id = fb.id
WHERE fbc.status <> 'ENABLED'
AND sub.type = 'budget'
AND u.balance IS NOT NULL AND u.balance > 20 AND u.status = 'active' AND u.free = 'no' AND u.tester = 'no'
) campaigns GROUP BY user_id")->get();

        if (count($reports) > 0) {
            $balance = 0;
            $count_customers = count($reports);
            foreach ($reports as $report) {
                if ($report->balance < ($report->price / 7)) {
                    $count_customers--;
                    continue;
                }
                $customers_with_paused_campaigns_html = "<tr>";
                $customers_with_paused_campaigns_html .= "<td>{$report->customer_id}</td>";
                $customers_with_paused_campaigns_html .= "<td>{$report->name}</td>";
                $customers_with_paused_campaigns_html .= "<td>$" . number_format($report->balance, 2) . "</td>";
                $customers_with_paused_campaigns_html .= "<td width='50%' contenteditable='true' style='cursor: text' data-user='{$report->customer_id}' data-type='paused_campaign' title='Click to add a note'></td>";
                $customers_with_paused_campaigns_html .= "</tr>";

                $balance += $report->balance;
            }

            $customers_with_paused_campaigns_html .= '<tfoot style="color: #fff; background-color: #0099CC"><tr>';
            $customers_with_paused_campaigns_html .= "<td colspan='2'><strong>TOTALS (" . $count_customers . ")</strong></td>";
            $customers_with_paused_campaigns_html .= "<td><strong>$" . number_format($balance, 2) . "</strong></td>";
            $customers_with_paused_campaigns_html .= "<td></td>";
            $customers_with_paused_campaigns_html .= "</tr> </tfoot>";
        }
        return $customers_with_paused_campaigns_html;
    }

    private function getCampaignsPerformance($start_date, $end_date)
    {
        /*SELECT `dealer`.`user_id`, `dealer`.`name`, 'google' AS network,
SUM(gc.costs) AS spent,
SUM(gc.impressions) AS impressions,
SUM(gc.clicks)AS clicks,
SUM(gc.cpc) AS cpc
FROM  user_outside_accounts uoa
INNER JOIN `dealer_info` `dealer` on `uoa`.`user_id`=`dealer`.`user_id`
INNER JOIN ad360_rem_google_daily_reports gc ON gc.site_id = `uoa`.`login_id`
WHERE uoa.service_name = 'ADWORDS' AND uoa.is_active = 1 AND dealer.name <> ''
AND DATE_FORMAT(gc.date,'%Y-%m-%d') BETWEEN '$start_date' AND '$end_date'
AND costs > 0
GROUP BY uoa.user_id

UNION ALL

SELECT `dealer`.`user_id`, `dealer`.`name`,  'facebook' AS network,
SUM(fr.spend) AS spent, SUM(fr.impression) AS impressions, SUM(fr.click)AS clicks,
SUM(fr.cpc) AS cpc
FROM  user_outside_accounts uoa
INNER JOIN `dealer_info` `dealer` on `uoa`.`user_id`=`dealer`.`user_id`
INNER JOIN ad360_rem_facebook_campaigns fc ON uoa.user_id = fc.user_id
INNER JOIN ad360_rem_facebook_reports fr ON fr.ad360_rem_facebook_campaigns_id = fc.id
WHERE fr.spend > 0 AND uoa.service_name = 'facebook_ads' AND uoa.is_active = 1 AND dealer.name <> ''
AND fr.created_at BETWEEN '$start_date' AND '$end_date'
GROUP BY uoa.user_id*/
        $campaigns_performance_html = "<tbody>NO DATA IN DATABASE</tbody>";
        $reports = DB::connection()
            ->getPdo()
            ->query("SELECT u.id AS customer_id, CONCAT(u.first_name, u.last_name) AS name, 'google' AS network,
SUM(gr.costs) AS spent,
SUM(gr.impressions) AS impressions,
SUM(gr.clicks)AS clicks,
SUM(gr.cpc) AS cpc
FROM  " . self::ADS_ACCOUNTS_TABLE . " ad_account
INNER JOIN " . self::CUSTOMERS_TABLE . " u on ad_account.customer_id = u.id
INNER JOIN " . self::AW_CAMPAIGNS_TABLE . " gc ON ad_account.id = gc.ad_account_id
INNER JOIN " . self::AW_CAMPAIGNS_REPORTS_TABLE . " gr ON gr.campaign_id = gc.id
WHERE ad_account.ad_service = '" . AdAccount::SERVICE_ADWORDS . "' AND ad_account.is_active = 1
AND gc.created_at BETWEEN '$start_date' AND '$end_date'
AND costs > 0
GROUP BY ad_account.customer_id

UNION ALL

SELECT u.id AS customer_id, CONCAT(u.first_name, u.last_name) AS name,  'facebook' AS network,
SUM(fr.spend) AS spent,
SUM(fr.impression) AS impressions,
SUM(fr.click)AS clicks,
SUM(fr.cpc) AS cpc
FROM  " . self::ADS_ACCOUNTS_TABLE . " ad_account
INNER JOIN " . self::CUSTOMERS_TABLE . " u on ad_account.customer_id = u.id
INNER JOIN " . self::FB_CAMPAIGNS_TABLE . " fc ON ad_account.id = fc.ad_account_id
INNER JOIN " . self::FB_CAMPAIGNS_REPORTS_TABLE . " fr ON fr.campaign_id = fc.id
WHERE fr.spend > 0 AND ad_account.ad_service = ''" . AdAccount::SERVICE_FACEBOOK_ADS . "'' AND ad_account.is_active = 1
AND fr.created_at BETWEEN '$start_date' AND '$end_date'
GROUP BY ad_account.customer_id")
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);
        if (count($reports) > 0) {
            $impressions = 0;
            $color = '';
            $aw_click = 0;
            $aw_total_click = 0;
            $aw_spent = 0;
            $aw_total_spent = 0;
            $aw_billed = 0;
            $aw_billedTotal = 0;
            $fb_click = 0;
            $fb_total_click = 0;
            $fb_spent = 0;
            $fb_total_spent = 0;
            $fb_billed = 0;
            $fb_billedTotal = 0;
            $aw_cpc = 0;
            $aw_total_cpc = 0;
            $fb_cpc = 0;
            $fb_total_cpc = 0;
            $fb_imps = 0;
            $countFB = 0;
            foreach ($reports as $dealer => $report) {

                $aw_spent = 0;
                $aw_billed = 0;
                $aw_click = 0;
                if (isset($report[0]) && $report[0]['network'] == 'google') {
                    $aw_spent = $report[0]['spent'];
                    $aw_total_spent += $report[0]['spent'];
                    $aw_click = $report[0]['clicks'];
                    $aw_total_click += $report[0]['clicks'];

                    $aw_cpc = $aw_spent / $aw_click;
                    $aw_total_cpc += $aw_cpc;

                    $aw_billed = DB::select("SELECT SUM(before_balance - after_balance) as cost FROM " . self::BALANCE_TRANSACTIONS_TABLE . " WHERE customer_id = {$dealer} AND `before_balance`!=`after_balance` AND `created_at` BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59' AND type='Billing' AND message = 'Billing cron job GOOGLE' GROUP BY customer_id")->get();
                    $aw_billedTotal += $aw_billed;
                }

                $fb_spent = 0;
                $fb_billed = 0;
                $fb_imp = 0;
                $fb_click = 0;
                if ((isset($report[1]) && $report[1]['network'] == 'facebook') || (isset($report[0]) && $report[0]['network'] == 'facebook')) {
                    $fb_spent = isset($report[1]['spent']) ? $report[1]['spent'] : $report[0]['spent'];
                    $fb_total_spent += isset($report[1]['spent']) ? $report[1]['spent'] : $report[0]['spent'];
                    $fb_click = isset($report[1]['clicks']) ? $report[1]['clicks'] : $report[0]['clicks'];

                    $fb_cpc = $fb_spent / $fb_click;
                    $fb_total_cpc += $fb_cpc;

                    $fb_imp = isset($report[1]['impressions']) ? $report[1]['impressions'] : $report[0]['impressions'];

                    $fb_imps += isset($report[1]['impressions']) ? $report[1]['impressions'] : $report[0]['impressions'];

                    $countFB++;

                    $fb_billed = DB::select("SELECT SUM(before_balance - after_balance) as cost FROM " . self::BALANCE_TRANSACTIONS_TABLE . " WHERE customer_id = {$dealer} AND `before_balance`!=`after_balance` AND `created` BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59' AND type='Billing' AND message = 'Billing cron job facebook' GROUP BY customer_id")->get();
                    $fb_billedTotal += $fb_billed;
                }

                $campaigns_performance_html = "<tbody id='reportTable'><tr>";
                $campaigns_performance_html .= "<td>{$dealer} - {$report[0]['name']}</td>";
                $campaigns_performance_html .= "<td>" . number_format($report[0]['impressions']) . "</td>";
                $impressions += $report[0]['impressions'];
                $campaigns_performance_html .= "<td>" . number_format($fb_imp) . "</td>";
                $campaigns_performance_html .= "<td>" . number_format($aw_click) . "</td>";
                $campaigns_performance_html .= "<td>" . number_format($fb_click) . "</td>";
                $color = number_format($aw_cpc, 2) > 0.50 ? '#ffb7b7' : '';
                $campaigns_performance_html .= "<td style='background-color: $color'>$" . number_format($aw_cpc, 2) . "</td>";
                $campaigns_performance_html .= "<td>$" . number_format($fb_cpc, 2) . "</td>";
                $campaigns_performance_html .= "<td>$" . number_format($aw_spent, 2) . "</td>";
                $campaigns_performance_html .= "<td>$" . number_format($fb_spent, 2) . "</td>";

                $campaigns_performance_html .= "<td>$" . number_format($aw_spent * self::MARGIN, 2) . "</td>";
                $color = abs(round($aw_billed - ($aw_spent * self::MARGIN), 3)) > 0.10 ? '#ffb7b7' : '';
                $campaigns_performance_html .= "<td style='background-color: $color'>$" . number_format($aw_billed, 2) . "</td>";

                $campaigns_performance_html .= "<td>$" . number_format($fb_spent * self::MARGIN, 2) . "</td>";
                $color = abs(round($fb_billed - ($fb_spent * self::MARGIN), 3)) > 0.10 ? '#ffb7b7' : '';
                $campaigns_performance_html .= "<td style='background-color: $color'>$" . number_format($fb_billed, 2) . "</td>";
                $campaigns_performance_html .= "</tr>";
            }

            $campaigns_performance_html .= "</tbody><tfoot style='color: #fff; background-color: #0099CC'><tr> <td>" . count($reports) . "</td>";
            $campaigns_performance_html .= "<td>" . number_format($impressions / count($reports)) . "</td>";
            $campaigns_performance_html .= "<td>" . number_format($fb_imps / $countFB) . "</td>";
            $campaigns_performance_html .= "<td>" . number_format($aw_total_click / count($reports)) . "</td>";
            $campaigns_performance_html .= "<td>" . number_format($fb_total_click) . "</td>";
            $campaigns_performance_html .= "<td>$" . number_format(($aw_total_cpc / count($reports)), 2) . "</td>";
            $campaigns_performance_html .= "<td>$" . number_format($fb_total_cpc, 2) . "</td>";
            $campaigns_performance_html .= "<td>$" . number_format($aw_total_spent, 2) . "</td>";
            $campaigns_performance_html .= "<td>$" . number_format($fb_total_spent, 2) . "</td>";
            $campaigns_performance_html .= "<td>$" . number_format(($aw_total_spent * self::MARGIN), 2) . "</td> ";
            $campaigns_performance_html .= "<td>$" . number_format($aw_billedTotal, 2) . "</td> ";
            $campaigns_performance_html .= "<td>$" . number_format(($fb_total_spent * self::MARGIN), 2) . "</td> ";
            $campaigns_performance_html .= "<td>$" . number_format($fb_billedTotal, 2) . "</td> </tr> ";
            $campaigns_performance_html .= "<tr> <td>Total Dealers</td> <td>Average Imp.</td> <td>Average Imp.</td> <td>AW Average Clicks</td> <td>Fb Average Clicks</td> <td>AW Average CPC</td> <td>Fb Average CPC</td>  <td>AW Spent</td> <td>Fb Spent</td> <td>AW w/Margin</td> <td>AW Amount Debited</td> <td>Fb w/Margin</td> <td>Fb Amount Debited</td></tr></tfoot>";
        }
        return $campaigns_performance_html;
    }

    private function last_week($start_date, $end_date)
    {
        $last_week_html = "NO DATA IN DATABASE";
        $reports = DB::connection()
            ->getPdo()
            ->query("SELECT u.id AS customer_id, CONCAT(u.first_name, u.last_name) AS name, 'google' AS network,
SUM(gr.costs) AS spent,
SUM(gr.impressions) AS impressions,
SUM(gr.clicks)AS clicks,
SUM(gr.cpc) AS cpc
FROM  " . self::ADS_ACCOUNTS_TABLE . " ad_account
INNER JOIN " . self::CUSTOMERS_TABLE . " u on ad_account.customer_id = u.id
INNER JOIN " . self::AW_CAMPAIGNS_TABLE . " gc ON ad_account.id = gc.ad_account_id
INNER JOIN " . self::AW_CAMPAIGNS_REPORTS_TABLE . " gr ON gr.campaign_id = gc.id
WHERE ad_account.ad_service = '" . AdAccount::SERVICE_ADWORDS . "' AND ad_account.is_active = 1
AND gc.created_at BETWEEN '$start_date' AND '$end_date'
AND costs > 0
GROUP BY ad_account.customer_id

UNION ALL

SELECT u.id AS customer_id, CONCAT(u.first_name, u.last_name) AS name,  'facebook' AS network,
SUM(fr.spend) AS spent,
SUM(fr.impression) AS impressions,
SUM(fr.click)AS clicks,
SUM(fr.cpc) AS cpc
FROM  " . self::ADS_ACCOUNTS_TABLE . " ad_account
INNER JOIN " . self::CUSTOMERS_TABLE . " u on ad_account.customer_id = u.id
INNER JOIN " . self::FB_CAMPAIGNS_TABLE . " fc ON ad_account.id = fc.ad_account_id
INNER JOIN " . self::FB_CAMPAIGNS_REPORTS_TABLE . " fr ON fr.campaign_id = fc.id
WHERE fr.spend > 0 AND ad_account.ad_service = ''" . AdAccount::SERVICE_FACEBOOK_ADS . "'' AND ad_account.is_active = 1
AND fr.created_at BETWEEN '$start_date' AND '$end_date'
GROUP BY ad_account.customer_id")
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);
        if (count($reports)) {
            $impressions = 0;
            $aw_click = 0;
            $aw_total_click = 0;
            $aw_spent = 0;
            $aw_total_spent = 0;
            $aw_billed = 0;
            $aw_billedTotal = 0;
            $fb_click = 0;
            $fb_total_click = 0;
            $fb_spent = 0;
            $fb_total_spent = 0;
            $fb_billed = 0;
            $fb_billedTotal = 0;
            $aw_cpc = 0;
            $aw_total_cpc = 0;
            $fb_cpc = 0;
            $fb_total_cpc = 0;

            foreach ($reports as $dealer => $report) {
                $aw_spent = 0;
                $aw_click = 0;
                $aw_billed = 0;
                $aw_cpc = 0;
                if (isset($report[0])) {
                    $aw_spent = $report[0]['spent'];
                    $aw_total_spent += $report[0]['spent'];
                    $aw_click = $report[0]['clicks'];
                    $aw_total_click += $report[0]['clicks'];
                    if ($aw_click > 0) {
                        $aw_cpc = $aw_spent / $aw_click;
                    }
                    $aw_total_cpc += $aw_cpc;

                    $aw_billed = DB::select("SELECT SUM(before_balance - after_balance) as cost FROM " . self::BALANCE_TRANSACTIONS_TABLE . " WHERE customer_id = {$dealer} AND `before_balance`!=`after_balance` AND `created` BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59' AND type='Billing' AND message = 'Billing cron job GOOGLE' GROUP BY customer_id")->get();
                    $aw_billedTotal += $aw_billed;
                }

                $fb_spent = 0;
                $fb_click = 0;
                $fb_billed = 0;
                $fb_cpc = 0;
                if (isset($report[1])) {
                    $fb_spent = $report[1]['spent'];
                    $fb_total_spent += $report[1]['spent'];
                    $fb_click = $report[1]['clicks'];
                    $fb_total_click += $report[1]['clicks'];

                    $fb_cpc = $fb_spent / $fb_click;
                    $fb_total_cpc += $fb_cpc;

                    $fb_billed = DB::select("SELECT SUM(before_balance - after_balance) as cost FROM " . self::BALANCE_TRANSACTIONS_TABLE . " WHERE customer_id = {$dealer} AND `before_balance`!=`after_balance` AND `created` BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59' AND type='Billing' AND message = 'Billing cron job facebook' GROUP BY customer_id")->get();
                    $fb_billedTotal += $fb_billed;
                }

                $impressions += $report[0]['impressions'];
            }

            $last_week_html = "<td>" . number_format(count($reports)) . "</td>";

            $last_week_html .= "<td>" . number_format($impressions / count($reports)) . "</td>";

            $last_week_html .= "<td>" . number_format($aw_total_click / count($reports)) . "</td>";
            $last_week_html .= "<td>" . number_format($fb_total_click) . "</td>";

            $last_week_html .= "<td>$" . number_format($aw_total_cpc / count($reports), 2) . "</td>";
            $last_week_html .= "<td>$" . number_format($fb_total_cpc, 2) . "</td>";

            $last_week_html .= "<td>$" . number_format($aw_total_spent, 2) . "</td>";
            $last_week_html .= "<td>$" . number_format($fb_total_spent, 2) . "</td>";

            $last_week_html .= "<td>$" . number_format(($aw_total_spent * self::MARGIN), 2) . "</td>";
            $last_week_html .= "<td>$" . number_format($aw_billedTotal, 2) . "</td>";

            $last_week_html .= "<td>$" . number_format(($fb_total_spent * self::MARGIN), 2) . "</td>";
            $last_week_html .= "<td>$" . number_format($fb_billedTotal, 2) . "</td> </tr> ";
        }
        return $last_week_html;
    }

    private function getNewCustomers($start_date, $end_date)
    {
        $reports = DB::select("SELECT u.id, CONCAT(u.first_name, u.last_name) AS name, sub.price
FROM " . self::CUSTOMERS_TABLE . " u
INNER JOIN " . self::ADS_ACCOUNTS_TABLE . " ad_account ON u.id = ad_account.customer_id
LEFT JOIN " . self::SUBSCRIPTIONS_TABLE . " sub ON sub.customer_id = u.id
WHERE sub.price IS NOT NULL AND sub.price > 0
AND sub.type = 'budget'
AND ad_account.created_at BETWEEN '$start_date' AND '$end_date'
GROUP BY u.id ORDER BY name ASC")->get();
        if (count($reports) > 0) {
            $color = '';
            $weekly_budgets = 0;
            foreach ($reports as $stat) {
                $weekly_budgets += $stat['ad360_credit'];

                if ($stat['ad360_credit'] < 199.99) {
                    if ($stat['ad360_credit'] <= 99.99) $color = '#ffb7b7';
                    if ($stat['ad360_credit'] >= 100 && $stat['ad360_credit'] <= 199.99) $color = '#ffc100';
                }

                $new_dealers .= "<tr>";
                $new_dealers .= "<td>{$stat['user_id']} - {$stat['name']}</td>";
                $new_dealers .= "<td style='background-color: $color'>$" . number_format($stat['ad360_credit'], 2) . "</td>";
                $new_dealers .= "<td>$" . number_format(abs(200 - $stat['ad360_credit']), 2) . "</td>";
                $new_dealers .= "</tr>";
            }
            $color = '';
            $new_dealers .= '<tfoot style="color: #fff; background-color: #0099CC">';
            $new_dealers .= "<tr> <td align='right'>TOTAL NEW DEALERS</td>";
            $new_dealers .= "<td>" . count($stats) . "</td>";
            $new_dealers .= "<td rowspan='2'></td> </tr> ";

            $new_dealers .= "<tr> <td align='right'>TOTAL ADDITIONAL SPENT</td> <td>$" . number_format($weekly_budgets, 2) . "</td> </tr>";
            $new_dealers .= "</tfoot>";
        }
    }
}
