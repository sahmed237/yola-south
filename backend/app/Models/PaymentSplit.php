<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'agency_id',
        'amount',
        'net_amount',
        'is_service_fee'
    ];

    protected $casts = [
        'amount' => 'float',
        'net_amount' => 'float',
        'is_service_fee' => 'boolean'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
