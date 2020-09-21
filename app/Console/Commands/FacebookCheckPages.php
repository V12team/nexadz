<?php


namespace App\Console\Commands;


use App\Models\Customer;
use App\Services\FacebookAdsService;
use Psy\Command\Command;

class FacebookCheckPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fbPagesCheck:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if business manager has grant access to client pages';
    /**
     * @var Customer
     */
    private $_customer;
    /**
     * @var FacebookAdsService
     */
    private $_fb_api;

    public function __construct(Customer $customer, FacebookAdsService $fb_api)
    {
        $this->_customer = $customer;
        $this->_fb_api = $fb_api;
        parent::__construct();
    }

    public function handle(){
        $customers = $this->_customer::where(['fb_grant_status' ,'<>', 'yes'])
            ->whereIn('status', ['active', 'suspend_payment'])
            ->get();
        if(count($customers) > 0){
            foreach ($customers as $customer){
                if($this->_fb_api->hasPage($customer->fb_page)){
                    $customer->fb_grant_status = 'yes';
                    $customer->save();
                    # TODO
                    // send alert email
                }
            }
        }
    }
}
