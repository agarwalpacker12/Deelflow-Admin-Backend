<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding test organization with admin and staff users...');

        // 1. Create the organization
        $organization = Organization::updateOrCreate(
            ['name' => 'Test Organization'],
            [
                'uuid' => Str::uuid(),
                'slug' => Str::slug('Test Organization'),
                'subscription_status' => 'active',
            ]
        );

        // 2. Create the admin user
        $adminUser = User::updateOrCreate(
            ['email' => 'test-admin@example.com'],
            [
                'uuid' => Str::uuid(),
                'first_name' => 'Test',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'organization_id' => $organization->id,
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        $adminUser->assignRole($adminRole);

        // 3. Create the staff user
        $staffUser = User::updateOrCreate(
            ['email' => 'test-staff@example.com'],
            [
                'uuid' => Str::uuid(),
                'first_name' => 'Test',
                'last_name' => 'Staff',
                'password' => Hash::make('password'),
                'organization_id' => $organization->id,
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        $staffRole = Role::where('name', 'staff')->first();
        $staffUser->assignRole($staffRole);

        $this->command->info('Test organization seeded successfully.');
    }
}
