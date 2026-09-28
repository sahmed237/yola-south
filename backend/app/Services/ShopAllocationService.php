<?php

namespace App\Services;

use App\Mail\ShopAllocatedPaymentMail;
use App\Mail\ShopApplicationUpdateRequestMail;
use App\Models\Market;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\ShopAllocation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ShopAllocationService
{
    /**
     * Generate unique application / allocation reference: e.g. ALL-YSLG-2026-000189
     */
    public function generateApplicationNo(): string
    {
        $year = date('Y');
        $prefix = "ALL-YSLG-{$year}-";

        // Query all existing application numbers for this year
        $existing = ShopAllocation::where('application_no', 'like', "{$prefix}%")
            ->pluck('application_no');

        $maxSeq = 188;
        foreach ($existing as $appNo) {
            if (preg_match('/ALL-YSLG-\d{4}-(\d+)/', $appNo, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxSeq) {
                    $maxSeq = $num;
                }
            }
        }

        $nextSeq = $maxSeq + 1;
        $candidate = $prefix . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);

        // Strict collision safety
        while (ShopAllocation::where('application_no', $candidate)->exists()) {
            $nextSeq++;
            $candidate = $prefix . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    /**
     * Create initial public application
     */
    public function createApplication(array $data): ShopAllocation
    {
        $applicationNo = $this->generateApplicationNo();
        $trackingHash = hash('sha256', $applicationNo . Str::random(16));

        $market = Market::findOrFail($data['market_id']);

        $allocation = ShopAllocation::create([
            'application_no' => $applicationNo,
            'market_id' => $market->id,
            'shop_id' => $data['shop_id'] ?? null,
            'applicant_name' => $data['applicant_name'],
            'applicant_phone' => $data['applicant_phone'],
            'applicant_email' => $data['applicant_email'],
            'applicant_nin_bvn' => $data['applicant_nin_bvn'] ?? null,
            'applicant_address' => $data['applicant_address'] ?? null,
            'trade_type' => $data['trade_type'] ?? 'General Commerce',
            'requested_size' => $data['requested_size'] ?? '3.0 × 4.0 m',
            'passport_photo' => $data['passport_photo'] ?? null,
            'id_document' => $data['id_document'] ?? null,
            'business_reg_doc' => $data['business_reg_doc'] ?? null,
            'stage' => 1,
            'status' => 'pending',
            'rent_amount' => $data['rent_amount'] ?? 15000.00,
            'allocation_fee' => 5000.00,
            'tracking_hash' => $trackingHash,
        ]);

        return $allocation;
    }

    /**
     * Advance application stage in the 7-stage workflow
     */
    public function advanceStage(ShopAllocation $allocation, int $targetStage, array $data = [], ?int $userId = null): ShopAllocation
    {
        switch ($targetStage) {
            case 2: // Review
                $allocation->update([
                    'stage' => 2,
                    'status' => 'review',
                    'reviewed_by' => $userId,
                    'reviewed_at' => now(),
                    'officer_recommendation' => $data['officer_recommendation'] ?? $allocation->officer_recommendation,
                ]);
                break;

            case 3: // Recommendation
            case 4: // Recommended to Directorate -> Awaiting Revenue Directorate Approval
                $allocation->update([
                    'stage' => 4,
                    'status' => 'recommended',
                    'reviewed_by' => $allocation->reviewed_by ?: $userId,
                    'reviewed_at' => $allocation->reviewed_at ?: now(),
                    'officer_recommendation' => $data['officer_recommendation'] ?? $allocation->officer_recommendation,
                ]);
                break;

            case 5: // Approved by Directorate -> Awaiting Unit Allocation
                $allocation->update([
                    'stage' => 5,
                    'status' => 'approved',
                    'approved_by' => $userId,
                    'approved_at' => now(),
                    'approval_notes' => $data['approval_notes'] ?? $allocation->approval_notes,
                ]);
                break;

            case 6: // Unit Assigned -> Awaiting Payment
                $shopId = $data['shop_id'] ?? $allocation->shop_id;
                if ($shopId) {
                    $shop = Shop::findOrFail($shopId);
                    $shop->update(['status' => 'reserved']);
                    $allocation->update([
                        'shop_id' => $shop->id,
                        'rent_amount' => $shop->monthly_rent > 0 ? $shop->monthly_rent : $allocation->rent_amount,
                        'allocated_by' => $userId,
                        'allocated_at' => now(),
                        'stage' => 6,
                        'status' => 'allocated',
                    ]);

                    // Automatically generate polymorphic invoice with generic line items
                    $allocation->fresh()->getOrCreateAllocationInvoice();

                    // Send official allocation & payment breakdown email to applicant
                    if (!empty($allocation->applicant_email)) {
                        try {
                            Setting::configureMailer();
                        } catch (\Throwable $e) {
                            Log::warning("Could not apply dynamic mail settings: " . $e->getMessage());
                        }

                        try {
                            Mail::to($allocation->applicant_email)->send(
                                new ShopAllocatedPaymentMail($allocation->fresh(['market', 'shop']))
                            );
                        } catch (\Throwable $e) {
                            Log::warning("Failed to send shop allocation payment email to {$allocation->applicant_email}: " . $e->getMessage());
                        }
                    }
                }
                break;

            case 7: // Payment Settled -> Completed Certificate & Card
                $payRef = $data['payment_reference'] ?? $allocation->payment_reference ?? ('REC-YSLG-' . date('Y') . '-' . rand(100000, 999999));
                $allocation->update([
                    'stage' => 7,
                    'status' => 'completed',
                    'payment_status' => $data['payment_status'] ?? 'paid',
                    'payment_reference' => $payRef,
                    'paid_at' => $allocation->paid_at ?: now(),
                    'conditions' => $data['conditions'] ?? $allocation->conditions ?? 'Rent falls due on the 5th of each month. The unit may not be sublet or transferred without the written approval of the Council. Three consecutive months in arrears is grounds for revocation.',
                ]);

                // Reconcile associated invoice if pending
                $inv = $allocation->latestInvoice;
                if ($inv && $inv->status === 'pending') {
                    $inv->update(['status' => 'success']);
                }

                // If no Payment record exists yet (e.g. administrative settlement), create one
                if (!$allocation->payments()->exists()) {
                    Payment::create([
                        'invoice_id' => $inv?->id,
                        'establishment_id' => null,
                        'payable_type' => ShopAllocation::class,
                        'payable_id' => $allocation->id,
                        'amount' => (float)($allocation->rent_amount + $allocation->allocation_fee),
                        'status' => 'success',
                        'reference' => $payRef,
                        'gateway' => $inv?->gateway ?? 'administrative',
                        'officer_id' => $userId,
                        'metadata' => [
                            'notes' => 'Settled via Revenue Directorate Allocation Desk',
                            'paid_by' => $allocation->applicant_name,
                        ],
                    ]);
                }

                if ($allocation->shop) {
                    $allocation->shop->update([
                        'status' => 'occupied',
                        'current_occupant_name' => $allocation->applicant_name,
                        'current_occupant_phone' => $allocation->applicant_phone,
                        'current_occupant_nin' => $allocation->applicant_nin_bvn,
                        'current_allocation_id' => $allocation->id,
                    ]);
                }
                break;
        }

        return $allocation->fresh();
    }

    /**
     * Reject Application
     */
    public function reject(ShopAllocation $allocation, string $reason, ?int $userId = null): ShopAllocation
    {
        if ($allocation->shop && $allocation->shop->status === 'reserved') {
            $allocation->shop->update(['status' => 'vacant']);
        }

        $allocation->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $allocation;
    }

    /**
     * Request updates / additional information from applicant
     */
    public function requestUpdate(ShopAllocation $allocation, string $notes, ?int $userId = null): ShopAllocation
    {
        $allocation->update([
            'status' => 'action_required',
            'action_required_notes' => $notes,
            'action_requested_at' => now(),
        ]);

        // Dynamically apply database SMTP configuration
        try {
            Setting::configureMailer();
        } catch (\Throwable $e) {
            Log::warning("Could not apply dynamic mail settings: " . $e->getMessage());
        }

        // Send email to applicant requesting update
        try {
            Mail::to($allocation->applicant_email)->send(new ShopApplicationUpdateRequestMail($allocation, $notes));
        } catch (\Throwable $e) {
            Log::warning("Failed to send update request email to {$allocation->applicant_email}: " . $e->getMessage());
        }

        return $allocation->fresh();
    }

    /**
     * Move application back to a previous stage
     */
    public function revertStage(ShopAllocation $allocation, int $targetStage, string $notes = '', ?int $userId = null): ShopAllocation
    {
        $stageStatusMap = [
            1 => 'pending',
            2 => 'review',
            3 => 'recommended',
            4 => 'recommended',
            5 => 'approved',
            6 => 'allocated',
            7 => 'completed',
        ];

        $targetStatus = $stageStatusMap[$targetStage] ?? 'pending';

        // If reverting from unit allocation/payment (stage 6+) back to earlier stage, free the unit
        if ($allocation->stage >= 6 && $targetStage < 6 && $allocation->shop) {
            if ($allocation->shop->status === 'reserved') {
                $allocation->shop->update(['status' => 'vacant']);
            }
            $allocation->shop_id = null;
        }

        $updateData = [
            'stage' => $targetStage,
            'status' => $targetStatus,
        ];

        if (!empty($notes)) {
            $updateData['officer_recommendation'] = $allocation->officer_recommendation 
                ? ($allocation->officer_recommendation . "\n[Reverted to Stage {$targetStage}]: " . $notes)
                : "[Reverted to Stage {$targetStage}]: " . $notes;
        }

        $allocation->update($updateData);

        return $allocation->fresh();
    }

    /**
     * Reopen a rejected application back to Stage 1
     */
    public function reopenApplication(ShopAllocation $allocation, ?int $userId = null): ShopAllocation
    {
        $allocation->update([
            'stage' => 1,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        return $allocation->fresh();
    }

    /**
     * Update application by applicant (Public side)
     */
    public function updateByApplicant(ShopAllocation $allocation, array $data): ShopAllocation
    {
        $fields = [
            'applicant_name' => $data['applicant_name'] ?? $allocation->applicant_name,
            'applicant_phone' => $data['applicant_phone'] ?? $allocation->applicant_phone,
            'applicant_nin_bvn' => $data['applicant_nin_bvn'] ?? $allocation->applicant_nin_bvn,
            'applicant_address' => $data['applicant_address'] ?? $allocation->applicant_address,
            'trade_type' => $data['trade_type'] ?? $allocation->trade_type,
            'requested_size' => $data['requested_size'] ?? $allocation->requested_size,
            'status' => 'pending', // Resets back to pending review
            'action_responded_at' => now(),
        ];

        if (!empty($data['market_id'])) {
            $fields['market_id'] = $data['market_id'];
        }

        if (!empty($data['passport_photo'])) {
            $fields['passport_photo'] = $data['passport_photo'];
        }

        if (!empty($data['id_document'])) {
            $fields['id_document'] = $data['id_document'];
        }

        $allocation->update($fields);

        return $allocation->fresh();
    }
}
