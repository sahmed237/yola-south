<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'establishment_id',
        'invoiceable_type',
        'invoiceable_id',
        'reference',
        'gateway',
        'email',
        'total_amount',
        'payment_fee',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'total_amount' => 'float',
        'payment_fee' => 'float',
    ];

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function genericItems()
    {
        return $this->hasMany(GenericInvoiceItem::class);
    }

    public function splits()
    {
        return $this->hasMany(InvoiceSplit::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
