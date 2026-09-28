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
        $email = 'testapplicant_' . uniqid() . '@example.com';

        // 1. Initiate OTP
        $initiateResponse = $this->postJson(route('public.shop-application.initiate-otp'), [
            'email' => $email
        ]);
        $initiateResponse->assertStatus(200);
        $initiateResponse->assertJson(['success' => true]);

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

        // Public track status
        $trackResponse = $this->get(route('public.shop-application.track-status', $allocation->application_no));
        $trackResponse->assertStatus(200);
        $trackResponse->assertSee($allocation->application_no);
        $trackResponse->assertSee('Bello Sani');
    }

    public function test_allocation_workflow_advancement_and_card()
    {
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

        // Review (Stage 2/3)
        $reviewRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.review', $allocation), [
            'officer_recommendation' => 'Applicant verified, recommended for allocation.',
            'advance_to_recommend' => 1
        ]);
        $reviewRes->assertRedirect();
        $this->assertEquals(3, $allocation->fresh()->stage);

        // Approval (Stage 4)
        $approveRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.approve', $allocation), [
            'approval_notes' => 'Approved by Director of Revenue.'
        ]);
        $approveRes->assertRedirect();
        $this->assertEquals(4, $allocation->fresh()->stage);

        // Allocation (Stage 5)
        $allocRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.allocate', $allocation), [
            'shop_id' => $this->testShop->id
        ]);
        $allocRes->assertRedirect();
        $this->assertEquals(5, $allocation->fresh()->stage);

        // Payment (Stage 6)
        $payRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.payment', $allocation), [
            'payment_reference' => 'REC-TEST-12345'
        ]);
        $payRes->assertRedirect();
        $this->assertEquals(6, $allocation->fresh()->stage);

        // Complete & Certificate (Stage 7)
        $compRes = $this->actingAs($this->superAdmin)->post(route('admin.allocations.complete', $allocation), [
            'conditions' => 'Standard council conditions apply.'
        ]);
        $compRes->assertRedirect(route('admin.allocations.card', $allocation));
        $this->assertEquals(7, $allocation->fresh()->stage);

        // View Certificate & Card
        $cardRes = $this->actingAs($this->superAdmin)->get(route('admin.allocations.card', $allocation));
        $cardRes->assertStatus(200);
        $cardRes->assertSee('Commercial Unit Allocation Certificate');
        $cardRes->assertSee('Fatima Aliyu');
    }
}
