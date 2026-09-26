<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            //AdamawaLgaWardSeeder::class,
            TarabaLgaWardSeeder::class,
            SettingSeeder::class,
            EstablishmentSetupSeeder::class,
            FaqSeeder::class,
        ]);

        $user = User::updateOrCreate(
            ['email' => 'superadmin@urcs.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('superadmin'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('super-admin');
    }
}
