<?php

namespace App\Http\Libs;

use SudiptoChoudhury\Zoho\Subscriptions\Api;

class ZohoApi
{
    public function getApi(){
        return new Api([
            'authtoken' => env('ZOHO_AUTH_TOKEN'),
            'zohoOrgId' => env('ZOHO_ORG_ID'),
        ]);
    }
}
