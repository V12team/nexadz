<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AdAccount extends Model
{
    protected $table = 'ads_accounts';

    const SERVICE_FACEBOOK_ADS = 'facebook_ads';
    const SERVICE_ADWORDS = 'adwords';

    public function create($data) {
        return $this->fill($data)
            ->saveOrFail();
    }
}
