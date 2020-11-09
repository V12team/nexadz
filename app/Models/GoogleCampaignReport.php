<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class GoogleCampaignReport extends Model
{
    protected $table = 'google_campaigns_reports';

    public function create($data) {
        return $this->fill($data)
            ->saveOrFail();
    }

}
