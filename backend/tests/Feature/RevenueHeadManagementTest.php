<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Establishment;
use App\Models\EstablishmentSize;
use App\Models\EstablishmentType;
use App\Models\RevenueHead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RevenueHeadManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('view all invalid establishment');

        $superAdminRole = Role::findOrCreate('super-admin');
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superAdminRole);

        $this->agency = Agency::create([
            'name' => 'Internal Revenue Agency',
            'code' => 'IRA',
            'status' => true,
            'is_service_fee' => false,
        ]);
    }

    public function test_super_admin_can_view_revenue_heads_index(): void
    {
        RevenueHead::create([
            'agency_id' => $this->agency->id,
            'name' => 'Business Premises Permit',
            'amount' => 15000.00,
            'frequency' => 'annual',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->superAdmin)->get('/admin/revenue-heads');

        $response->assertOk();
        $response->assertSee('Revenue Heads');
        $response->assertSee('Business Premises Permit');
    }

    public function test_super_admin_can_create_revenue_head(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/revenue-heads', [
            'agency_id' => $this->agency->id,
            'name' => 'Sanitation Levy',
            'amount' => 5000.00,
            'frequency' => 'annual',
            'sql_rule' => 'size=large : 10000; default: 5000',
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin/revenue-heads');
        $this->assertDatabaseHas('revenue_heads', [
            'name' => 'Sanitation Levy',
            'amount' => 5000.00,
            'status' => 'active',
        ]);
    }

    public function test_super_admin_can_update_revenue_head(): void
    {
        $head = RevenueHead::create([
            'agency_id' => $this->agency->id,
            'name' => 'Signboard Fee',
            'amount' => 2000.00,
            'frequency' => 'annual',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->superAdmin)->put("/admin/revenue-heads/{$head->id}", [
            'agency_id' => $this->agency->id,
            'name' => 'Updated Signboard Fee',
            'amount' => 3500.00,
            'frequency' => 'annual',
            'sql_rule' => null,
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin/revenue-heads');
        $this->assertDatabaseHas('revenue_heads', [
            'id' => $head->id,
            'name' => 'Updated Signboard Fee',
            'amount' => 3500.00,
        ]);
    }

    public function test_legacy_revenue_rules_url_redirects_to_revenue_heads(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/revenue-rules');

        $response->assertRedirect('/admin/revenue-heads');
    }

    public function test_non_super_admin_cannot_access_revenue_heads(): void
    {
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)->get('/admin/revenue-heads');

        $response->assertStatus(403);
    }
}
