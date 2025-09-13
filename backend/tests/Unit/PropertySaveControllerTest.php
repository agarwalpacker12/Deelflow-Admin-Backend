<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PropertySave;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PropertySaveControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function index_method_returns_paginated_saves()
    {
        // Create multiple properties to avoid unique constraint violation
        $properties = Property::factory()->count(15)->create(['user_id' => $this->user->id]);
        
        foreach ($properties as $property) {
            PropertySave::factory()->create([
                'user_id' => $this->user->id,
                'property_id' => $property->id
            ]);
        }
        
        $response = $this->getJson('/api/property-saves');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data',
                    'meta',
                ]
            ]);
    }

    /** @test */
    public function store_method_validates_required_fields()
    {
        $response = $this->postJson('/api/property-saves', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['property_id']);
    }

    /** @test */
    public function store_method_creates_save_successfully()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        
        $saveData = [
            'property_id' => $property->id
        ];
        
        $response = $this->postJson('/api/property-saves', $saveData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'user_id', 'property_id']
            ]);
        
        $this->assertDatabaseHas('property_saves', [
            'user_id' => $this->user->id,
            'property_id' => $property->id
        ]);
    }

    /** @test */
    public function destroy_method_deletes_save_successfully()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        $save = PropertySave::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $property->id
        ]);
        
        $response = $this->deleteJson("/api/property-saves/{$save->id}");
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
        
        $this->assertDatabaseMissing('property_saves', ['id' => $save->id]);
    }

    /** @test */
    public function show_method_returns_existing_save()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        $save = PropertySave::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $property->id
        ]);
        
        $response = $this->getJson("/api/property-saves/{$save->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $save->id
                ]
            ]);
    }
}
