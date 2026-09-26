<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'revenue_rule_id',
        'agency_id',
        'period',
        'amount',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function revenueRule()
    {
        return $this->belongsTo(RevenueRule::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
