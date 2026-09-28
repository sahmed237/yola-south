<?php

namespace App\Services;

use App\Models\ShopAllocation;
use App\Models\Shop;
use App\Models\Market;
use Illuminate\Support\Str;

class ShopAllocationService
{
    /**
     * Generate unique application / allocation reference: e.g. ALL-YSLG-2026-000188
     */
    public function generateApplicationNo(): string
    {
        $year = date('Y');
        $prefix = "ALL-YSLG-{$year}-";

        $last = ShopAllocation::where('application_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($last && preg_match('/ALL-YSLG-\d{4}-(\d+)/', $last->application_no, $matches)) {
            $nextSeq = str_pad((int) $matches[1] + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '000189';
        }

        return $prefix . $nextSeq;
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
    public function advanceStage(ShopAllocation $allocation, int $targetStage, array $data = [], int $userId = null): ShopAllocation
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
                $allocation->update([
                    'stage' => 3,
                    'status' => 'recommended',
                    'officer_recommendation' => $data['officer_recommendation'] ?? $allocation->officer_recommendation,
                ]);
                break;

            case 4: // Approval
                $allocation->update([
                    'stage' => 4,
                    'status' => 'approved',
                    'approved_by' => $userId,
                    'approved_at' => now(),
                    'approval_notes' => $data['approval_notes'] ?? $allocation->approval_notes,
                ]);
                break;

            case 5: // Unit Allocation
                $shopId = $data['shop_id'] ?? $allocation->shop_id;
                if ($shopId) {
                    $shop = Shop::findOrFail($shopId);
                    $shop->update(['status' => 'reserved']);
                    $allocation->update([
                        'shop_id' => $shop->id,
                        'rent_amount' => $shop->monthly_rent > 0 ? $shop->monthly_rent : $allocation->rent_amount,
                        'allocated_by' => $userId,
                        'allocated_at' => now(),
                        'stage' => 5,
                        'status' => 'allocated',
                    ]);
                }
                break;

            case 6: // Invoice & Payment
                $allocation->update([
                    'stage' => 6,
                    'status' => 'payment',
                    'payment_status' => $data['payment_status'] ?? 'paid',
                    'payment_reference' => $data['payment_reference'] ?? 'REC-YSLG-' . date('Y') . '-' . rand(100000, 999999),
                    'paid_at' => now(),
                ]);
                break;

            case 7: // Letter & Card Issued
                $allocation->update([
                    'stage' => 7,
                    'status' => 'completed',
                    'conditions' => $data['conditions'] ?? $allocation->conditions ?? 'Rent falls due on the 5th of each month. The unit may not be sublet or transferred without the written approval of the Council. Three consecutive months in arrears is grounds for revocation.',
                ]);

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
    public function reject(ShopAllocation $allocation, string $reason, int $userId = null): ShopAllocation
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
}
