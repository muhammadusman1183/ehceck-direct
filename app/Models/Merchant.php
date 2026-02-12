<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'password', 'dwolla_customer_id', 'status', 'verified_at'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function banks()
    {
        return $this->hasMany(MerchantBank::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function echeck()
    {
        return $this->hasMany(Echeck::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
