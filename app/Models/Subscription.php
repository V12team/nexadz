<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    public function create($data)
    {
        return $this->fill($data)
            ->saveOrFail();
    }
}
