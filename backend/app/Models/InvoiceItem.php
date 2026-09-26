<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'revenue_head_id',
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

    public function revenueHead()
    {
        return $this->belongsTo(RevenueHead::class, 'revenue_head_id');
    }

    public function revenueRule()
    {
        return $this->revenueHead();
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
