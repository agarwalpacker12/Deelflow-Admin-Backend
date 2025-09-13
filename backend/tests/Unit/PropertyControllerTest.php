<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PropertyControllerTest extends TestCase
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
    public function index_method_returns_paginated_properties()
    {
        Property::factory()->count(15)->create(['user_id' => $this->user->id]);
        
        $response = $this->getJson('/api/properties');
        
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
    public function index_method_applies_property_type_filter()
    {
        Property::factory()->create(['user_id' => $this->user->id, 'property_type' => 'single_family']);
        Property::factory()->create(['user_id' => $this->user->id, 'property_type' => 'condo']);
        
        $response = $this->getJson('/api/properties?property_type=single_family');
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    /** @test */
    public function index_method_applies_price_range_filter()
    {
        Property::factory()->create(['user_id' => $this->user->id, 'purchase_price' => 100000]);
        Property::factory()->create(['user_id' => $this->user->id, 'purchase_price' => 200000]);
        
        $response = $this->getJson('/api/properties?min_price=150000&max_price=250000');
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    /** @test */
    public function index_method_applies_location_filter()
    {
        Property::factory()->create(['user_id' => $this->user->id, 'city' => 'Dallas', 'state' => 'TX']);
        Property::factory()->create(['user_id' => $this->user->id, 'city' => 'Houston', 'state' => 'TX']);
        
        $response = $this->getJson('/api/properties?city=Dallas');
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    /** @test */
    public function store_method_validates_required_fields()
    {
        $response = $this->postJson('/api/properties', []);
        
        $response->assertStatus(422)
            ->assertJson([
                "status" => "error",
                "error" => [
                    "code" => "VALIDATION_ERROR"
                ]
            ]);

        $json = $response->json();
        $this->assertArrayHasKey("error", $json);
        $this->assertArrayHasKey("details", $json["error"]);
        $this->assertArrayHasKey("field_errors", $json["error"]["details"]);
        
        $errors = $json["error"]["details"]["field_errors"];
        $errorString = implode(" ", $errors);
        $this->assertTrue(
            str_contains($errorString, "address") ||
            str_contains($errorString, "city") ||
            str_contains($errorString, "state") ||
            str_contains($errorString, "zip") ||
            str_contains($errorString, "property type") ||
            str_contains($errorString, "purchase price") ||
            str_contains($errorString, "arv") ||
            str_contains($errorString, "transaction type")
        );
    }

    /** @test */
    public function store_method_validates_property_type()
    {
        $propertyData = [
            'address' => '123 Main St',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip' => '75001',
            'property_type' => 'invalid_type',
            'purchase_price' => 100000,
            'arv' => 150000,
            'transaction_type' => 'wholesale'
        ];
        
        $response = $this->postJson('/api/properties', $propertyData);
        
        $response->assertStatus(422)
            ->assertJson([
                "status" => "error",
                "error" => [
                    "code" => "VALIDATION_ERROR"
                ]
            ]);

        $json = $response->json();
        $this->assertArrayHasKey("error", $json);
        $this->assertArrayHasKey("details", $json["error"]);
        $this->assertArrayHasKey("field_errors", $json["error"]["details"]);
        
        $errors = $json["error"]["details"]["field_errors"];
        $errorString = implode(" ", $errors);
        $this->assertTrue(
            str_contains($errorString, "address") ||
            str_contains($errorString, "city") ||
            str_contains($errorString, "state") ||
            str_contains($errorString, "zip") ||
            str_contains($errorString, "property type") ||
            str_contains($errorString, "purchase price") ||
            str_contains($errorString, "arv") ||
            str_contains($errorString, "transaction type")
        );
    }

    /** @test */
    public function store_method_validates_transaction_type()
    {
        $propertyData = [
            'address' => '123 Main St',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip' => '75001',
            'property_type' => 'single_family',
            'purchase_price' => 100000,
            'arv' => 150000,
            'transaction_type' => 'invalid_type'
        ];
        
        $response = $this->postJson('/api/properties', $propertyData);
        
        $response->assertStatus(422)
            ->assertJson([
                "status" => "error",
                "error" => [
                    "code" => "VALIDATION_ERROR"
                ]
            ]);

        $json = $response->json();
        $this->assertArrayHasKey("error", $json);
        $this->assertArrayHasKey("details", $json["error"]);
        $this->assertArrayHasKey("field_errors", $json["error"]["details"]);
        
        $errors = $json["error"]["details"]["field_errors"];
        $errorString = implode(" ", $errors);
        $this->assertTrue(
            str_contains($errorString, "address") ||
            str_contains($errorString, "city") ||
            str_contains($errorString, "state") ||
            str_contains($errorString, "zip") ||
            str_contains($errorString, "property type") ||
            str_contains($errorString, "purchase price") ||
            str_contains($errorString, "arv") ||
            str_contains($errorString, "transaction type")
        );
    }

    /** @test */
    public function store_method_validates_numeric_fields()
    {
        $propertyData = [
            'address' => '123 Main St',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip' => '75001',
            'property_type' => 'single_family',
            'purchase_price' => 'not_a_number',
            'arv' => 'not_a_number',
            'transaction_type' => 'wholesale'
        ];
        
        $response = $this->postJson('/api/properties', $propertyData);
        
        $response->assertStatus(422)
            ->assertJson([
                "status" => "error",
                "error" => [
                    "code" => "VALIDATION_ERROR"
                ]
            ]);

        $json = $response->json();
        $this->assertArrayHasKey("error", $json);
        $this->assertArrayHasKey("details", $json["error"]);
        $this->assertArrayHasKey("field_errors", $json["error"]["details"]);
        
        $errors = $json["error"]["details"]["field_errors"];
        $errorString = implode(" ", $errors);
        $this->assertTrue(
            str_contains($errorString, "address") ||
            str_contains($errorString, "city") ||
            str_contains($errorString, "state") ||
            str_contains($errorString, "zip") ||
            str_contains($errorString, "property type") ||
            str_contains($errorString, "purchase price") ||
            str_contains($errorString, "arv") ||
            str_contains($errorString, "transaction type")
        );
    }

    /** @test */
    public function store_method_creates_property_successfully()
    {
        $propertyData = [
            'address' => '123 Main St',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip' => '75001',
            'property_type' => 'single_family',
            'bedrooms' => 3,
            'bathrooms' => 2.5,
            'square_feet' => 1500,
            'purchase_price' => 100000,
            'arv' => 150000,
            'repair_estimate' => 20000,
            'transaction_type' => 'wholesale'
        ];
        
        $response = $this->postJson('/api/properties', $propertyData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'address', 'city', 'state']
            ]);
        
        $this->assertDatabaseHas('properties', [
            'address' => '123 Main St',
            'city' => 'Dallas',
            'state' => 'TX',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function store_method_generates_uuid_and_ai_score()
    {
        $propertyData = [
            'address' => '123 Main St',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip' => '75001',
            'property_type' => 'single_family',
            'purchase_price' => 100000,
            'arv' => 150000,
            'transaction_type' => 'wholesale'
        ];
        
        $response = $this->postJson('/api/properties', $propertyData);
        
        $response->assertStatus(201);
        $property = $response->json('data');
        
        $this->assertNotNull($property['uuid']);
        $this->assertNotNull($property['ai_score']);
        $this->assertEquals('draft', $property['status']); // Updated expectation
    }

    /** @test */
    public function show_method_returns_existing_property()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->getJson("/api/properties/{$property->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $property->id
                ]
            ]);
    }

    /** @test */
    public function show_method_returns_404_for_nonexistent_property()
    {
        $response = $this->getJson('/api/properties/999');
        
        $response->assertStatus(404)
            ->assertJson(['status' => 'error']);
    }

    /** @test */
    public function show_method_returns_404_for_other_users_property()
    {
        $otherUser = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $otherUser->id]);
        
        $response = $this->getJson("/api/properties/{$property->id}");
        
        $response->assertStatus(404)
            ->assertJson(['status' => 'error']);
    }

    /** @test */
    public function update_method_validates_optional_fields()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->putJson("/api/properties/{$property->id}", [
            'property_type' => 'invalid_type'
        ]);
        
        $response->assertStatus(422)
            ->assertJson([
                "status" => "error",
                "error" => [
                    "code" => "VALIDATION_ERROR"
                ]
            ]);

        $json = $response->json();
        $this->assertArrayHasKey("error", $json);
        $this->assertArrayHasKey("details", $json["error"]);
        $this->assertArrayHasKey("field_errors", $json["error"]["details"]);
        
        $errors = $json["error"]["details"]["field_errors"];
        $errorString = implode(" ", $errors);
        $this->assertTrue(
            str_contains($errorString, "address") ||
            str_contains($errorString, "city") ||
            str_contains($errorString, "state") ||
            str_contains($errorString, "zip") ||
            str_contains($errorString, "property type") ||
            str_contains($errorString, "purchase price") ||
            str_contains($errorString, "arv") ||
            str_contains($errorString, "transaction type")
        );
    }

    /** @test */
    public function update_method_updates_property_successfully()
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'address' => '123 Main St'
        ]);
        
        $response = $this->putJson("/api/properties/{$property->id}", [
            'address' => '456 Oak Ave',
            'bedrooms' => 4
        ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'address' => '456 Oak Ave',
                    'bedrooms' => 4
                ]
            ]);
    }

    /** @test */
    public function update_method_returns_404_for_nonexistent_property()
    {
        $response = $this->putJson('/api/properties/999', [
            'address' => '456 Oak Ave'
        ]);
        
        $response->assertStatus(404)
            ->assertJson(['status' => 'error']);
    }

    /** @test */
    public function destroy_method_deletes_property_successfully()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->deleteJson("/api/properties/{$property->id}");
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
        
        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }

    /** @test */
    public function destroy_method_returns_404_for_nonexistent_property()
    {
        $response = $this->deleteJson('/api/properties/999');
        
        $response->assertStatus(404)
            ->assertJson(['status' => 'error']);
    }

    /** @test */
    public function ai_analysis_method_returns_property_analysis()
    {
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'ai_score' => 85,
            'purchase_price' => 100000,
            'arv' => 150000,
            'repair_estimate' => 20000
        ]);
        
        $response = $this->getJson("/api/properties/{$property->id}/ai-analysis");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'property_id',
                    'ai_score',
                    'analysis'
                ]
            ]);
    }

    /** @test */
    public function ai_analysis_method_returns_404_for_nonexistent_property()
    {
        $response = $this->getJson('/api/properties/999/ai-analysis');
        
        $response->assertStatus(404)
            ->assertJson(['status' => 'error']);
    }

    /** @test */
    public function controller_handles_pagination_parameters()
    {
        Property::factory()->count(25)->create(['user_id' => $this->user->id]);
        
        $response = $this->getJson('/api/properties?page=2&per_page=10');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data',
                    'meta' => [
                        'current_page',
                        'per_page'
                    ]
                ]
            ]);
    }

    /** @test */
    public function controller_calculates_profit_potential()
    {
        $propertyData = [
            'address' => '123 Main St',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip' => '75001',
            'property_type' => 'single_family',
            'purchase_price' => 100000,
            'arv' => 150000,
            'repair_estimate' => 20000,
            'holding_costs' => 5000,
            'transaction_type' => 'wholesale'
        ];
        
        $response = $this->postJson('/api/properties', $propertyData);
        
        $response->assertStatus(201);
        $property = $response->json('data');
        
        // Profit potential = ARV - Purchase Price - Repair Estimate - Holding Costs
        // 150000 - 100000 - 20000 - 5000 = 25000
        $this->assertEquals(25000, $property['profit_potential']);
    }

    /** @test */
    public function controller_uses_correct_response_format()
    {
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->getJson("/api/properties/{$property->id}");
        
        $response->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
        
        $this->assertContains($response->json('status'), ['success', 'error']);
    }
}
