<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Customer extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPEND_PAYMENT = 'suspend_payment';

    protected $table = 'customers';

    protected $guarded = [];
}
