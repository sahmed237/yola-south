<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Establishment;
use App\Models\EstablishmentType;
use App\Models\EstablishmentSize;

class PublicMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_map_page_renders_successfully(): void
    {
        // 1. Create type and size dependencies
        $type = EstablishmentType::create([
            'key' => 'hotel',
            'value' => 'Hotel',
            'status' => true
        ]);

        $size = EstablishmentSize::create([
            'key' => 'medium',
            'value' => 'Medium',
            'status' => true
        ]);

        // 2. Create an approved establishment with coordinates
        $est = Establishment::create([
            'unique_id' => 'EST-123456',
            'name' => 'Grand Palace Hotel',
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

        // 3. Create an approved establishment without coordinates
        $estNoCoords = Establishment::create([
            'unique_id' => 'EST-999999',
            'name' => 'Invisible Shop',
            'establishment_type_id' => $type->id,
            'establishment_size_id' => $size->id,
            'lga' => 'Jimeta',
            'ward' => 'Karewa',
            'lat' => null,
            'lng' => null,
            'street_address' => '12 Galadima Road',
            'house_number' => '12',
            'status' => 'approved',
            'base_year' => 2026,
        ]);

        // 4. Request the public map page
        $response = $this->get('/map');

        // 5. Assertions
        $response->assertStatus(200);
        $response->assertViewHas('establishments');
        $response->assertSee('Grand Palace Hotel');
        $response->assertSee('EST-123456');
        $response->assertDontSee('Invisible Shop');
        $response->assertDontSee('EST-999999');
    }
}
