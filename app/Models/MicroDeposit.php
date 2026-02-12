<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MicroDeposit extends Model
{
    protected $fillable = [
        'customer_bank_id','amount_cents',
        'status','attempts','expires_at'
    ];

    protected $casts = ['expires_at' => 'datetime'];
}
