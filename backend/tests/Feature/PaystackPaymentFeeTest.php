<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Establishment;
use App\Models\EstablishmentSize;
use App\Models\EstablishmentType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceSplit;
use App\Models\PaystackSubAccount;
use App\Models\RevenueHead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaystackPaymentFeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_paystack_service_fee_adjustment_on_checkout(): void
    {
        // 1. Create type and size dependencies
        $type = EstablishmentType::create([
            'key' => 'retail',
            'value' => 'Retail',
            'status' => true
        ]);
        $size = EstablishmentSize::create([
            'key' => 'medium',
            'value' => 'Medium',
            'status' => true
        ]);

        $establishment = Establishment::create([
            'unique_id' => 'EST-123456',
            'name' => 'Test Retail Store',
            'establishment_type_id' => $type->id,
            'establishment_size_id' => $size->id,
            'lga' => 'Jimeta',
            'ward' => 'Karewa',
            'lat' => 9.2035,
            'lng' => 12.4954,
            'street_address' => '10 Galadima Road',
            'house_number' => '10',
            'status' => 'approved',
            'base_year' => 2026,
        ]);

        // 2. Setup Agencies
        $agency1 = Agency::create([
            'name' => 'Agency 1',
            'code' => 'A1',
            'status' => true,
        ]);
        PaystackSubAccount::create([
            'agency_id' => $agency1->id,
            'subaccount_code' => 'ACCT_1',
            'active' => true,
        ]);

        $agency2 = Agency::create([
            'name' => 'Agency 2',
            'code' => 'A2',
            'status' => true,
        ]);
        PaystackSubAccount::create([
            'agency_id' => $agency2->id,
            'subaccount_code' => 'ACCT_2',
            'active' => true,
        ]);

        $serviceFeeAgency = Agency::create([
            'name' => 'Service Fee Agency',
            'code' => 'SFA',
            'status' => true,
            'is_service_fee' => true,
            'service_fee_amount' => 200.0,
        ]);
        PaystackSubAccount::create([
            'agency_id' => $serviceFeeAgency->id,
            'subaccount_code' => 'ACCT_SF',
            'active' => true,
        ]);

        $rule1 = RevenueHead::create([
            'id' => 1,
            'agency_id' => $agency1->id,
            'name' => 'Rule 1',
            'amount' => 5500.0,
            'frequency' => 'annual',
            'status' => 'active'
        ]);
        $rule2 = RevenueHead::create([
            'id' => 2,
            'agency_id' => $agency1->id,
            'name' => 'Rule 2',
            'amount' => 2160.0,
            'frequency' => 'annual',
            'status' => 'active'
        ]);
        $rule3 = RevenueHead::create([
            'id' => 3,
            'agency_id' => $agency2->id,
            'name' => 'Rule 3',
            'amount' => 5000.0,
            'frequency' => 'annual',
            'status' => 'active'
        ]);

        // 3. Tax items total = ₦12,660 (which makes total unadjusted = 12660 + 200 = 12860)
        $items = [
            [
                'rule_id' => 1,
                'agency_id' => $agency1->id,
                'agency_name' => 'Agency 1',
                'period' => '2026',
                'amount' => 5500.0
            ],
            [
                'rule_id' => 2,
                'agency_id' => $agency1->id,
                'agency_name' => 'Agency 1',
                'period' => '2026',
                'amount' => 2160.0
            ],
            [
                'rule_id' => 3,
                'agency_id' => $agency2->id,
                'agency_name' => 'Agency 2',
                'period' => '2026',
                'amount' => 5000.0
            ]
        ];

        // 4. Send checkout request for Paystack
        $response = $this->post('/public/pay', [
            'establishment_id' => $establishment->id,
            'items' => json_encode($items),
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'gateway' => 'Paystack',
            'amount' => 12860.0, // Unadjusted total (items sum 12660 + service fee 200)
        ]);

        // 5. Verify the response is successful
        $response->assertStatus(200);

        // 6. Verify invoice created in database is adjusted to cover Paystack fee
        // T_tax = 12660, S_set = 200, R = 3 (Agency 1: ACCT_1, Agency 2: ACCT_2, Service Fee: ACCT_SF).
        // T = 12958.13.
        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        
        // Assert invoice total is adjusted correctly (12,958.13 NGN)
        $this->assertEqualsWithDelta(12958.13, $invoice->total_amount, 0.02);

        // Assert service fee split amount is adjusted correctly (298.13 NGN)
        $sfSplit = InvoiceSplit::where('invoice_id', $invoice->id)->where('is_service_fee', true)->first();
        $this->assertNotNull($sfSplit);
        $this->assertEqualsWithDelta(298.13, $sfSplit->amount, 0.02);
    }

    public function test_paystack_equal_split_fee_distribution_on_success(): void
    {
        // 1. Create type and size dependencies
        $type = EstablishmentType::create([
            'key' => 'retail',
            'value' => 'Retail',
            'status' => true
        ]);
        $size = EstablishmentSize::create([
            'key' => 'medium',
            'value' => 'Medium',
            'status' => true
        ]);

        $establishment = Establishment::create([
            'unique_id' => 'EST-123456',
            'name' => 'Test Retail Store',
            'establishment_type_id' => $type->id,
            'establishment_size_id' => $size->id,
            'lga' => 'Jimeta',
            'ward' => 'Karewa',
            'lat' => 9.2035,
            'lng' => 12.4954,
            'street_address' => '10 Galadima Road',
            'house_number' => '10',
            'status' => 'approved',
            'base_year' => 2026,
        ]);

        $agency1 = Agency::create([
            'id' => 1,
            'name' => 'Agency 1',
            'code' => 'A1',
            'status' => true,
        ]);
        PaystackSubAccount::create([
            'agency_id' => 1,
            'subaccount_code' => 'ACCT_1',
            'active' => true,
        ]);

        $agency2 = Agency::create([
            'id' => 2,
            'name' => 'Agency 2',
            'code' => 'A2',
            'status' => true,
        ]);
        PaystackSubAccount::create([
            'agency_id' => 2,
            'subaccount_code' => 'ACCT_2',
            'active' => true,
        ]);

        $serviceFeeAgency = Agency::create([
            'id' => 3,
            'name' => 'Service Fee Agency',
            'code' => 'SFA',
            'status' => true,
            'is_service_fee' => true,
            'service_fee_amount' => 200.0,
        ]);
        PaystackSubAccount::create([
            'agency_id' => 3,
            'subaccount_code' => 'ACCT_SF',
            'active' => true,
        ]);

        RevenueHead::create([
            'id' => 1,
            'agency_id' => 1,
            'name' => 'Rule 1',
            'amount' => 5500.0,
            'frequency' => 'annual',
            'status' => 'active'
        ]);
        RevenueHead::create([
            'id' => 2,
            'agency_id' => 1,
            'name' => 'Rule 2',
            'amount' => 2160.0,
            'frequency' => 'annual',
            'status' => 'active'
        ]);
        RevenueHead::create([
            'id' => 3,
            'agency_id' => 2,
            'name' => 'Rule 3',
            'amount' => 5000.0,
            'frequency' => 'annual',
            'status' => 'active'
        ]);

        // 2. Setup Invoice with Splits manually (simulating Paystack transaction with total = 12958.13)
        $invoice = Invoice::create([
            'establishment_id' => $establishment->id,
            'reference' => 'INV-PAYSTACK-TEST-123',
            'gateway' => 'Paystack',
            'email' => 'test@example.com',
            'total_amount' => 12958.13,
            'status' => 'pending',
            'metadata' => ['simulate' => true]
        ]);

        // Add 3 splits
        InvoiceSplit::create([
            'invoice_id' => $invoice->id,
            'agency_id' => 1,
            'amount' => 7660.00, // Agency 1 (sum of 5500 + 2160)
            'ratio' => 59.1135,
            'subaccount_code' => 'ACCT_1',
            'is_service_fee' => false,
        ]);

        InvoiceSplit::create([
            'invoice_id' => $invoice->id,
            'agency_id' => 2,
            'amount' => 5000.00, // Agency 2
            'ratio' => 38.5858,
            'subaccount_code' => 'ACCT_2',
            'is_service_fee' => false,
        ]);

        InvoiceSplit::create([
            'invoice_id' => $invoice->id,
            'agency_id' => 3,
            'amount' => 298.13, // Service Fee
            'ratio' => 2.3007,
            'subaccount_code' => 'ACCT_SF',
            'is_service_fee' => true,
        ]);

        // Create InvoiceItem records for items relation
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'revenue_head_id' => 1,
            'agency_id' => 1,
            'period' => '2026',
            'amount' => 5500.0,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'revenue_head_id' => 2,
            'agency_id' => 1,
            'period' => '2026',
            'amount' => 2160.0,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'revenue_head_id' => 3,
            'agency_id' => 2,
            'period' => '2026',
            'amount' => 5000.0,
        ]);

        // 3. Call verification endpoint (simulate=true)
        $response = $this->get('/public/payment-success/INV-PAYSTACK-TEST-123?action=verify&simulate=true');
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Total fee = 12958.13 * 0.015 + 100 = 294.37.
        // Recipients count = 3 (ACCT_1, ACCT_2, ACCT_SF).
        // Fee per recipient = ceil(294.37 / 3) = 98.13.
        // Check net amounts in database:
        // - Agency 1 net: 7660 - 98.13 = 7561.87
        // - Agency 2 net: 5000 - 98.13 = 4901.87
        // - Service Fee net: 298.13 - 98.13 = 200.00! (Matches the set service fee exactly!)
        $sfSplit = InvoiceSplit::where('invoice_id', $invoice->id)->where('is_service_fee', true)->first();
        $this->assertEquals(200.00, $sfSplit->net_amount);

        $a1Split = InvoiceSplit::where('invoice_id', $invoice->id)->where('agency_id', 1)->first();
        $this->assertEquals(7561.87, $a1Split->net_amount);

        $a2Split = InvoiceSplit::where('invoice_id', $invoice->id)->where('agency_id', 2)->first();
        $this->assertEquals(4901.87, $a2Split->net_amount);
    }
}
