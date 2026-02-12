<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Echeck extends Model
{
    protected $fillable = [
        'merchant_id',
        'customer_name','customer_email','customer_phone',
        'amount',
        'plaid_item_id','plaid_access_token','plaid_account_id',
        'bank_name','bank_mask','account_subtype',
        'account_number','routing_number','account_last4',
        'status','account_holder_name','account_holder_address1','account_holder_address2',
        'pdf_path', 'image_path',
        'verification_json',
    ];

    protected $casts = [
        'verification_json' => 'array',
        'amount' => 'decimal:2',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
