<?php

namespace App\Services;

use App\Events\createEventSourceGroup;
use FacebookAds\Object\Business;
use FacebookAds\Object\CustomAudience;
use FacebookAds\Object\Fields\AdCreativeLinkDataFields;
use FacebookAds\Object\Fields\AdCreativeObjectStorySpecFields;
use FacebookAds\Object\Fields\AdsInsightsFields;
use FacebookAds\Object\Fields\CustomAudienceFields;
use FacebookAds\Object\Page;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Object\Values\AdAccountPermittedTasksValues;
use FacebookAds\Object\Values\AdSetBidStrategyValues;
use FacebookAds\Object\Values\CampaignObjectiveValues;
use FacebookAds\Object\Values\CustomAudienceSubtypes;
use FacebookAds\Object\Values\CustomAudienceTypes;
use FacebookAds\Api;
use FacebookAds\Object\AdSet;
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\AdSetFields;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Fields\AdAccountFields;
use FacebookAds\Object\Fields\TargetingFields;
use FacebookAds\Object\Targeting;
use FacebookAds\Object\TargetingSearch;
use FacebookAds\Object\Search\TargetingSearchTypes;
use FacebookAds\Object\Values\AdSetBillingEventValues;
use FacebookAds\Object\Values\AdSetOptimizationGoalValues;
use FacebookAds\Object\Fields\AdImageFields;
use FacebookAds\Object\Fields\AdCreativeFields;
use FacebookAds\Object\Ad;
use FacebookAds\Object\Fields\AdFields;
use FacebookAds\Object\AdsPixel;
use FacebookAds\Object\Fields\AdsPixelFields;
use FacebookAds\Http\RequestInterface;
use FacebookAds\Http\Exception\RequestException;

class FacebookAdsService
{
    CONST STATUS   = [0 => 'PAUSED', 1 => 'ACTIVE'];
    CONST RETENTION_DAYS = 60;

    private $business;
    private $account;
    private $catalog;
    private $pixel = null;

    public function __construct($app_id, $app_secret, $token)
    {
        Api::init($app_id, $app_secret, $token);
        $this->setBusinessId(env('FACEBOOK_BUSINESS_ID'));
        $this->setCatalogId(config('facebook.' . $this->getBusinessId() . '.catalog_id'));
    }

    public function getBusinessId() {
        return $this->business;
    }

    public function setBusinessId($business) {
        $this->business = $business;
    }

    public function getCatalogId(){
        return $this->catalog;
    }

    public function setCatalogId($catalog) {
        $this->catalog = $catalog;
    }

    public function getAdAccount()
    {
        $fields = array(
            AdAccountFields::ID,
            AdAccountFields::NAME,
            AdAccountFields::ACCOUNT_STATUS
        );
        $adAccount = (new AdAccount($this->account))->getSelf($fields);

        return $adAccount;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount($account)
    {
        $this->account = "act_{$account}";
    }

    public function createAdAccount($adAccountName, $fundingId = 0, $businessId = 318550401827287 ){
        $adAccount = (new Business($businessId))->createAdAccount(
            [ AdAccountFields::ACCOUNT_ID, AdAccountFields::ID  ],
            array(
                //'funding_id'=> $fundingId,
                AdAccountFields::NAME => $adAccountName,
                AdAccountFields::CURRENCY => 'USD',
                AdAccountFields::TIMEZONE_ID => '1' ,
                AdAccountFields::MEDIA_AGENCY => "{$businessId}",
                AdAccountFields::END_ADVERTISER => "{$businessId}",
                AdAccountFields::PARTNER => 'NONE'
            )
        )->exportAllData();

        $this->setAccount($adAccount['account_id']);
        return $adAccount['account_id'];
    }

    public function AdAccountAssignedUser($user = 131989721042944) {
        (new AdAccount($this->account))->createAssignedUser(
            [],
            [
                'user' => $user,
                'tasks' => AdAccountPermittedTasksValues::MANAGE
            ]
        )->exportAllData();
    }

    public function claimPage($page_id, $businessId = 0) {
        return (new Business($businessId))->createClientPage(
            [],
            array(
                'page_id' => $page_id
            )
            )->exportAllData();
    }

    public function hasPage($page_id)
    {
        if(empty($page_id)) return false;

        $pages = Api::instance()->call(
            '/' . env('FACEBOOK_BUSINESS_ID') . '/pages' ,
            RequestInterface::METHOD_GET,
            array()
        )->getContent();

        if(isset($pages['data']) && !empty($pages))
        {
            foreach ($pages['data'] as $page) {
                if(in_array($page_id, $page) && in_array("CONFIRMED", $page))
                    return true;
            }
        }
        return false;
    }

    private function createEventSourceGroup($customer_id) {
        $eventSourceGroups =  Api::instance()->call(
            '/127915908088501/event_source_groups' ,
            RequestInterface::METHOD_POST,
            array(
                'name' => "{$customer_id}'s ESG",
                'event_sources' => [$this->getPixel($customer_id)],
            )
        )->getContent();

        event(new createEventSourceGroup($customer_id, $eventSourceGroups));

        //DB::connection('mysql_write')->update("update ad360_facebook_custom_audiences set event_source_group_id = '{$eventSourceGroups['id']}' where user_id = {$user}");

        return $eventSourceGroups['id'];
    }

    public function getPixel($user)
    {
        if(!is_null($this->pixel))
            return $this->pixel;

        $pixelID = Ad360FacebookCustomAudience::where('user_id', '=', $user)->whereNotNull('pixel_id')->value('pixel_id');

        if(!is_null($pixelID))
            return $pixelID;

        $pixelID = (new AdAccount($this->account))->getAdsPixels(
            ['id'], []
        )->getLastResponse()->getContent();

        if(!isset($pixelID['data'][0]))
        {
            $this->setPixel($this->createPixel("Dealer {$user}"));
            return $this->getPixel($user);
        }

        return $pixelID['data'][0]['id'];

    }

    public function setPixel($pixel)
    {
        $this->pixel = $pixel;
    }

    public function createPixel($name)
    {
        $pixel = (new AdAccount($this->account))->createAdsPixel(
            [],
            [ AdsPixelFields::NAME => "{$name}'s Pixel" ]
        )->exportAllData();

        $product_catalog = new ProductCatalog($this->getCatalogId());
        $product_catalog->createExternalEventSource(array(), array(
            'external_event_sources' => array("{$pixel['id']}")
        ));

        return $pixel['id'];
    }
}
