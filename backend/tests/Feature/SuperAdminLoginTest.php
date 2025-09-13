<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;

class SuperAdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_login_in_development_environment()
    {
        // Set the environment to 'development'
        Config::set('app.env', 'development');

        // Set super admin credentials in config
        $superAdminEmail = 'superadmin@test.com';
        $superAdminPassword = 'supersecretpassword';
        Config::set('auth.super_admin.email', $superAdminEmail);
        Config::set('auth.super_admin.password', $superAdminPassword);

        // Attempt to log in as super admin
        $response = $this->postJson('/api/login', [
            'email' => $superAdminEmail,
            'password' => $superAdminPassword,
        ]);

        // Assert the response is successful and contains a token
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'uuid',
                        'email',
                        'first_name',
                        'last_name',
                        'role',
                    ],
                ],
            ])
            ->assertJson([
                'status' => 'success',
            ]);

        // Assert the super admin user was created in the database
        $this->assertDatabaseHas('users', [
            'email' => $superAdminEmail,
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'role' => 'admin',
        ]);

        // Assert the organization was created
        $this->assertDatabaseHas('organizations', [
            'name' => 'Super Admin Organization',
        ]);
    }
}
