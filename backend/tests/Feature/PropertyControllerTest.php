<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PropertyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->user->organization_id = \App\Models\Organization::factory()->create()->id;
        $this->user->save();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function authenticated_user_can_get_properties_list()
    {
        Property::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/properties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'uuid',
                        'address',
                        'city',
                        'state',
                        'zip',
                        'property_type',
                        'purchase_price',
                        'arv',
                        'ai_score'
                    ]
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_create_property()
    {
        $propertyData = [
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => 'TX',
            'zip' => $this->faker->postcode,
            'property_type' => 'single_family',
            'purchase_price' => 150000,
            'arv' => 200000,
            'repair_estimate' => 25000,
            'transaction_type' => 'wholesale'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/properties', $propertyData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'uuid',
                    'address',
                    'city',
                    'state',
                    'property_type',
                    'purchase_price',
                    'arv'
                ]
            ]);

        $this->assertDatabaseHas('properties', [
            'address' => $propertyData['address'],
            'city' => $propertyData['city'],
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function authenticated_user_can_view_specific_property()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/properties/{$property->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'uuid',
                    'address',
                    'city',
                    'state'
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_property()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'address' => 'Updated Address',
            'purchase_price' => 175000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/properties/{$property->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'address' => 'Updated Address',
            'purchase_price' => 175000
        ]);
    }

    /** @test */
    public function authenticated_user_can_delete_property()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Property deleted successfully'
            ]);

        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }

    /** @test */
    public function authenticated_user_can_get_property_ai_analysis()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/properties/{$property->id}/ai-analysis");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'property_id',
                    'ai_score',
                    'market_analysis',
                    'repair_analysis',
                    'investment_metrics'
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_properties()
    {
        $response = $this->getJson('/api/properties');
        $response->assertStatus(401);
    }

    /** @test */
    public function property_validation_works_correctly()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/properties', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['address', 'city', 'state', 'zip', 'property_type', 'purchase_price', 'arv']);
    }

    /** @test */
    public function property_model_relationships_work()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        
        $this->assertInstanceOf(User::class, $property->user);
        $this->assertEquals($this->user->id, $property->user->id);
    }
}
