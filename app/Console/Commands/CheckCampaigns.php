<?php

namespace App\Console\Commands;

use App\Models\AdAccount;
use App\Models\Campaign;
use App\Models\Customer;
use App\Services\FacebookAdsService;
use Illuminate\Console\Command;
use Log;

class CheckCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkCampaigns:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check campaigns to start or pause';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $_adAccount;

    protected $_campaign;

    protected $_fb_api;

    const NETWORK_STATUS = ['UNFUNDED' => 'PAUSED', 'ACTIVE' => 'ACTIVE'];

    private $_dateStart = null;

    public function __construct(
        AdAccount $adAccount,
        Campaign $campaign,
        FacebookAdsService $fb_api)
    {
        parent::__construct();
        $this->_adAccount = $adAccount;
        $this->_campaign = $campaign;
        $this->_fb_api = $fb_api;
        $this->_dateStart = (new \DateTime())->modify('-1 day')->format('Ymd');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $adAccounts = $this->_adAccount::select('customers.id AS owner, ad_accounts.id AS adAccount, customers.balance, customers.budget')
                ->join('customers', 'customers.id', '=', 'ad_accounts.customer_id')
                ->where([
                    ['ad_accounts.ad_service', 'facebook_ads'],
                    ['ad_accounts.is_active', '1'],
                    ['customers.id', '<>', '']
                ])
                ->whereIn('customers.status', [
                    Customer::STATUS_ACTIVE,
                    Customer::STATUS_SUSPEND_PAYMENT
                ])
                ->groupBy('customers.id')
                ->get();
            if ($adAccounts) {
                foreach ($adAccounts as $adAccount) {
                    $daily_budget = ($adAccount->budget - ($adAccount->budget * 0.3)) / 7;
                    $this->updateCampaign(
                        $adAccount->owner,
                        $adAccount->balance <= $daily_budget ? 'UNFUNDED' : 'ACTIVE'
                    );
                }
            }
        } catch (\Exception $e) {
            Log::info('Error : in ' . $e->getFile() . ' Line : ' . $e->getLine());
            Log::info('Error message : ' . $e->getMessage());
        }
    }

    protected function updateCampaign($customer, $status)
    {
        try {
            $campaigns = $this->_campaign::where([
                'customer_id' => $customer
            ])->whereIn('status', ['ACTIVE', 'UNFUNDED'])
                ->get();
            if ($campaigns) {
                foreach ($campaigns as $campaign) {
                    $campaign->status = NETWORK_STATUS[$status];
                    $campaign->save();
                    $FbCampaign = (new \FacebookAds\Object\Campaign($campaign->campaign_id))->updateSelf(
                        [],
                        [Campaign::STATUS_PARAM_NAME => NETWORK_STATUS[$status]]
                    )->exportAllData();
                }
            }
        } catch (\Exception $e) {
            Log::info('Error : in ' . $e->getFile() . ' Line : ' . $e->getLine());
            Log::info('Error message : ' . $e->getMessage());
        }
    }
}
