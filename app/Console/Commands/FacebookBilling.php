<?php


namespace App\Console\Commands;


use App\Models\AdAccount;
use App\Models\BalanceTransaction;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\FacebookCampaignReport;
use App\Services\FacebookAdsService;
use Illuminate\Console\Command;

class FacebookBilling extends Command
{
    const NETWORKS = ['facebook', 'messenger', 'instagram', 'marketplace'];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'FbBilling:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Facebook network daily billing';
    /**
     * @var AdAccount
     */
    private $_adAccount;
    /**
     * @var Campaign
     */
    private $_campaign;
    /**
     * @var BalanceTransaction
     */
    private $_balanceTransaction;
    /**
     * @var FacebookAdsService
     */
    private $fb_api;
    /**
     * @var FacebookCampaignReport
     */
    private $_facebookCampaignReport;
    /**
     * @var Customer
     */
    private $_customer;

    /**
     * Create a new command instance.
     *
     * @param FacebookAdsService $fb_api
     * @param Customer $customer
     * @param AdAccount $adAccount
     * @param Campaign $campaign
     * @param FacebookCampaignReport $facebookCampaignReport
     * @param BalanceTransaction $balanceTransaction
     */

    public function __construct(
        FacebookAdsService $fb_api,
        Customer $customer,
        AdAccount $adAccount,
        Campaign $campaign,
        FacebookCampaignReport $facebookCampaignReport,
        BalanceTransaction $balanceTransaction
    )
    {
        $this->fb_api = $fb_api;
        $this->_customer = $customer;
        $this->_adAccount = $adAccount;
        $this->_campaign = $campaign;
        $this->_balanceTransaction = $balanceTransaction;
        $this->_facebookCampaignReport = $facebookCampaignReport;
        parent::__construct();
    }

    public function handle()
    {
        try {
            $dateStart = (new \DateTime())->modify('-1 day');
            $campaigns = $this->_adAccount::select('ad_accounts.*')
                ->join('ad_accounts', 'ad_accounts.id', '=', 'facebook_campaigns.ad_account_id')
                ->join('customers', 'customers.id', '=', 'ad_accounts.customer_id')
                ->where([
                    ['ad_accounts.ad_service', 'facebook_ads'],
                    ['ad_accounts.is_active', '1'],
                    ['facebook_campaigns.campaign_id', '<>', '']
                ])
                ->whereNotNull('facebook_campaigns.ad_set_id')
                ->get();
            foreach ($campaigns as $campaign) {
                $facebookStats = $this->fb_api->getStat($campaign->campaign_id, $dateStart->format('Y-m-d'), $dateStart->format('Y-m-d'))
                    ->getLastResponse()
                    ->getContent()['data'];
                if (!empty($facebookStats)) {
                    $publisher_platform = $facebookStat['publisher_platform'] ?? 'facebook';
                    $spend = $facebookStat['spend'] ?? 0;
                    $cpc = $facebookStat['cpc'] ?? 0;
                    $cpm = $facebookStat['cpm'] ?? 0;
                    $cpp = $facebookStat['cpp'] ?? 0;
                    $ctr = $facebookStat['ctr'] ?? 0;
                    $click = $facebookStat['inline_link_clicks'] ?? 0;
                    $impressions = $facebookStat['impressions'] ?? 0;
                    $conversion = isset($facebookStat['conversions']) ? $facebookStat['conversions'][0]['value'] : 0;
                    $publisher_platform = in_array($publisher_platform, self::NETWORKS) ? $publisher_platform : 'facebook';
                    $facebookCampaignReport = $this->_facebookCampaignReport->create([
                        'campaign_id' => $campaign->id,
                        'facebook_campaigns_id' => $campaign->campaign_id,
                        'impression' => $impressions,
                        'click' => $click,
                        'cpc' => $cpc,
                        'cpm' => $cpm,
                        'cpp' => $cpp,
                        'ctr' => $ctr,
                        'spend' => $spend,
                        'publisher_platform' => $publisher_platform,
                        'date' => $dateStart->format('Y-m-d'),
                        'conversion' => $conversion
                    ]);
                }
            }
            $customers = $this->_customer->selectRaw('customers.id AS customer, u.login, uoa.login_id AS adAccount, bl.balance, uoa.settting, sum(spend) as spend, GROUP_CONCAT(fbcp.campaign_id) AS campaigns, fbrp.date')
                ->join('ad_accounts', 'customers.id', 'ad_accounts.customer_id')
                ->join('facebook_campaigns', 'ad_accounts.id', 'facebook_campaigns.ad_account_id')
                ->join('facebook_campaigns_reports', 'facebook_campaigns.id', 'facebook_campaigns_reports.campaign_id')
                ->where([
                    ['ad_accounts.ad_service', 'facebook_ads'],
                    ['ad_accounts.is_active', '1'],
                    ['ad_accounts.ad_account_id', '<>', ''],
                    ['facebook_campaigns_reports.date', '=', $this->dateStart],
                ])
                ->groupBy('ad_accounts.customer_id')
                ->get();
            if (count($customers) > 0) {
                foreach ($customers as $customer) {
                    $spend = $customer->spend;
                    if ($spend > 0) {
                        $this->updateBalance($customer->id, $customer->balance, $customer->spend);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::info('Error : in ' . $e->getFile() . ' Line : ' . $e->getLine());
            Log::info('Error message : ' . $e->getMessage());
        }
    }

    private function updateBalance($customer_id, $currentBalance, $spend)
    {
        $customerEntity = $this->_customer->find($customer_id);
        if ($customerEntity) {
            $customerEntity->balance = $currentBalance - $spend;
            $customerEntity->save();
        }
        $after_balance = $currentBalance - $spend;
        $this->_balanceTransaction->addFacebookBilling($customer_id, $currentBalance, $after_balance);
        return $after_balance;
    }
}
