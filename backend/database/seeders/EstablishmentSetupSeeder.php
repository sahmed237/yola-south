<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstablishmentType;
use App\Models\EstablishmentSize;
use App\Models\User;
use Illuminate\Support\Str;

class EstablishmentSetupSeeder extends Seeder
{
    public function run()
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : null;

        $types = [
            'Retail Store',
            'Wholesale Outlet',
            'Manufacturing Plant',
            'Hotel / Guest House',
            'Beauty Salon / Spa',
            'Bar / Nightclub',
            'Restaurant / Eatery',
            'General Service Provider',
            'Hospitality Venue'
        ];

        foreach ($types as $type) {
            EstablishmentType::firstOrCreate(
                ['key' => Str::slug($type)],
                [
                    'value' => $type,
                    'status' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );
        }

        $sizes = [
            'Small (Kiosk / Single Unit)',
            'Medium (Standard Store / Salon)',
            'Large (Supermarket / Plaza)',
            'Mega (Industrial / Large Hotel / Mall)'
        ];

        foreach ($sizes as $size) {
            EstablishmentSize::firstOrCreate(
                ['key' => Str::slug($size)],
                [
                    'value' => $size,
                    'status' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );
        }
    }
}
