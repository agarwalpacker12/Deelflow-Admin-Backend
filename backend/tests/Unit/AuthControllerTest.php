<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function register_method_validates_required_fields()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ])
            ->assertJsonPath('error.details.field_errors', function ($errors) {
                return is_array($errors) && count($errors) > 0 && 
                       (str_contains(implode(' ', $errors), 'first_name') ||
                        str_contains(implode(' ', $errors), 'last_name') ||
                        str_contains(implode(' ', $errors), 'email') ||
                        str_contains(implode(' ', $errors), 'password') ||
                        str_contains(implode(' ', $errors), 'role'));
            });
    }

    /** @test */
    public function register_method_validates_email_format()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ])
            ->assertJsonPath('error.details.field_errors', function ($errors) {
                return is_array($errors) && count($errors) > 0 && 
                       str_contains(implode(' ', $errors), 'email');
            });
    }

    /** @test */
    public function register_method_validates_password_confirmation()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
            'role' => 'staff'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ])
            ->assertJsonPath('error.details.field_errors', function ($errors) {
                return is_array($errors) && count($errors) > 0 && 
                       str_contains(implode(' ', $errors), 'password');
            });
    }

    /** @test */
    public function register_method_creates_user_successfully()
    {
        $userData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
            'organization_name' => 'Test Organization'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'uuid',
                    'email',
                    'first_name',
                    'last_name',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'first_name' => 'Test'
        ]);
    }

    /** @test */
    public function register_method_prevents_duplicate_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $userData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
            'organization_name' => 'Test Organization'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ])
            ->assertJsonPath('error.details.field_errors', function ($errors) {
                return is_array($errors) && count($errors) > 0 && 
                       str_contains(implode(' ', $errors), 'email');
            });
    }

    /** @test */
    public function login_method_validates_required_fields()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ])
            ->assertJsonPath('error.details.field_errors', function ($errors) {
                return is_array($errors) && count($errors) > 0 && 
                       (str_contains(implode(' ', $errors), 'email') ||
                        str_contains(implode(' ', $errors), 'password'));
            });
    }

    /** @test */
    public function login_method_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Login failed. The email address or password you entered is incorrect.'
            ]);
    }

    /** @test */
    public function login_method_succeeds_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'uuid', 'email']
                ]
            ]);
    }

    /** @test */
    public function login_method_fails_for_inactive_user()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'You don\'t have permission to access your account because it has been deactivated.'
            ]);
    }

    /** @test */
    public function me_method_returns_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email
                ]
            ]);
    }

    /** @test */
    public function logout_method_revokes_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User logged out successfully'
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }
}
