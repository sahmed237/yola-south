<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Establishment;
use App\Models\EstablishmentType;
use App\Models\EstablishmentSize;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1EstablishmentTest extends TestCase
{
    use RefreshDatabase;

    private $superAdmin;
    private $type;
    private $size;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic types and sizes
        $this->type = EstablishmentType::create([
            'key' => 'retail-store',
            'value' => 'Retail Store',
            'status' => true
        ]);

        $this->size = EstablishmentSize::create([
            'key' => 'small-kiosk-single-unit',
            'value' => 'Small (Kiosk / Single Unit)',
            'status' => true
        ]);

        $superAdminRole = Role::create(['name' => 'super-admin']);
        $this->superAdmin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole($superAdminRole);
    }

    public function test_api_store_establishment_successful(): void
    {
        $payload = [
            'name' => 'Test Retail Shop',
            'type' => 'Retail Store',
            'size' => 'Small (Kiosk / Single Unit)',
            'lga' => 'Jimeta',
            'ward' => 'Karewa',
            'lat' => 9.2035,
            'lng' => 12.4954,
            'inside_metropolis' => true,
            'street_address' => '10 Galadima Road',
            'house_number' => '10',
            'city' => 'Jimeta',
            'postal_code' => '640211',
            'owner_name' => 'John Doe',
            'owner_gender' => 'Male',
            'owner_phone' => '08012345678',
            'owner_email' => 'john@example.com',
            'owner_nin' => '12345678901',
            'occupant_name' => 'Jane Smith',
            'occupant_phone' => '08087654321',
            'base_year' => 2026,
        ];

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/establishments', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('establishment.name', 'Test Retail Shop');
        $response->assertJsonPath('establishment.inside_metropolis', true);
        $response->assertJsonPath('establishment.street_address', '10 Galadima Road');

        $this->assertDatabaseHas('establishments', [
            'name' => 'Test Retail Shop',
            'establishment_type_id' => $this->type->id,
            'establishment_size_id' => $this->size->id,
            'inside_metropolis' => 1,
            'street_address' => '10 Galadima Road',
            'house_number' => '10',
            'city' => 'Jimeta',
            'postal_code' => '640211',
            'base_year' => 2026,
        ]);

        $this->assertDatabaseHas('establishment_owners', [
            'name' => 'John Doe',
            'gender' => 'male',
            'phone' => '08012345678',
            'email' => 'john@example.com',
            'nin' => '12345678901',
        ]);

        $this->assertDatabaseHas('occupants', [
            'name' => 'Jane Smith',
            'phone' => '08087654321',
        ]);
    }

    public function test_api_sync_establishments_successful(): void
    {
        $payload = [
            'shops' => [
                [
                    'local_id' => 42,
                    'name' => 'Sync Shop 1',
                    'type' => 'Retail Store',
                    'size' => 'Small (Kiosk / Single Unit)',
                    'lga' => 'Jimeta',
                    'ward' => 'Karewa',
                    'lat' => 9.2035,
                    'lng' => 12.4954,
                    'inside_metropolis' => false,
                    'street_address' => '20 Galadima Road',
                    'house_number' => '20',
                    'city' => 'Jimeta',
                    'postal_code' => '640211',
                    'owner_name' => 'Alice Allison',
                    'owner_gender' => 'Female',
                    'owner_phone' => '08022222222',
                    'owner_email' => 'alice@example.com',
                    'owner_nin' => '22222222222',
                    'occupant_name' => 'Bob Builder',
                    'occupant_phone' => '08033333333',
                    'base_year' => 2026,
                ]
            ]
        ];

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/sync', $payload);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'local_id' => 42,
            'status' => 'synced',
        ]);

        $this->assertDatabaseHas('establishments', [
            'name' => 'Sync Shop 1',
            'establishment_type_id' => $this->type->id,
            'establishment_size_id' => $this->size->id,
            'inside_metropolis' => 0,
            'street_address' => '20 Galadima Road',
            'house_number' => '20',
            'city' => 'Jimeta',
            'postal_code' => '640211',
            'base_year' => 2026,
        ]);

        $this->assertDatabaseHas('establishment_owners', [
            'name' => 'Alice Allison',
            'gender' => 'female',
            'phone' => '08022222222',
            'email' => 'alice@example.com',
            'nin' => '22222222222',
        ]);

        $this->assertDatabaseHas('occupants', [
            'name' => 'Bob Builder',
            'phone' => '08033333333',
        ]);
    }

    public function test_api_sync_establishments_with_images_successful(): void
    {
        // A dummy 1x1 pixel base64 PNG image
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $payload = [
            'shops' => [
                [
                    'local_id' => 99,
                    'name' => 'Sync Shop With Images',
                    'type' => 'Retail Store',
                    'size' => 'Small (Kiosk / Single Unit)',
                    'lga' => 'Jimeta',
                    'ward' => 'Karewa',
                    'lat' => 9.2035,
                    'lng' => 12.4954,
                    'inside_metropolis' => false,
                    'street_address' => '20 Galadima Road',
                    'house_number' => '20',
                    'city' => 'Jimeta',
                    'postal_code' => '640211',
                    'owner_name' => 'Alice Allison',
                    'owner_gender' => 'Female',
                    'owner_phone' => '08022222222',
                    'owner_email' => 'alice@example.com',
                    'owner_nin' => '22222222222',
                    'occupant_name' => 'Bob Builder',
                    'occupant_phone' => '08033333333',
                    'base_year' => 2026,
                    'images' => [
                        $base64Image
                    ]
                ]
            ]
        ];

        \Illuminate\Support\Facades\Storage::fake('public');

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/sync', $payload);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'local_id' => 99,
            'status' => 'synced',
        ]);

        $establishment = Establishment::where('name', 'Sync Shop With Images')->first();
        $this->assertNotNull($establishment);

        $this->assertDatabaseHas('establishment_images', [
            'establishment_id' => $establishment->id,
            'is_primary' => 1,
            'lat' => 9.2035,
            'lng' => 12.4954,
        ]);

        $image = $establishment->images()->first();
        $this->assertNotNull($image);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($image->image_path);
    }
}
