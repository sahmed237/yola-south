<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'bank_name',
        'bank_code',
        'account_number',
        'account_name',
        'email',
        'status',
        'is_service_fee',
        'service_fee_amount',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_service_fee' => 'boolean',
        'service_fee_amount' => 'decimal:2',
    ];

    public function revenueRules()
    {
        return $this->hasMany(RevenueRule::class);
    }

    public function paystackSubAccount()
    {
        return $this->hasOne(PaystackSubAccount::class);
    }

    public function monnifySubAccount()
    {
        return $this->hasOne(MonnifySubAccount::class);
    }
}
