<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'market_id',
        'shop_code',
        'block_name',
        'shop_number',
        'size',
        'type',
        'monthly_rent',
        'annual_rent',
        'status',
        'current_occupant_name',
        'current_occupant_phone',
        'current_occupant_nin',
        'current_allocation_id',
        'notes',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'annual_rent' => 'decimal:2',
    ];

    /**
     * Relationship to Market
     */
    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Relationship to Current Allocation
     */
    public function currentAllocation()
    {
        return $this->belongsTo(ShopAllocation::class, 'current_allocation_id');
    }

    /**
     * All Allocations history
     */
    public function allocations()
    {
        return $this->hasMany(ShopAllocation::class);
    }

    /**
     * Full Unit Identifier: e.g. "Block B · unit B12"
     */
    public function getFullLocationAttribute(): string
    {
        return "{$this->block_name} · unit {$this->shop_number}";
    }

    public function getFormattedMonthlyRentAttribute(): string
    {
        return '₦' . number_format($this->monthly_rent, 2);
    }

    public function getFormattedAnnualRentAttribute(): string
    {
        return '₦' . number_format($this->annual_rent, 2);
    }
}
