<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Customer extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPEND_PAYMENT = 'suspend_payment';

    protected $table = 'customers';

    protected $guarded = [];

    protected $fillable = [

        'external_id', 'first_name', 'last_name', 'email', 'phone', 'company', 'fb_page', 'fb_grant_status',
        'country', 'state', 'zipcode', 'address', 'status', 'balance'

    ];
}
