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
# SQL Query for Facebook
SELECT gc.date, adsacc.customer_id AS customers, gc.costs AS amount 
FROM ads_accounts adsacc

INNER JOIN google_campaigns_reports gc ON gc.google_campaigns_id = adsacc.ad_account_id
WHERE adsacc.ad_service = 'adwords' AND adsacc.is_active = 1

AND gc.costs > 0 AND DATE_FORMAT(gc.date,'%Y-%m-%d') BETWEEN DATE_SUB(DATE_FORMAT(NOW() ,'%Y-%m-01'), interval 11 month) AND DATE_FORMAT(NOW() ,'%Y-%m-%d')


UNION ALL

# SQL Query for Facebook t
SELECT fg.date, year(DATE_FORMAT(fg.date,'%Y-%m-%d')) AS year, adsacc.customer_id AS customers, fg.spend AS amount 
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
    






    private function _getWeekStartEndDatesByWeekAndYear($week, $year) {
        $dto = new \DateTime();
        return "{$dto->setISODate($year, $week, 1)->format('m-d')}/{$dto->setISODate($year, $week, 7)->format('m-d')}";
    }
}
