<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Market extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'ward_id',
        'ward_name',
        'address',
        'blocks_count',
        'description',
        'status',
        'revenue_ytd',
    ];

    protected $casts = [
        'blocks_count' => 'integer',
        'revenue_ytd' => 'decimal:2',
    ];

    /**
     * Relationship to Ward
     */
    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    /**
     * Relationship to Shops
     */
    public function shops()
    {
        return $this->hasMany(Shop::class);
    }

    /**
     * Relationship to Allocations
     */
    public function allocations()
    {
        return $this->hasMany(ShopAllocation::class);
    }

    /**
     * Computed counts & rates
     */
    public function getTotalUnitsAttribute(): int
    {
        return $this->shops()->count();
    }

    public function getOccupiedUnitsAttribute(): int
    {
        return $this->shops()->where('status', 'occupied')->count();
    }

    public function getVacantUnitsAttribute(): int
    {
        return $this->shops()->where('status', 'vacant')->count();
    }

    public function getOccupancyRateAttribute(): float
    {
        $total = $this->total_units;
        if ($total <= 0) {
            return 0.0;
        }
        return round(($this->occupied_units / $total) * 100, 1);
    }

    public function getFormattedRevenueAttribute(): string
    {
        return '₦' . number_format($this->revenue_ytd, 2);
    }
}
