<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BalanceTransaction extends Model
{
    protected $table = 'balance_transactions';

    const TRANSACTION_TYPE_REFILL = 'Refill';
    const TRANSACTION_TYPE_BILLING = 'Billing';
    const TRANSACTION_TYPE_REFUND = 'Refund';

    protected $guarded = [];

    public function addRefillTransaction($customer_id, $before_balance, $after_balance)
    {
        return $this::create([
            'type' => self::TRANSACTION_TYPE_REFILL,
            'message' => 'Balance refill',
            'before_balance' => $before_balance,
            'after_balance' => $after_balance,
            'customer_id' => $customer_id
        ]);
    }

    public function addAdwordsBilling($customer_id, $before_balance, $after_balance){
        return $this::create([
            'type' => self::TRANSACTION_TYPE_BILLING,
            'message' => 'Billing cron job google',
            'before_balance' => $before_balance,
            'after_balance' => $after_balance,
            'customer_id' => $customer_id
        ]);
    }

    public function addFacebookBilling($customer_id, $before_balance, $after_balance){
        return $this::create([
            'type' => self::TRANSACTION_TYPE_BILLING,
            'message' => 'Billing cron job facebook',
            'before_balance' => $before_balance,
            'after_balance' => $after_balance,
            'customer_id' => $customer_id
        ]);
    }
}
