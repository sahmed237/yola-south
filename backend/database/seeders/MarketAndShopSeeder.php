<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Market;
use App\Models\Shop;
use App\Models\ShopAllocation;
use App\Models\Ward;
use App\Models\User;

class MarketAndShopSeeder extends Seeder
{
    public function run(): void
    {
        $marketsData = [
            ['code' => 'MKT-ADR-001', 'name' => 'Adarawo Market', 'ward' => 'Adarawo', 'blocks' => 8, 'rev' => 44600000.00],
            ['code' => 'MKT-NGR-002', 'name' => 'Ngurore Central Market', 'ward' => 'Ngurore', 'blocks' => 6, 'rev' => 36200000.00],
            ['code' => 'MKT-MKM-003', 'name' => 'Makama Lock-up Shops', 'ward' => "Makama 'A'", 'blocks' => 4, 'rev' => 21400000.00],
            ['code' => 'MKT-NAM-004', 'name' => 'Namtari Market', 'ward' => 'Namtari', 'blocks' => 5, 'rev' => 18700000.00],
            ['code' => 'MKT-BYP-005', 'name' => 'Bole Yolde Pate Market', 'ward' => 'Bole Yolde Pate', 'blocks' => 3, 'rev' => 11300000.00],
            ['code' => 'MKT-YKC-006', 'name' => 'Yolde Kohi Cattle Market', 'ward' => 'Yolde Kohi', 'blocks' => 2, 'rev' => 9800000.00],
        ];

        $marketMap = [];
        foreach ($marketsData as $mData) {
            $ward = Ward::where('name', 'like', '%' . $mData['ward'] . '%')->first();
            $market = Market::updateOrCreate(
                ['code' => $mData['code']],
                [
                    'name' => $mData['name'],
                    'ward_id' => $ward?->id,
                    'ward_name' => $mData['ward'],
                    'blocks_count' => $mData['blocks'],
                    'revenue_ytd' => $mData['rev'],
                    'status' => 'active',
                    'address' => "{$mData['ward']} Ward, Yola South LGA",
                    'description' => "Municipal commercial market operating in {$mData['ward']} ward with lock-up shops, open stalls and storage units.",
                ]
            );
            $marketMap[$mData['name']] = $market;
        }

        $shopsData = [
            ['id' => 'YSLG-SHP-000412', 'market' => 'Makama Lock-up Shops', 'b' => 'Block B', 'no' => 'B12', 'sz' => '3.0 × 4.0 m', 'oc' => 'Hauwa Ibrahim', 'rent' => 18000.00, 's' => 'occupied'],
            ['id' => 'YSLG-SHP-000413', 'market' => 'Makama Lock-up Shops', 'b' => 'Block B', 'no' => 'B13', 'sz' => '3.0 × 4.0 m', 'oc' => null, 'rent' => 18000.00, 's' => 'vacant'],
            ['id' => 'YSLG-SHP-001120', 'market' => 'Adarawo Market', 'b' => 'Block D', 'no' => 'D31', 'sz' => '2.5 × 3.0 m', 'oc' => 'Zainab Umar', 'rent' => 9000.00, 's' => 'arrears'],
            ['id' => 'YSLG-SHP-001121', 'market' => 'Adarawo Market', 'b' => 'Block D', 'no' => 'D32', 'sz' => '2.5 × 3.0 m', 'oc' => 'Salamatu Adamu', 'rent' => 9000.00, 's' => 'occupied'],
            ['id' => 'YSLG-SHP-000876', 'market' => 'Ngurore Central Market', 'b' => 'Block A', 'no' => 'A07', 'sz' => '4.0 × 5.0 m', 'oc' => 'Alhaji Musa Bello', 'rent' => 24000.00, 's' => 'occupied'],
            ['id' => 'YSLG-SHP-000877', 'market' => 'Ngurore Central Market', 'b' => 'Block A', 'no' => 'A08', 'sz' => '4.0 × 5.0 m', 'oc' => null, 'rent' => 24000.00, 's' => 'vacant'],
            ['id' => 'YSLG-SHP-000559', 'market' => 'Namtari Market', 'b' => 'Block C', 'no' => 'C04', 'sz' => '3.0 × 3.0 m', 'oc' => 'Fulbe Dairy Cooperative', 'rent' => 12000.00, 's' => 'occupied'],
            ['id' => 'YSLG-SHP-000208', 'market' => 'Bole Yolde Pate Market', 'b' => 'Block A', 'no' => 'A15', 'sz' => '2.5 × 3.0 m', 'oc' => 'Rukayya Bappa', 'rent' => 9000.00, 's' => 'occupied'],
        ];

        $shopMap = [];
        foreach ($shopsData as $sData) {
            $market = $marketMap[$sData['market']] ?? null;
            if (!$market) continue;

            $shop = Shop::updateOrCreate(
                ['shop_code' => $sData['id']],
                [
                    'market_id' => $market->id,
                    'block_name' => $sData['b'],
                    'shop_number' => $sData['no'],
                    'size' => $sData['sz'],
                    'type' => 'Lock-up shop',
                    'monthly_rent' => $sData['rent'],
                    'annual_rent' => $sData['rent'] * 12,
                    'status' => $sData['s'],
                    'current_occupant_name' => $sData['oc'],
                    'current_occupant_phone' => $sData['oc'] ? '080' . rand(10000000, 99999999) : null,
                ]
            );
            $shopMap[$sData['id']] = $shop;
        }

        $superAdmin = User::first();

        // Sample Allocations
        $allocationsData = [
            [
                'id' => 'ALL-YSLG-2026-000188',
                'name' => 'Halima Zubairu',
                'phone' => '08031234567',
                'email' => 'halima.zubairu@example.com',
                'trade' => 'Textiles & Fashion',
                'market' => 'Makama Lock-up Shops',
                'shop_code' => 'YSLG-SHP-000413',
                'stage' => 3,
                'status' => 'review',
                'rent' => 18000.00,
                'date' => '2026-09-02 10:15:00',
            ],
            [
                'id' => 'ALL-YSLG-2026-000186',
                'name' => 'Jerome Vandi',
                'phone' => '08054472210',
                'email' => 'jerome.vandi@example.com',
                'trade' => 'Electronics repair',
                'market' => 'Ngurore Central Market',
                'shop_code' => 'YSLG-SHP-000877',
                'stage' => 7,
                'status' => 'completed',
                'rent' => 24000.00,
                'payment_status' => 'paid',
                'payment_ref' => 'REC-YSLG-2026-002890',
                'date' => '2026-08-28 09:30:00',
            ],
            [
                'id' => 'ALL-YSLG-2026-000181',
                'name' => 'Grace Ndayako',
                'phone' => '08029988776',
                'email' => 'grace.ndayako@example.com',
                'trade' => 'Provisions & Groceries',
                'market' => 'Adarawo Market',
                'shop_code' => 'YSLG-SHP-001121',
                'stage' => 7,
                'status' => 'completed',
                'rent' => 9000.00,
                'payment_status' => 'paid',
                'date' => '2026-08-21 14:20:00',
            ],
            [
                'id' => 'ALL-YSLG-2026-000179',
                'name' => 'Ibrahim Danladi',
                'phone' => '08145566778',
                'email' => 'ibrahim.danladi@example.com',
                'trade' => 'Grains & Cereals wholesale',
                'market' => 'Namtari Market',
                'shop_code' => 'YSLG-SHP-000559',
                'stage' => 2,
                'status' => 'pending',
                'rent' => 12000.00,
                'date' => '2026-08-18 11:00:00',
            ],
        ];

        foreach ($allocationsData as $aData) {
            $market = $marketMap[$aData['market']] ?? null;
            $shop = $shopMap[$aData['shop_code']] ?? null;
            if (!$market) continue;

            $alloc = ShopAllocation::updateOrCreate(
                ['application_no' => $aData['id']],
                [
                    'market_id' => $market->id,
                    'shop_id' => $shop?->id,
                    'applicant_name' => $aData['name'],
                    'applicant_phone' => $aData['phone'],
                    'applicant_email' => $aData['email'],
                    'applicant_address' => "{$market->ward_name}, Yola South",
                    'trade_type' => $aData['trade'],
                    'requested_size' => $shop?->size ?? '3.0 × 4.0 m',
                    'stage' => $aData['stage'],
                    'status' => $aData['status'],
                    'rent_amount' => $aData['rent'],
                    'allocation_fee' => 5000.00,
                    'payment_status' => $aData['payment_status'] ?? 'unpaid',
                    'payment_reference' => $aData['payment_ref'] ?? null,
                    'reviewed_by' => $superAdmin?->id,
                    'approved_by' => $aData['stage'] >= 4 ? $superAdmin?->id : null,
                    'allocated_by' => $aData['stage'] >= 5 ? $superAdmin?->id : null,
                    'reviewed_at' => $aData['stage'] >= 2 ? now()->subDays(5) : null,
                    'approved_at' => $aData['stage'] >= 4 ? now()->subDays(3) : null,
                    'allocated_at' => $aData['stage'] >= 5 ? now()->subDays(2) : null,
                    'paid_at' => ($aData['payment_status'] ?? '') === 'paid' ? now()->subDay() : null,
                    'officer_recommendation' => 'Applicant verified as genuine resident and reputable trader. Unit allocation recommended.',
                    'approval_notes' => 'Approved by Revenue Directorate subject to compliance with market bye-laws.',
                    'conditions' => 'Rent falls due on the 5th of each month. The unit may not be sublet or transferred without written approval of the Council. Three consecutive months in arrears is grounds for revocation.',
                    'created_at' => $aData['date'],
                ]
            );

            if ($aData['stage'] >= 7 && $shop) {
                $shop->update([
                    'current_occupant_name' => $aData['name'],
                    'current_occupant_phone' => $aData['phone'],
                    'status' => 'occupied',
                    'current_allocation_id' => $alloc->id,
                ]);
            }
        }
    }
}
