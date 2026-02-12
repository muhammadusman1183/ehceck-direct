<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email'];

    public function banks()
    {
        return $this->hasMany(CustomerBank::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
