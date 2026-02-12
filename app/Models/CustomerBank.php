<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'plaid_item_id',
        'plaid_access_token',
        'plaid_account_id',
        'name',
        'mask',
        'account_type',
        'account_subtype',
        'routing_number',
        'account_number',
    ];

    protected $casts = [
        'plaid_access_token' => 'encrypted',
        'routing_number' => 'encrypted',
        'account_number' => 'encrypted',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
