<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions with groups
        $permissionGroups = [
            'Role Management' => [
                'manage roles',
            ],
            'Establishment Management' => [
                'create establishment',
                'view establishment',
                'edit establishment',
                'delete establishment',
                'view all establishment',
                'establishment approval',
                'request establishment update',
                'approve establishment update',
                'execute establishment update',
                'view all invalid establishment',
            ],
            'Revenue & Payments' => [
                'view payments',
                'view payment',
                'view invoice',
                'verify invoice',
                'view report',
                'manage revenue heads',
                'view unpaid taxes',
            ],
            'Agency Management' => [
                'manage agencies',
            ],
            'User Management' => [
                'users view',
                'users create',
                'users edit',
                'users delete',
                'users status',
                'users logs',
                'users security',
                'users reset password',
                'users reset 2fa',
                'users verify',
                'users email',
                'users unlock',
            ],
            'System Configuration' => [
                'manage faq',
            ],
        ];

        foreach ($permissionGroups as $group => $perms) {
            foreach ($perms as $permission) {
                Permission::findOrCreate($permission, 'web')->update(['group' => $group]);
            }
        }

        // Create roles and assign permissions
        $superAdmin = Role::findOrCreate('super-admin');
        $superAdmin->givePermissionTo(Permission::all());

        $revenueManager = Role::findOrCreate('revenue-manager');
        $revenueManager->syncPermissions([
            'view establishment',
            'view all establishment',
            'view payments',
            'view payment',
            'view invoice',
            'verify invoice',
            'view report',
            'manage revenue heads',
            'view all invalid establishment',
            'view unpaid taxes',
        ]);

        $lgaCoordinator = Role::findOrCreate('lga-coordinator');
        $lgaCoordinator->syncPermissions([
            'view establishment',
            'view all establishment',
            'view payments',
            'view payment',
            'view invoice',
            'view report',
            'view all invalid establishment',
            'request establishment update',
            'view unpaid taxes',
        ]);

        $verificationOfficer = Role::findOrCreate('verification-officer');
        $verificationOfficer->syncPermissions([
            'view establishment',
            'view all establishment',
            'establishment approval',
            'edit establishment',
            'view all invalid establishment',
        ]);

        $fieldOfficer = Role::findOrCreate('field-officer');
        $fieldOfficer->syncPermissions([
            'create establishment',
            'view establishment',
            'edit establishment',
            'request establishment update',
            'view payments',
            'view payment',
            'view invoice',
            'view unpaid taxes',
        ]);

        $agencyRep = Role::findOrCreate('agency-representative');
        $agencyRep->syncPermissions([
            'view payments',
            'view payment',
            'view invoice',
            'view report',
        ]);

        // User creation is now handled in DatabaseSeeder.php
    }
}
