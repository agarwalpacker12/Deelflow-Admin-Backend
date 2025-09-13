<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Role;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::factory()->create(['name' => 'super_admin', 'label' => 'Super Admin']);
        Role::factory()->create(['name' => 'admin', 'label' => 'Admin']);
    }

    public function test_super_admin_can_update_user_status()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->roles()->attach(Role::where('name', 'super_admin')->first());

        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);

        $response = $this->actingAs($superAdmin)->patchJson("/api/users/{$user->id}/status", [
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'inactive',
            'is_active' => false,
        ]);
    }

    public function test_org_admin_can_update_user_status_within_own_org()
    {
        $organization = Organization::factory()->create();
        $orgAdmin = User::factory()->create(['organization_id' => $organization->id]);
        $orgAdmin->roles()->attach(Role::where('name', 'admin')->first());

        $user = User::factory()->create(['organization_id' => $organization->id]);

        $response = $this->actingAs($orgAdmin)->patchJson("/api/users/{$user->id}/status", [
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'inactive',
            'is_active' => false,
        ]);
    }

    public function test_org_admin_cannot_update_user_status_in_another_org()
    {
        $organization1 = Organization::factory()->create();
        $orgAdmin = User::factory()->create(['organization_id' => $organization1->id]);
        $orgAdmin->roles()->attach(Role::where('name', 'admin')->first());

        $organization2 = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization2->id]);

        $response = $this->actingAs($orgAdmin)->patchJson("/api/users/{$user->id}/status", [
            'status' => 'inactive',
        ]);

        $response->assertStatus(403);
    }
}
