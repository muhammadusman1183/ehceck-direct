<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPayment extends Model
{
    protected $fillable = [
        'invoice_id','merchant_id','reference',
        'plaid_item_id','plaid_access_token','plaid_account_id',
        'balance_status','balance_json',
        'status','decision_reason',
        'amount_cents','currency',
    ];

    protected $casts = [
        'balance_json' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
