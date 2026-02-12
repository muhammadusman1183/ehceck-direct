<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'amount',
        'currency',
        'status',
        'processor_payout_id',
        'processor',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
