<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'merchant_id','invoice_number','customer_name','customer_email','customer_phone',
        'amount_cents','currency','status','due_at','notes','public_token'
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function amount(): string
    {
        return number_format($this->amount_cents / 100, 2);
    }
}
