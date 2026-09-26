<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'agency_id',
        'amount',
        'net_amount',
        'ratio',
        'subaccount_code',
        'is_service_fee',
     ];
 
     protected $casts = [
         'amount' => 'float',
         'net_amount' => 'float',
         'ratio'  => 'float',
         'is_service_fee' => 'boolean',
     ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
