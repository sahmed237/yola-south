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
        'action_required_notes',
        'action_requested_at',
        'action_responded_at',
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
        'action_requested_at' => 'datetime',
        'action_responded_at' => 'datetime',
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

    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    public function latestInvoice()
    {
        return $this->morphOne(Invoice::class, 'invoiceable')->latestOfMany();
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Get existing pending invoice or generate new polymorphic invoice with generic line items
     */
    public function getOrCreateAllocationInvoice(string $gateway = 'monnify'): Invoice
    {
        $existing = $this->invoices()->where('status', 'pending')->latest()->first();
        if ($existing) {
            if (strtolower($existing->gateway) !== strtolower($gateway)) {
                $meta = $existing->metadata ?? [];
                unset($meta['redirect_url'], $meta['gateway_reference']);
                $existing->update([
                    'gateway' => $gateway,
                    'metadata' => $meta,
                ]);
            }
            return $existing->load('genericItems');
        }

        $totalAmount = (float) ($this->allocation_fee + $this->rent_amount);
        $cleanGateway = strtolower($gateway) ?: 'monnify';
        $reference = 'INV-SHP-' . strtoupper($cleanGateway) . '-' . date('Ymd') . '-' . rand(1000, 9999);

        $invoice = Invoice::create([
            'invoiceable_type' => self::class,
            'invoiceable_id' => $this->id,
            'establishment_id' => null,
            'reference' => $reference,
            'gateway' => $cleanGateway,
            'email' => $this->applicant_email,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'metadata' => [
                'application_no' => $this->application_no,
                'applicant_name' => $this->applicant_name,
                'phone' => $this->applicant_phone,
                'market' => $this->market?->name,
                'shop_code' => $this->shop?->shop_code,
            ],
        ]);

        // Generic invoice items:
        GenericInvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Allocation & Documentation Fee',
            'description' => 'Statutory processing and commercial unit allocation documentation fee',
            'quantity' => 1,
            'unit_price' => $this->allocation_fee,
            'amount' => $this->allocation_fee,
            'metadata' => ['fee_type' => 'allocation_fee'],
        ]);

        GenericInvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Initial Monthly Rent (' . ($this->shop?->shop_code ?? 'Assigned Unit') . ')',
            'description' => 'Month 1 commercial rent for ' . ($this->shop?->block_name ?? '') . ' Unit ' . ($this->shop?->shop_number ?? ''),
            'quantity' => 1,
            'unit_price' => $this->rent_amount,
            'amount' => $this->rent_amount,
            'metadata' => ['fee_type' => 'rent', 'shop_id' => $this->shop_id],
        ]);

        $this->update(['invoice_no' => $invoice->reference]);

        return $invoice->fresh('genericItems');
    }
}

