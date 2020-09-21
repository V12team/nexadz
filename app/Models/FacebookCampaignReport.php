<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class FacebookCampaignReport extends Model
{
    protected $table = 'facebook_campaigns_reports';

    public function create($data) {
        return $this->fill($data)
            ->saveOrFail();
    }
}
