<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenericInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'amount' => 'float',
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
