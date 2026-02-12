<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'customer_id',
        'amount',
        'currency',
        'status',
        'reference',
        'memo',
        'processor_transfer_id',
        'processor',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
