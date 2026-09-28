<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Market;
use App\Models\Shop;
use App\Models\ShopAllocation;
use App\Models\ShopApplicationOtp;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MarketAndShopManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Market $testMarket;
    protected Shop $testShop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@urcs.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('super-admin');

        $this->testMarket = Market::create([
            'name' => 'Adarawo Market',
            'code' => 'MKT-ADR-001',
            'ward_name' => 'Adarawo',
            'blocks_count' => 8,
            'revenue_ytd' => 44600000.00,
            'status' => 'active',
        ]);

        $this->testShop = Shop::create([
            'market_id' => $this->testMarket->id,
            'shop_code' => 'YSLG-SHP-000412',
            'block_name' => 'Block B',
            'shop_number' => 'B12',
            'size' => '3.0 × 4.0 m',
            'monthly_rent' => 18000.00,
            'annual_rent' => 216000.00,
            'status' => 'vacant',
        ]);
    }

    public function test_admin_can_view_markets_index()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.markets.index'));
        $response->assertStatus(200);
        $response->assertSee('Markets and shops');
        $response->assertSee('Adarawo Market');
    }

    public function test_admin_creating_market_redirects_to_market_show_page()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.markets.store'), [
            'name' => 'Toungo Timber Market',
            'code' => 'MKT-TNG-009',
            'ward_name' => 'Toungo',
            'blocks_count' => 3,
            'status' => 'active',
            'address' => 'Toungo Sawmill Road',
        ]);

        $market = Market::where('code', 'MKT-TNG-009')->first();
        $this->assertNotNull($market);
        $response->assertRedirect(route('admin.markets.show', $market));
    }

    public function test_admin_can_view_shops_index()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.shops.index'));
        $response->assertStatus(200);
        $response->assertSee('Shop inventory');
        $response->assertSee('YSLG-SHP-000412');
    }

    public function test_admin_can_view_allocations_index()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.allocations.index'));
        $response->assertStatus(200);
        $response->assertSee('Shop allocations');
    }

    public function test_public_can_view_shop_application_landing()
    {
        $response = $this->get(route('public.shop-application.index'));
        $response->assertStatus(200);
        $response->assertSee('Apply for Market Shop or Stall');
        $response->assertSee('Adarawo Market');
    }

    public function test_public_can_initiate_and_verify_otp()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $email = 'testapplicant_' . uniqid() . '@example.com';

        // 1. Initiate OTP
        $initiateResponse = $this->postJson(route('public.shop-application.initiate-otp'), [
            'email' => $email
        ]);
        $initiateResponse->assertStatus(200);
        $initiateResponse->assertJson(['success' => true]);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ShopApplicationOtpMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });

        $otpRecord = ShopApplicationOtp::where('email', $email)->latest()->first();
        $this->assertNotNull($otpRecord);

        // 2. Verify OTP
        $verifyResponse = $this->postJson(route('public.shop-application.verify-otp'), [
            'email' => $email,
            'otp' => $otpRecord->otp
        ]);
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJson(['success' => true]);
    }

    public function test_public_can_submit_application_and_track()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $email = 'applicant_' . uniqid() . '@example.com';
        session(['shop_app_verified_email' => $email]);

        $response = $this->post(route('public.shop-application.submit'), [
            'email' => $email,
            'applicant_name' => 'Bello Sani',
            'applicant_phone' => '08099887766',
            'applicant_address' => 'Makama Ward, Yola South',
            'market_id' => $this->testMarket->id,
            'trade_type' => 'Retail — Provisions & Groceries',
            'requested_size' => '3.0 × 4.0 m',
        ]);

        $allocation = ShopAllocation::where('applicant_email', $email)->first();
        $this->assertNotNull($allocation);
        $response->assertRedirect(route('public.shop-application.track-status', $allocation->application_no));

        // Assert submission confirmation email sent with tracking ID
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ShopApplicationSubmittedMail::class, function ($mail) use ($email, $allocation) {
            return $mail->hasTo($email) && $mail->allocation->application_no === $allocation->application_no;
        });

        // Public track status
        $trackResponse = $this->get(route('public.shop-application.track-status', $allocation->application_no));
        $trackResponse->assertStatus(200);
        $trackResponse->assertSee($allocation->application_no);
        $trackResponse->assertSee('Bello Sani');
    }

    public function test_allocation_workflow_advancement_and_card()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $allocation = ShopAllocation::create([
            'application_no' => 'ALL-YSLG-' . date('Y') . '-' . rand(200000, 999999),
            'market_id' => $this->testMarket->id,
            'applicant_name' => 'Fatima Aliyu',
            'applicant_phone' => '08011223344',
            'applicant_email' => 'fatima_' . uniqid() . '@example.com',
            'trade_type' => 'Textiles & Fashion Retail',
            'requested_size' => '3.0 × 4.0 m',
            'stage' => 1,
            'status' => 'pending',
            'rent_amount' => 15000.00,
            'allocation_fee' => 5000.00,
        ]);

        // Review (Stage 4)
        $reviewRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.review', $allocation), [
            'officer_recommendation' => 'Applicant verified, recommended for allocation.',
            'advance_to_recommend' => 1
        ]);
        $reviewRes->assertRedirect();
        $this->assertEquals(4, $allocation->fresh()->stage);

        // Approval (Stage 5)
        $approveRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.approve', $allocation), [
            'approval_notes' => 'Approved by Director of Revenue.'
        ]);
        $approveRes->assertRedirect();
        $this->assertEquals(5, $allocation->fresh()->stage);

        // Allocation (Stage 6) -> Assign Shop Unit & Dispatch Allocation + Payment Email
        $allocRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.allocate', $allocation), [
            'shop_id' => $this->testShop->id
        ]);
        $allocRes->assertRedirect();
        $this->assertEquals(6, $allocation->fresh()->stage);

        // Assert ShopAllocatedPaymentMail was sent to applicant
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ShopAllocatedPaymentMail::class, function ($mail) use ($allocation) {
            return $mail->hasTo($allocation->applicant_email) && 
                   $mail->allocation->id === $allocation->id &&
                   $mail->allocation->shop_id === $this->testShop->id;
        });

        // Payment (Stage 7 - Completed Certificate & Card)
        $payRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.payment', $allocation), [
            'payment_reference' => 'REC-TEST-12345'
        ]);
        $payRes->assertRedirect();
        $this->assertEquals(7, $allocation->fresh()->stage);

        // View Certificate & Card
        $cardRes = $this->actingAs($this->superAdmin)->get(route('admin.allocations.card', $allocation));
        $cardRes->assertStatus(200);
        $cardRes->assertSee('Commercial Unit Allocation Certificate');
        $cardRes->assertSee('Fatima Aliyu');
    }

    public function test_request_update_sends_email_and_applicant_updates_via_tracking_id()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $applicantEmail = 'applicant_' . uniqid() . '@example.com';
        $allocation = ShopAllocation::create([
            'application_no' => 'ALL-YSLG-' . date('Y') . '-' . rand(200000, 999999),
            'market_id' => $this->testMarket->id,
            'applicant_name' => 'Usman Danladi',
            'applicant_phone' => '08099887766',
            'applicant_email' => $applicantEmail,
            'trade_type' => 'Provisions & Groceries',
            'requested_size' => '3.0 × 4.0 m',
            'stage' => 1,
            'status' => 'pending',
            'rent_amount' => 12000.00,
            'allocation_fee' => 5000.00,
        ]);

        // 1. Officer requests update from applicant in Stage 1
        $requestRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.request-update', $allocation), [
            'update_notes' => 'Please update your residential address and upload your valid NIN document.'
        ]);
        $requestRes->assertRedirect();

        $allocation->refresh();
        $this->assertEquals('action_required', $allocation->status);
        $this->assertStringContainsString('residential address', $allocation->action_required_notes);
        $this->assertNotNull($allocation->action_requested_at);

        // Assert update request email sent
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ShopApplicationUpdateRequestMail::class, function ($mail) use ($applicantEmail, $allocation) {
            return $mail->hasTo($applicantEmail) && $mail->allocation->application_no === $allocation->application_no;
        });

        // 2. Applicant visits update page using tracking ID
        $editRes = $this->get(route('public.shop-application.edit', $allocation->application_no));
        $editRes->assertStatus(200);
        $editRes->assertSee($allocation->application_no);
        $editRes->assertSee('Please update your residential address');

        // 3. Applicant submits updated details
        $updateRes = $this->post(route('public.shop-application.update', $allocation->application_no), [
            'applicant_name' => 'Usman Danladi Updated',
            'applicant_phone' => '08099887766',
            'applicant_nin_bvn' => '22334455667',
            'applicant_address' => 'Plot 45 Lamido Zubairu Way, Yola South',
            'trade_type' => 'Provisions, Beverages & Groceries',
        ]);
        $updateRes->assertRedirect(route('public.shop-application.track-status', $allocation->application_no));

        $allocation->refresh();
        $this->assertEquals('pending', $allocation->status);
        $this->assertEquals('Usman Danladi Updated', $allocation->applicant_name);
        $this->assertEquals('Plot 45 Lamido Zubairu Way, Yola South', $allocation->applicant_address);
        $this->assertNotNull($allocation->action_responded_at);
    }

    public function test_revert_stage_and_reopen_application_workflow()
    {
        $allocation = ShopAllocation::create([
            'application_no' => 'ALL-YSLG-' . date('Y') . '-' . rand(200000, 999999),
            'market_id' => $this->testMarket->id,
            'applicant_name' => 'Aisha Mohammed',
            'applicant_phone' => '08033445566',
            'applicant_email' => 'aisha_' . uniqid() . '@example.com',
            'trade_type' => 'Cosmetics',
            'requested_size' => '3.0 × 4.0 m',
            'stage' => 4,
            'status' => 'approved',
            'rent_amount' => 15000.00,
            'allocation_fee' => 5000.00,
        ]);

        // 1. Revert from Stage 4 back to Stage 2
        $revertRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.revert', $allocation), [
            'target_stage' => 2,
            'revert_notes' => 'Need additional committee verification of trade license.'
        ]);
        $revertRes->assertRedirect();

        $allocation->refresh();
        $this->assertEquals(2, $allocation->stage);
        $this->assertEquals('review', $allocation->status);
        $this->assertStringContainsString('Reverted to Stage 2', $allocation->officer_recommendation);

        // 2. Reject application
        $rejectRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.reject', $allocation), [
            'rejection_reason' => 'Trade category prohibited in this section of the market.'
        ]);
        $rejectRes->assertRedirect();

        $allocation->refresh();
        $this->assertEquals('rejected', $allocation->status);
        $this->assertEquals('Trade category prohibited in this section of the market.', $allocation->rejection_reason);

        // 3. Reopen application back to Stage 1
        $reopenRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.reopen', $allocation));
        $reopenRes->assertRedirect();

        $allocation->refresh();
        $this->assertEquals(1, $allocation->stage);
        $this->assertEquals('pending', $allocation->status);
        $this->assertNull($allocation->rejection_reason);
    }

    public function test_unauthenticated_visitor_requires_email_otp_to_access_tracking()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $applicantEmail = 'sec_applicant_' . uniqid() . '@example.com';
        $allocation = ShopAllocation::create([
            'application_no' => 'ALL-YSLG-' . date('Y') . '-' . rand(200000, 999999),
            'market_id' => $this->testMarket->id,
            'applicant_name' => 'Khadija Umar',
            'applicant_phone' => '08055443322',
            'applicant_email' => $applicantEmail,
            'trade_type' => 'Tailoring & Embroidery',
            'requested_size' => '3.0 × 4.0 m',
            'stage' => 1,
            'status' => 'pending',
            'rent_amount' => 10000.00,
            'allocation_fee' => 5000.00,
        ]);

        // 1. Direct unauthenticated request to track status without token or session
        $res = $this->get(route('public.shop-application.track-status', $allocation->application_no));
        $res->assertRedirect(route('public.shop-application.track-verify', [
            'application_no' => $allocation->application_no,
            'redirect' => route('public.shop-application.track-status', $allocation->application_no),
        ]));

        // 2. View security verification challenge screen
        $verifyScreen = $this->get(route('public.shop-application.track-verify', $allocation->application_no));
        $verifyScreen->assertStatus(200);
        $verifyScreen->assertSee('Security Verification');
        $verifyScreen->assertSee($allocation->application_no);

        // 3. Send tracking OTP
        $sendRes = $this->postJson(route('public.shop-application.track-send-otp', $allocation->application_no));
        $sendRes->assertStatus(200);
        $sendRes->assertJson(['success' => true]);

        // Assert OTP email was sent to applicant's email
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ShopApplicationOtpMail::class, function ($mail) use ($applicantEmail) {
            return $mail->hasTo($applicantEmail);
        });

        // 4. Verify invalid OTP fails
        $badVerify = $this->postJson(route('public.shop-application.track-verify-otp', $allocation->application_no), [
            'otp' => '000000',
        ]);
        $badVerify->assertStatus(422);

        // 5. Verify valid OTP succeeds
        $latestOtp = \App\Models\ShopApplicationOtp::where('email', $applicantEmail)->latest()->first();
        $this->assertNotNull($latestOtp);

        $goodVerify = $this->postJson(route('public.shop-application.track-verify-otp', $allocation->application_no), [
            'otp' => $latestOtp->otp,
            'redirect' => route('public.shop-application.track-status', $allocation->application_no),
        ]);
        $goodVerify->assertStatus(200);
        $goodVerify->assertJson(['success' => true]);

        // 6. Now the applicant has authorized access in their session
        $authorizedTrack = $this->get(route('public.shop-application.track-status', $allocation->application_no));
        $authorizedTrack->assertStatus(200);
        $authorizedTrack->assertSee('Khadija Umar');
        $authorizedTrack->assertSee('08055443322');
    }

    public function test_applicant_can_checkout_and_settle_invoice_with_generic_items()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $applicantEmail = 'pay_applicant_' . uniqid() . '@example.com';
        $allocation = ShopAllocation::create([
            'application_no' => 'ALL-YSLG-' . date('Y') . '-' . rand(200000, 999999),
            'market_id' => $this->testMarket->id,
            'shop_id' => $this->testShop->id,
            'applicant_name' => 'Maryam Ibrahim',
            'applicant_phone' => '08022334455',
            'applicant_email' => $applicantEmail,
            'trade_type' => 'Grains & Cereals',
            'requested_size' => '3.0 × 4.0 m',
            'stage' => 5,
            'status' => 'approved',
            'rent_amount' => 18000.00,
            'allocation_fee' => 5000.00,
        ]);

        // 1. Officer allocates shop (Stage 6) -> automatically provisions polymorphic invoice and generic items
        $allocRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.allocate', $allocation), [
            'shop_id' => $this->testShop->id
        ]);
        $allocRes->assertRedirect();

        $allocation->refresh();
        $this->assertEquals(6, $allocation->stage);
        $this->assertEquals('allocated', $allocation->status);
        $this->assertNotNull($allocation->invoice_no);

        // Assert polymorphic invoice exists
        $invoice = $allocation->latestInvoice;
        $this->assertNotNull($invoice);
        $this->assertEquals(\App\Models\ShopAllocation::class, $invoice->invoiceable_type);
        $this->assertEquals($allocation->id, $invoice->invoiceable_id);
        $this->assertNull($invoice->establishment_id);
        $this->assertEquals(23000.00, $invoice->total_amount);
        $this->assertEquals('pending', $invoice->status);

        // Assert generic invoice items exist (not using existing revenue_rule invoice_items)
        $this->assertCount(2, $invoice->genericItems);
        $itemNames = $invoice->genericItems->pluck('item_name')->toArray();
        $this->assertContains('Allocation & Documentation Fee', $itemNames);
        $this->assertTrue(collect($itemNames)->contains(fn($name) => str_contains($name, 'Initial Monthly Rent')));

        // 2. Applicant visits checkout page with session authorization
        $checkoutRes = $this->withSession(["verified_tracking_{$allocation->application_no}" => true])
            ->get(route('public.shop-application.checkout', $allocation->application_no));
        $checkoutRes->assertStatus(200);
        $checkoutRes->assertSee('Settle Unit Allocation Invoice');
        $checkoutRes->assertSee('Allocation &amp; Documentation Fee', false);
        $checkoutRes->assertSee('23,000.00');

        // 3. Payment callback settles invoice and advances to Stage 7
        $callbackRes = $this->get(route('public.shop-application.payment-callback', [
            'application_no' => $allocation->application_no,
            'reference' => $invoice->reference,
            'simulate' => 'true'
        ]));
        $callbackRes->assertRedirect(route('public.shop-application.track-status', $allocation->application_no));

        // Assert invoice is success
        $invoice->refresh();
        $this->assertEquals('success', $invoice->status);

        // Assert polymorphic payment record was created
        $payment = \App\Models\Payment::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(\App\Models\ShopAllocation::class, $payment->payable_type);
        $this->assertEquals($allocation->id, $payment->payable_id);
        $this->assertNull($payment->establishment_id);
        $this->assertEquals(23000.00, $payment->amount);
        $this->assertEquals('success', $payment->status);

        // Assert allocation completed
        $allocation->refresh();
        $this->assertEquals(7, $allocation->stage);
        $this->assertEquals('completed', $allocation->status);
        $this->assertEquals('paid', $allocation->payment_status);
        $this->assertEquals('occupied', $this->testShop->fresh()->status);
    }

    public function test_admin_can_re_query_gateway_and_resend_payment_notice()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $applicantEmail = 'recheck_applicant_' . uniqid() . '@example.com';
        $allocation = ShopAllocation::create([
            'application_no' => 'ALL-YSLG-' . date('Y') . '-' . rand(200000, 999999),
            'market_id' => $this->testMarket->id,
            'shop_id' => $this->testShop->id,
            'applicant_name' => 'Ibrahim Galadima',
            'applicant_phone' => '08033221100',
            'applicant_email' => $applicantEmail,
            'trade_type' => 'Spices & Condiments',
            'requested_size' => '3.0 × 4.0 m',
            'stage' => 6,
            'status' => 'allocated',
            'rent_amount' => 15000.00,
            'allocation_fee' => 5000.00,
            'payment_status' => 'unpaid',
        ]);

        $invoice = $allocation->getOrCreateAllocationInvoice('monnify');

        // 1. Admin resends payment notice
        $resendRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.resend-payment-notice', $allocation));
        $resendRes->assertRedirect();
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ShopAllocatedPaymentMail::class, function ($mail) use ($applicantEmail) {
            return $mail->hasTo($applicantEmail);
        });

        // 2. Admin re-queries gateway status (simulated success)
        $verifyRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.verify-payment', $allocation), [
            'simulate' => true,
        ]);
        $verifyRes->assertRedirect();

        $allocation->refresh();
        $this->assertEquals(7, $allocation->stage);
        $this->assertEquals('completed', $allocation->status);
        $this->assertEquals('paid', $allocation->payment_status);
        $this->assertEquals('success', $invoice->fresh()->status);
    }
}

