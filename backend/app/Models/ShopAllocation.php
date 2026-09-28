<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopAllocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_no',
        'market_id',
        'shop_id',
        'applicant_name',
        'applicant_phone',
        'applicant_email',
        'applicant_nin_bvn',
        'applicant_address',
        'trade_type',
        'requested_size',
        'passport_photo',
        'id_document',
        'business_reg_doc',
        'stage',
        'status',
        'rent_amount',
        'allocation_fee',
        'payment_status',
        'payment_reference',
        'invoice_no',
        'reviewed_by',
        'approved_by',
        'allocated_by',
        'reviewed_at',
        'approved_at',
        'allocated_at',
        'paid_at',
        'officer_recommendation',
        'approval_notes',
        'rejection_reason',
        'conditions',
        'tracking_hash',
    ];

    protected $casts = [
        'stage' => 'integer',
        'rent_amount' => 'decimal:2',
        'allocation_fee' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'allocated_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public static function getWorkflowStages(): array
    {
        return [
            1 => 'Application',
            2 => 'Review',
            3 => 'Recommendation',
            4 => 'Approval',
            5 => 'Allocation',
            6 => 'Invoice & Payment',
            7 => 'Letter & Card'
        ];
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function allocator()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function getStageLabelAttribute(): string
    {
        return self::getWorkflowStages()[$this->stage] ?? "Stage {$this->stage}";
    }

    public function getStagePercentageAttribute(): int
    {
        return (int) round(($this->stage / 7) * 100);
    }

    public function getFormattedTotalFeeAttribute(): string
    {
        return '₦' . number_format($this->rent_amount + $this->allocation_fee, 2);
    }
}
