<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcheckVerification extends Model
{
    protected $fillable = [
        'merchant_id',
        'customer_name','customer_email','customer_phone',
        'amount',
        'plaid_item_id','plaid_access_token','plaid_account_id',
        'routing_number','account_last4',
        'available_balance','current_balance',
        'status','result_json',
    ];

    protected $casts = [
        'result_json' => 'array',
        'amount' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];
}
