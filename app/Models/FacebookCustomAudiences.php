<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class FacebookCustomAudiences extends Model
{
    protected $table = 'facebook_custom_audiences';

    public function create($data) {
        return $this->fill($data)
            ->saveOrFail();
    }
}
