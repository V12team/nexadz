<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\FacebookCampaignReport;
use Illuminate\Support\Facades\DB;
use PDO;


class CampaignReport extends Controller
{
    // Get all reports
    public function index()
    {
        // Get customers DATA
        $page_title = '';
        $page_description = '';

        //$businessId = 127915908088501;

        $pagesWithPermission = [];

        $customers = Customer::all();

        return view('reports.all-performance', compact(
                        'customers',
                        'page_title',
                        'page_description',
                        'pagesWithPermission'
        ));
    }

    // Get Weekly performance based on spending budget
    public function weeklyPerformance(Request $request) {

        $page_title = '';
        $page_description = '';

        $start_date = date("Y-m-01", strtotime('previous monday', strtotime("-2 months")));
        $end_date = $request->get('date_end') ?? date("Y-m-d", strtotime("last sunday"));

        $weekly_performance = '';

        $stats = DB::connection()
                ->getPdo()
                ->query("SELECT week(DATE_FORMAT(date,'%Y-%m-%d'), 1) AS week, year, COUNT(DISTINCT customers) AS customers, SUM(amount) AS amount 

# SQL query to get weekely performance based spending budget on facebook and google, we JOINT ads accounts table with Facebook compaings/Google Compaings and UNION ALL
FROM  (
# SQL Query for Google
SELECT gc.date, year(DATE_FORMAT(gc.date,'%Y-%m-%d')) AS year, adsacc.customer_id AS customers, gc.costs AS amount 
FROM ads_accounts adsacc

INNER JOIN google_campaigns c ON c.ad_account_id = adsacc.ad_account_id
INNER JOIN google_campaigns_reports gc ON gc.google_campaigns_id = c.campaign_id

WHERE adsacc.ad_service = 'adwords' AND adsacc.is_active = 1
AND gc.spend > 0 AND DATE_FORMAT(gc.date,'%Y-%m-%d') BETWEEN '" . $start_date . "' AND '$end_date'


UNION ALL


# SQL Query for Facebook
SELECT fg.date, year(DATE_FORMAT(fg.date,'%Y-%m-%d')) AS year, adsacc.customer_id AS customers, fg.spend AS amount 
FROM ads_accounts adsacc

INNER JOIN facebook_campaigns c ON c.ad_account_id = adsacc.ad_account_id
INNER JOIN facebook_campaigns_reports fg ON fg.campaign_id = c.id

WHERE adsacc.ad_service = 'facebook_ads' AND adsacc.is_active = 1
AND fg.spend > 0 AND DATE_FORMAT(fg.date,'%Y-%m-%d') BETWEEN '" . $start_date . "' AND '$end_date'


) AS week_table GROUP BY week(DATE_FORMAT(date,'%Y-%m-%d'), 1) ORDER BY date DESC")
                ->fetchAll(PDO::FETCH_ASSOC);

                if (count($stats) > 0) {
                    foreach ($stats as $stat) {
                        $weekly_performance .= "<tr>";
                        $weekly_performance .= "<td>" . $this->_getWeekStartEndDatesByWeekAndYear($stat['week'], $stat['year']) . "</td>";
                        $weekly_performance .= "<td>{$stat['customers']} </td>";
                        $weekly_performance .= "<td>$" . number_format($stat['amount'], 2) . "</td>";
                        $weekly_performance .= "<td>$" . number_format(($stat['amount'] / $stat['customers']), 2) . "</td>";
                        $weekly_performance .= "</tr>";
                    }
                }
                return view('reports.weekly-performance', compact(
                                'page_title',
                                'page_description',
                                'start_date',
                                'end_date',
                                'weekly_performance'
                ));
    }

    // Get Monthly performance based on spending budget
    public function monthlyPerformance() {
        $page_title = 'Monthly Performance';
        $page_description = '';

        $monthly_performance = '';

        $stats = DB::connection()
                ->getPdo()
                ->query("SELECT DATE_FORMAT(date,'%Y-%b') AS month, DATE_FORMAT(date,'%Y-%m') AS month_num, COUNT(DISTINCT customers) AS customers, SUM(amount) AS amount 

FROM  (
# SQL Query for google
SELECT gc.date, adsacc.customer_id AS customers, gc.spend AS amount 
FROM ads_accounts adsacc

INNER JOIN google_campaigns c ON c.ad_account_id = adsacc.ad_account_id #OK
INNER JOIN google_campaigns_reports gc ON gc.campaign_id = c.id

WHERE adsacc.ad_service = 'adwords' AND adsacc.is_active = 1

AND gc.spend > 0 AND DATE_FORMAT(gc.date,'%Y-%m-%d') BETWEEN DATE_SUB(DATE_FORMAT(NOW() ,'%Y-%m-01'), interval 11 month) AND DATE_FORMAT(NOW() ,'%Y-%m-%d')


UNION ALL

# SQL Query for Facebook
SELECT fg.date, adsacc.customer_id AS customers, fg.spend AS amount 
FROM ads_accounts adsacc

INNER JOIN facebook_campaigns c ON c.ad_account_id = adsacc.ad_account_id
INNER JOIN facebook_campaigns_reports fg ON fg.campaign_id = c.id

WHERE adsacc.ad_service = 'facebook_ads' AND adsacc.is_active = 1

AND fg.spend > 0 AND DATE_FORMAT(fg.date,'%Y-%m-%d') BETWEEN DATE_SUB(DATE_FORMAT(NOW() ,'%Y-%m-01'), interval 11 month) AND DATE_FORMAT(NOW() ,'%Y-%m-%d')

) AS month_table GROUP BY MONTH(DATE_FORMAT(date,'%Y-%m-%d')) ORDER BY date DESC")
                ->fetchAll(PDO::FETCH_ASSOC);


        if (count($stats) > 0) {
            foreach ($stats as $key => $stat) {
                $month = explode('-', $stat['month_num']);
                $monthly_performance .= "<tr>";
                $monthly_performance .= "<td>{$stat['month']}</td>";
                $monthly_performance .= "<td style='position: relative'>
                                        <a href='" . url("/dashboard/performance/month/{$month[0]}/{$month[1]}") . "' target='_blank'> {$stat['customers']} </a>
                                    </td>";
                $monthly_performance .= "<td>$" . number_format($stat['amount'], 2) . "</td>";
                $avg = (isset($stats[$key + 1])) ? number_format((($stat['amount'] - $stats[$key + 1]['amount']) / $stats[$key + 1]['amount']) * 100, 2) . '%' : '-';

                $monthly_performance .= "<td>$avg</td>";

                $monthly_performance .= "</tr>";
            }
        }
        return view('reports.monthly-performance', compact(
                        'page_title',
                        'page_description',
                        'stats',
                        'monthly_performance'
        ));
    }

    // Get all active customers
    public function allActiveCustomers(Request $request) {

        $page_title = '';
        $page_description = '';
        $table = '';

        $start_date = $request->get('date_start') ?? date("Y-m-d", strtotime("last week monday"));
        $end_date = $request->get('date_end') ?? date("Y-m-d", strtotime("last sunday"));


        # SQL Query for google and facebook UNION to give use all active customers
        $customers = $stats = DB::connection()
                        ->getPdo()
                        ->query("SELECT `cstmr`.`id`, `cstmr`.`last_name`, 'google' AS network,
SUM(gc.costs) AS spent,
SUM(gc.impression) AS impressions,
SUM(gc.clicks) AS clicks,
SUM(gc.cpc) AS cpc,
adsacc.budget AS budget
FROM  ads_accounts adsacc 


INNER JOIN customers cstmr ON cstmr.id = adsacc.customer_id #ok
INNER JOIN google_campaigns_reports gc ON gc.campaign_id = cstmr.id


WHERE adsacc.ad_service = 'adwords' AND adsacc.is_active = 1 AND cstmr.last_name <> ''
AND costs > 0  
AND DATE_FORMAT(gc.date,'%Y-%m-%d') BETWEEN '$start_date' AND '$end_date'

GROUP BY adsacc.id

UNION ALL

SELECT `cstmr`.`id`, `cstmr`.`last_name`,  'facebook' AS network,
SUM(fr.spend) AS spent, SUM(fr.impression) AS impressions, SUM(fr.click)AS clicks,
SUM(fr.cpc) AS cpc,
adsacc.budget AS budget
FROM  ads_accounts adsacc 

INNER JOIN customers cstmr ON cstmr.id = adsacc.customer_id
INNER JOIN facebook_campaigns_reports fr ON fr.campaign_id = cstmr.id

WHERE adsacc.ad_service = 'facebook_ads' AND adsacc.is_active = 1 AND cstmr.last_name <> ''
AND fr.spend > 0
AND fr.created_at BETWEEN '$start_date' AND '$end_date'

GROUP BY adsacc.id



")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
        $table = '';
        if (count($customers) > 0) {
            $countFB = 0;
            $countAW = 0;
            foreach ($customers as $id => $customer) {
                $clicks_aw = 0;
                $spent_aw = 0;
                $imps_aw = 0;
                $budget = 0;
                $cpc_aw = 0;
                $cpl_aw = 0;
                $clicks_fb = 0;
                $spent_fb = 0;
                $imps_fb = 0;
                $cpc_fb = 0;
                $cpl_fb = 0;
                $leads_aw = 0;
                $leads_fb = 0;
                if (isset($customer[0]) && $customer[0]['network'] == 'google') {
                    $spent_aw += $customer[0]['spent'];
                    $imps_aw += $customer[0]['impressions'];
                    $clicks_aw += $customer[0]['clicks'];
                    $leads_aw = DB::connection()->getPdo()->query("SELECT count(id) as Leads FROM v12_crm.crm_leads WHERE user_id = " . $id . " AND source_id = 219 AND type <> 19 AND updated_at BETWEEN '$start_date 23:59:59' AND '$end_date'")->fetchColumn();
                    if ($clicks_aw > 0) {
                        $cpc_aw = $spent_aw / $clicks_aw;
                    }
                    if ($leads_aw > 0) {
                        $cpl_aw = $spent_aw / $leads_aw;
                    }
                }
                if ((isset($customer[1]) && $customer[1]['network'] == 'facebook') || (isset($customer[0]) && $customer[0]['network'] == 'facebook')) {
                    $spent_fb += isset($customer[1]['spent']) ? $customer[1]['spent'] : $customer[0]['spent'];
                    $imps_fb += isset($customer[1]['impressions']) ? $customer[1]['impressions'] : $customer[0]['impressions'];
                    $clicks_fb += isset($customer[1]['clicks']) ? $customer[1]['clicks'] : $customer[0]['clicks'];
                    $leads_fb = DB::connection()->getPdo()->query("SELECT count(id) as Leads FROM v12_crm.crm_leads WHERE user_id = " . $id . " AND source_id = 219 AND type = 19 AND updated_at BETWEEN '$start_date 23:59:59' AND '$end_date'")->fetchColumn();

                    if ($clicks_fb > 0) {
                        $cpc_fb = $spent_fb / $clicks_fb;
                    }
                    if ($leads_fb > 0) {
                        $cpl_fb = $spent_fb / $leads_fb;
                    }
                }

                $table .= "<tr>";
                $table .= "<td>" . $id . "</td>";
                $table .= "<td>" . $customer[0]['name'] . "</td>";
                $table .= "<td>" . (!empty($customer[0]['budget']) && $customer[0]['budget'] > 0 ? '$' . $customer[0]['budget'] : '--') . "</td>";
                $table .= "<td>" . number_format($clicks_aw) . "</td>";
                $table .= "<td>" . number_format($leads_aw) . "</td>";
                $table .= "<td>" . '$' . number_format($spent_aw) . "</td>";
                $color = $cpc_aw > 1 ? '#ffb7b7' : '';
                $table .= "<td style='background-color: $color'>" . '$' . number_format($cpc_aw, 2) . "</td>";
                $color = $cpl_aw > 5 ? '#ffb7b7' : '';
                $table .= "<td style='background-color: $color'>" . '$' . number_format($cpl_aw, 2) . "</td>";
                $table .= "<td>" . number_format($clicks_fb) . "</td>";
                $table .= "<td>" . number_format($leads_fb) . "</td>";
                $table .= "<td>" . '$' . number_format($spent_fb) . "</td>";
                $color = $cpc_fb > 1 ? '#ffb7b7' : '';
                $table .= "<td style='background-color: $color'>" . '$' . number_format($cpc_fb, 2) . "</td>";
                $color = $cpl_fb > 5 ? '#ffb7b7' : '';
                $table .= "<td style='background-color: $color'>" . '$' . number_format($cpl_fb, 2) . "</td>";
                $table .= "</tr>";
            }
            $table .= "<tr><td colspan='2'>Total Dealers</td><td colspan='11'>" . count($customers) . "</td></tr>";
        }
        return view('reports.all-active-customers', compact(
                        'page_title',
                        'page_description',
                        'start_date',
                        'end_date',
                        'table'
        ));
    }

    public function getCustomersWithNoCampaigns() {

        return view("reports.CustomersWithBudgetAndNoCampaign");
    }
    






    private function _getWeekStartEndDatesByWeekAndYear($week, $year) {
        $dto = new \DateTime();
        return "{$dto->setISODate($year, $week, 1)->format('m-d')}/{$dto->setISODate($year, $week, 7)->format('m-d')}";
    }
}
