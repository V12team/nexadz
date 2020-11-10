<?php


namespace App\Http\Controllers;

use App\Http\Libs\ZohoApi;
use App\Models\AdAccount;
use App\Models\BalanceTransaction;
use App\Models\Customer;
use App\Services\FacebookAdsService;
use Illuminate\Http\Request;
use DateTime;
use Log;


class CustomerController extends Controller
{
    /**
     * @var \SudiptoChoudhury\Zoho\Subscriptions\Api
     */
    protected $_zoho_api;
    /**
     * @var App\Models\Customer
     */
    protected $_customer;
    /**
     * @var App\Models\AdAccount
     */
    private $_adAccount;
    /**
     * @var BalanceTransaction
     */
    private $_balanceTransaction;

    public function __construct(
        ZohoApi $zohoApi,
        Customer $customer,
        AdAccount $adAccount,
    BalanceTransaction $balanceTransaction
    )
    {
        $this->_zoho_api = $zohoApi->getApi();
        $this->_customer = $customer;
        $this->_adAccount = $adAccount;
        $this->_balanceTransaction = $balanceTransaction;
    }

    public function index()
    {
        $page_title = 'Customers list';
        $page_description = '';
        try {
            $customers = $this->_customer->get();
            $temp = [];
            $unpaid_invoices = [];
            $invoices = $this->_zoho_api->getInvoices([
                'filter_by' => 'Status.Unpaid',
            ]);
            $invoices = $invoices['invoices'] ?? false;
            if ($invoices) {
                foreach ($invoices as $key => $invoice) {
                    $unpaid_invoices[$invoice['customer_id']][] = $invoice;
                }
            }
            foreach ($customers as $customer) {
                $subscriptions = $this->_zoho_api->getSubscriptions(['customer_id' => $customer->external_id]);
                $subscriptions = $subscriptions['subscriptions'] ?? false;
                if ($subscriptions) {
                    foreach ($subscriptions as $subscription) {
                        if ($subscription['shipping_interval_unit'] == 'months') {
                            $customer->plan = $subscription['amount'];
                            if (isset($subscription['last_billing_at'])) {
                                $customer->last_billing = new DateTime($subscription['last_billing_at']);
                            }
                            if (isset($subscription['next_billing_at'])) {
                                $customer->next_billing = new DateTime($subscription['next_billing_at']);
                            }
                        } else if ($subscription['shipping_interval_unit'] == 'weeks') {
                            $customer->weekly_budget = $subscription['amount'];
                        }
                    }
                }
                $customer->payment = array_key_exists($customer->external_id, $unpaid_invoices) ? 'Rejected' : 'Paid';
                if ($customer->status == 'active' || $customer->status == 'suspend_payment') {
                    $campaigns = $this->_adAccount::where([
                        'customer_id' => $customer->id,
                        'ad_service' => 'facebook_ads'
                    ])
                        ->join('facebook_campaigns', 'facebook_campaigns.ad_account_id', 'ads_accounts.id')
                        ->get();
                    if (count($campaigns) > 0) {
                        $customer->campaign_status = 'Launched';
                        foreach ($campaigns as $campaign) {
                            if ($campaign->status != 'ACTIVE') {
                                $customer->campaign_status = 'Stopped';
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::info('Error in : ' . $e->getFile() . ' line ' . $e->getLine());
            Log::info('Error message : ' . $e->getMessage());
        }
        return view('reports.customersList', compact('page_title', 'page_description', 'customers'));
    }

    public function show($customer_id)
    {
        /*$customer = $this->_zoho_api->getCustomer([
            'customer_id' => $customer_id
        ]);*/
        $customer = $this->_customer::find($customer_id);
        /*$fb_api = new FacebookAdsService(
            env('FACEBOOK_APP_ID'),
            env('FACEBOOK_APP_SECRET'),
            env('FACEBOOK_TOKEN')
        );
        $adAccountId = $fb_api->createAdAccount("{$customer_id} - {$customer->first_name} {$customer->last_name}");*/
        dd($customer);
    }

    public function dump()
    {
        /*$customers = $this->_zoho_api->getCustomers()['customers'];
        $added = [];
        $i = 1;
        foreach($customers as $customer){
            $address = $this->_zoho_api->getCustomer(['customer_id' => $customer['customer_id']])['customer']['billing_address'];
            $customer_data = [
                'external_id' => $customer['customer_id'],
                'first_name'  => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'] . $i,
                'phone' => $customer['phone'],
                'company' => $customer['company_name'],
                'fb_page' => '',
                'fb_grant_status' => 'pending',
                'country' => $address['country'],
                'state' => $address['state'],
                'zipcode' => $address['zip'],
                'address' => $address['street'],
                'status' => 'active',
                'balance' => 0
            ];
            $c = $this->_customer::create($customer_data);
            $i++;
            array_push($added, $c);
        }
        dd($added);*/
    }

    public function add(Request $request)
    {
        $data = $request->all();
        // create a customer in our database
        $ourCustomer = $this->_customer->create($data);
        $full_name = $data['last_name'] . ' ' . $data['first_name'];
        $address = [
            "attention" => $full_name,
            "street" => $data['address'],
            "city" => $data['city'],
            "state" => $data['state'],
            "zip" => $data['zipcode'],
            "country" => $data['country'],
            "fax" => ''
        ];
        $customer_data = [
            'display_name' => $full_name,
            'salutation' => $full_name,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'company_name' => $data['company'],
            'phone' => $data['phone'],
            'facebook' => $data['fb_page'],
            'billing_address' => $address,
            'shipping_address' => $address,
        ];
        // create a customer in zoho platform
        $customer = $this->_zoho_api->addCustomer($customer_data);
        if ($ourCustomer && isset($customer['customer'])) {
            $ourCustomer->external_id = $customer['customer']['customer_id'];
            $ourCustomer->save();
            return true;
        }
        return false;
    }

    public function updateBalance(Request $request)
    {
        // payload format example
        /*$res = '{
    "payment_id": "9030000079467",
    "payment_mode": "cash",
    "amount": 130,
    "amount_refunded": 0,
    "date": "2016-06-05",
    "status": "success",
    "reference_number": "INV-384",
    "description": "Payment has been added to INV-384",
    "customer_id": "2221476000000120041",
    "customer_name": "Bowman Furniture",
    "email": "benjamin.george@bowmanfurniture.com",
    "autotransaction": {
        "autotransaction_id": "90300000079465",
        "payment_gateway": "payflow_pro",
        "gateway_transaction_id": "B10E6E0F31BD",
        "gateway_error_message": "Gateway error message for a failed transaction.",
        "card_id": "90300000079226",
        "last_four_digits": 1111,
        "expiry_month": 9,
        "expiry_year": 2030
    },
    "invoices": [
        {
            "invoice_id": "2221476000000152001",
            "invoice_number": "INV-384",
            "date": "2016-06-05",
            "invoice_amount": 130,
            "amount_applied": 130,
            "balance_amount": 0
        }
    ],
    "currency_code": "USD",
    "currency_symbol": "$",
    "exchange_rate": 1,
    "bank_charges": 10,
    "tax_account_id": "903000000000370",
    "account_id": "903000000000361",
    "custom_fields": [
        {
            "value": 129890,
            "label": "label",
            "data_type": "text"
        }
    ]
}';*/
        try {
            $payload = $request->all();
            if (!empty($payload['payment_id']) && !empty($payload['customer_id']) && !empty($payload['amount']) && !empty($payload['invoices'])) {
                $customer = $this->_customer::where('external_id', '=', $payload['customer_id'])
                    ->first();
                if ($customer) {
                    if (isset($payload['invoices'][0])) {
                        $z_invoice = $this->_zoho_api->getInvoice([
                            'invoice_id' => $payload['invoices'][0]['invoice_id']
                        ]);
                        $z_invoice = $z_invoice['invoice'] ?? false;
                        if (isset($z_invoice['invoice_id'])) {
                            if (isset($z_invoice['invoice_items'][0])) {
                                if ($z_invoice['invoice_items'][0]['code'] != 'Basic' && $payload['status'] == 'success') {
                                    $before_balance = $customer->balance;
                                    $customer->balance += (int)$payload['amount'];
                                    $customer->save();
                                    $this->_balanceTransaction->addRefillTransaction($customer->id, $before_balance, $customer->balance);
                                    Log::info('Refill balance for customer : ' . $customer->id);
                                } else {
                                    if ($payload['status'] == 'success') {
                                        $customer->status = Customer::STATUS_ACTIVE;
                                        $customer->save();
                                        Log::info('Invoice paid for customer : ' . $customer->id);
                                        Log::info('Change status to ' . Customer::STATUS_ACTIVE . ' for customer : ' . $customer->id);
                                    } else {
                                        #TODO notify with an email
                                        $customer->status = Customer::STATUS_SUSPEND_PAYMENT;
                                        $customer->save();
                                        Log::info('Invoice not paid for customer : ' . $customer->id);
                                        Log::info('Change status to ' . Customer::STATUS_SUSPEND_PAYMENT . ' for customer : ' . $customer->id);
                                    }
                                }
                            }
                        } else {
                            Log::info('Error : Not found zoho invoice : ' . $payload['invoices'][0]['invoice_id'] . ' with api call.');
                        }
                    }
                } else {
                    Log::info('Error : Not found zoho customer : ' . $payload['customer_id'] . ' in our database.');
                }
            }
        } catch (\Exception $e) {
            Log::info('Error in : ' . $e->getFile() . ' line ' . $e->getLine());
            Log::info('Error message : ' . $e->getMessage());
        }
    }
}
