<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'plaid_item_id',
        'plaid_access_token',
        'plaid_account_id',
        'name',
        'mask',
        'account_type',
        'account_subtype',
        'routing_number',
        'account_number',
        'verification_status',
        'verification_json',
    ];

    protected $casts = [
        'plaid_access_token' => 'encrypted',
        'routing_number' => 'encrypted',
        'account_number' => 'encrypted',
        'verification_json' => 'array',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
