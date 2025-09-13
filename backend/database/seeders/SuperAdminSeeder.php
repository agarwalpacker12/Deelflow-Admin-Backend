<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding super admin...');

        $superAdminEmail = config('auth.super_admin.email');

        $superAdmin = User::updateOrCreate(
            ['email' => $superAdminEmail],
            [
                'uuid' => Str::uuid(),
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make(config('auth.super_admin.password')),
                'organization_id' => null,
                'is_active' => true,
                'is_verified' => true,
                'level' => 99,
                'points' => 9999,
            ]
        );

        // Ensure the 'super_admin' role exists and assign it
        $superAdminRole = Role::updateOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'api']
        );

        $superAdmin->assignRole($superAdminRole);

        $this->command->info('Super admin created successfully.');
    }
}
