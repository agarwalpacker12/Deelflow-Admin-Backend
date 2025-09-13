<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions with more descriptive groups
        $permissions = [
            'User Management' => ['view users', 'manage users', 'impersonate users'],
            'Role Management' => ['view roles', 'manage roles'],
            'Permission Management' => ['view permissions', 'manage permissions'],
            'Application Settings' => ['view settings', 'manage settings'],
            'Deal Management' => ['view deals', 'manage deals', 'delete deals'],
            'Lead Management' => ['view leads', 'manage leads', 'delete leads'],
            'Property Management' => ['view properties', 'manage properties', 'delete properties'],
            'Campaign Management' => ['view campaigns', 'manage campaigns', 'delete campaigns'],
            'Organization Management' => ['view organizations', 'manage organizations', 'delete organizations'],
        ];

        // Create or update permissions
        foreach ($permissions as $group => $permissionNames) {
            foreach ($permissionNames as $name) {
                Permission::updateOrCreate(
                    ['name' => $name, 'guard_name' => 'api'],
                    ['group' => $group]
                );
            }
        }

        // Define roles and assign permissions
        $superAdminRole = Role::updateOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);
        $superAdminRole->syncPermissions(Permission::all());

        $adminRole = Role::updateOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo([
            'view users', 'manage users',
            'view roles', 'manage roles',
            'view permissions', 'manage permissions',
            'view settings', 'manage settings',
            'view deals', 'manage deals',
            'view leads', 'manage leads',
            'view properties', 'manage properties',
            'view campaigns', 'manage campaigns',
            'view organizations', 'manage organizations',
        ]);

        $staffRole = Role::updateOrCreate(['name' => 'staff', 'guard_name' => 'api']);
        $staffRole->givePermissionTo([
            'view deals', 'manage deals',
            'view leads', 'manage leads',
            'view properties', 'manage properties',
            'view campaigns', 'manage campaigns',
        ]);
    }
}
