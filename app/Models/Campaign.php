<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'facebook_campaigns';

    const NETWORK_FACEBOOK = 'facebook_ads';
    const NETWORK_ADWORDS = 'adwords';

    public function create($data) {
        return $this->fill($data)
            ->saveOrFail();
    }
}
