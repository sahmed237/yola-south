<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'establishment_id',
        'revenue_rule_id',
        'amount',
        'status',
        'reference',
        'gateway',
        'metadata',
        'officer_id'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function revenueRule()
    {
        return $this->belongsTo(RevenueRule::class);
    }

    public function splits()
    {
        return $this->hasMany(PaymentSplit::class);
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}
