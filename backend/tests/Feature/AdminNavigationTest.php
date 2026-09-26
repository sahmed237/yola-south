<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Establishment;
use App\Models\EstablishmentType;
use App\Models\EstablishmentSize;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    private $manageUsersPermission;
    private $manageRolesPermission;
    private $establishmentApprovalPermission;
    private $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manageUsersPermission = Permission::create(['name' => 'manage users']);
        $this->manageRolesPermission = Permission::create(['name' => 'manage roles']);
        $this->establishmentApprovalPermission = Permission::create(['name' => 'establishment approval']);
        $this->adminRole = Role::create(['name' => 'admin-test']);
    }

    public function test_navigation_links_hidden_for_unauthorized_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        
        $response->assertOk();
        $response->assertDontSee('Administration');
        $response->assertDontSee('User Management');
        $response->assertDontSee('Roles & Permissions');
        $response->assertDontSee('System Setup');
        $response->assertDontSee('Establishment Types');
        $response->assertDontSee('Establishment Sizes');
        $response->assertDontSee('Establishment Approvals');

        // Verify route access is denied
        $this->actingAs($user)->get('/admin/setup/establishment-types')->assertStatus(403);
        $this->actingAs($user)->get('/admin/setup/establishment-sizes')->assertStatus(403);
        $this->actingAs($user)->get('/admin/approvals')->assertStatus(403);
    }

    public function test_navigation_links_partially_visible_with_manage_users_permission(): void
    {
        $this->adminRole->givePermissionTo($this->manageUsersPermission);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        
        $response->assertOk();
        $response->assertSee('Administration');
        $response->assertSee('User Management');
        $response->assertDontSee('Roles & Permissions');
    }

    public function test_navigation_links_fully_visible_with_all_permissions(): void
    {
        $this->adminRole->givePermissionTo($this->manageUsersPermission);
        $this->adminRole->givePermissionTo($this->manageRolesPermission);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        
        $response->assertOk();
        $response->assertSee('Administration');
        $response->assertSee('User Management');
        $response->assertSee('Roles & Permissions', false);
    }

    public function test_super_admin_has_full_setup_navigation_and_access(): void
    {
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $response = $this->actingAs($superAdmin)->get('/admin/dashboard');
        
        $response->assertOk();
        $response->assertSee('System Setup');
        $response->assertSee('Establishment Types');
        $response->assertSee('Establishment Sizes');

        // Verify route access is allowed
        $this->actingAs($superAdmin)->get('/admin/setup/establishment-types')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/setup/establishment-sizes')->assertOk();
    }

    public function test_navigation_links_partially_visible_with_establishment_approval_permission(): void
    {
        $this->adminRole->givePermissionTo($this->establishmentApprovalPermission);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        
        $response->assertOk();
        $response->assertSee('Establishment Approvals');
        
        $this->actingAs($user)->get('/admin/approvals')->assertOk();
    }

    public function test_invalid_establishment_badge_count(): void
    {
        $viewAllInvalidPermission = Permission::findOrCreate('view all invalid establishment');

        $type = EstablishmentType::create([
            'key' => 'hotel-test',
            'value' => 'Hotel Test',
            'status' => true
        ]);
        $size = EstablishmentSize::create([
            'key' => 'medium-test',
            'value' => 'Medium Test',
            'status' => true
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Establishment::create([
            'name' => 'Rejected 1',
            'establishment_type_id' => $type->id,
            'establishment_size_id' => $size->id,
            'lga' => 'Jimeta',
            'ward' => 'Karewa',
            'lat' => 9.2035,
            'lng' => 12.4954,
            'street_address' => '10 Galadima Road',
            'house_number' => '10',
            'status' => 'rejected',
            'created_by' => $user1->id,
            'base_year' => 2026,
        ]);

        Establishment::create([
            'name' => 'Rejected 2',
            'establishment_type_id' => $type->id,
            'establishment_size_id' => $size->id,
            'lga' => 'Jimeta',
            'ward' => 'Karewa',
            'lat' => 9.2035,
            'lng' => 12.4954,
            'street_address' => '11 Galadima Road',
            'house_number' => '11',
            'status' => 'rejected',
            'created_by' => $user2->id,
            'base_year' => 2026,
        ]);

        // 1. Logged in as user1 without view all invalid establishment permission
        $response = $this->actingAs($user1)->get('/admin/dashboard');
        $response->assertOk();
        // Should only see count of 1 (their own)
        $response->assertSee('1');
        $response->assertDontSee('2');

        // 2. Grant permission and check again
        $role = Role::create(['name' => 'admin-invalid-test']);
        $role->givePermissionTo($viewAllInvalidPermission);
        $user1->assignRole($role);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user1)->get('/admin/dashboard');
        $response->assertOk();
        // Should see count of 2 (all rejected)
        $response->assertSee('2');
    }

    public function test_pending_approvals_badge_count(): void
    {
        $type = EstablishmentType::create([
            'key' => 'hotel-approval-test',
            'value' => 'Hotel Approval Test',
            'status' => true
        ]);
        $size = EstablishmentSize::create([
            'key' => 'medium-approval-test',
            'value' => 'Medium Approval Test',
            'status' => true
        ]);

        // Create a pending establishment
        Establishment::create([
            'name' => 'Pending 1',
            'establishment_type_id' => $type->id,
            'establishment_size_id' => $size->id,
            'lga' => 'Jimeta',
            'ward' => 'Karewa',
            'lat' => 9.2035,
            'lng' => 12.4954,
            'street_address' => '10 Galadima Road',
            'house_number' => '10',
            'status' => 'pending',
            'base_year' => 2026,
        ]);

        // Create another pending establishment
        Establishment::create([
            'name' => 'Pending 2',
            'establishment_type_id' => $type->id,
            'establishment_size_id' => $size->id,
            'lga' => 'Jimeta',
            'ward' => 'Karewa',
            'lat' => 9.2035,
            'lng' => 12.4954,
            'street_address' => '11 Galadima Road',
            'house_number' => '11',
            'status' => 'pending',
            'base_year' => 2026,
        ]);

        // Log in as user with establishment approval permission
        $this->adminRole->givePermissionTo($this->establishmentApprovalPermission);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->assignRole($this->adminRole);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertOk();
        // Should see count of 2 (all pending)
        $response->assertSee('2');
    }
}
