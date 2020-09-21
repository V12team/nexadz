<?php


namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\AdAccount;
use App\Models\Subscription;
use App\Services\FacebookAdsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdController extends Controller
{
    /**
     * @var App\Models\Customer $_customer
     */
    protected $_customer;

    /**
     * @var App\Models\Subscription $_subscription
     */
    protected $_subscription;

    /**
     * @var App\Models\AdAccount $_adAccount
     */
    protected $_adAccount;

    protected $_fb_api;

    public function __construct(
        Customer $customer,
        Subscription $subscription,
        AdAccount $adAccount,
        FacebookAdsService $fb_api
    )
    {
        $this->_customer = $customer;
        $this->_subscription = $subscription;
        $this->_adAccount = $adAccount;
        $this->_fb_api = $fb_api;
    }

    public function createAdAccount(Request $request)
    {
        $customer_id = $request->post('customer_id');
        $service = $request->post('service') ?? AdAccount::SERVICE_FACEBOOK_ADS;
        $budget = $request->post('budget');
        $data = $request->all();
        try {
            $validator = Validator::make($data, [
                'customer_id' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return response()->json(['success' => false, "message" => $validator->errors()], 200);
            }
            $customer = $this->_customer::where('id', $customer_id);
            if (!$customer) {
                Log::info($customer_id . " not found");
                return response()->json(['success' => false, "message" => "No customer found"], 200);
            }
            $subscription = $this->_subscription::where('customer_id', $customer_id);
            if (!$subscription) {
                Log::info("No subscription found for customer " . $customer_id);
                return response()->json(['success' => false, "message" => "No subscription found"], 200);
            }
            $adAccount = $this->_adAccount::where('ad_account_id', $subscription->ad_account);
            if (!$adAccount) {
                switch ($service) {
                    case AdAccount::SERVICE_FACEBOOK_ADS :
                        $adAccountId = $this->_fb_api->createAdAccount("{$customer_id} - {$customer->first_name} {$customer->last_name}");
                        $this->_fb_api->AdAccountAssignedUser();
                        break;
                    case AdAccount::SERVICE_ADWORDS :
                        // adwords ad account creation
                        break;
                }
                if (!empty($adAccountId)) {
                    $adAccount = $this->_adAccount->create([
                        'ad_account' => $adAccountId,
                        'customer_id' => $customer->id,
                        'ad_service' => $service,
                        'budget' => $budget ?? $subscription->price
                    ]);
                    if($adAccount) {
                        $this->_fb_api->claimPage($customer->fb_page);
                        $subscription->ad_account = $adAccountId;
                        $subscription->save();
                    }
                }
            }
        } catch (\Exception $e) {
            Log::info('Exception : ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'error occurred during process'], 200);
        }
    }

    public function checkCampaigns(Request $request){

    }
}
