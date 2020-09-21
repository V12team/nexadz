<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BalanceTransaction extends Model
{
    protected $table = 'balance_transactions';

    const TRANSACTION_TYPE_REFILL = 'Refill';
    const TRANSACTION_TYPE_BILLING ='Billing';
    const TRANSACTION_TYPE_REFUND = 'Refund';

    public function create($data) {
        return $this->fill($data)
            ->saveOrFail();
    }
}
