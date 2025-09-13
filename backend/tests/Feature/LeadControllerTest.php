<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LeadControllerTest extends TestCase
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
    public function authenticated_user_can_get_leads_list()
    {
        Lead::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/leads');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'uuid',
                            'first_name',
                            'last_name',
                            'email',
                            'phone',
                            'property_address',
                            'ai_score',
                            'status'
                        ]
                    ],
                    'meta'
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_create_lead()
    {
        $leadData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'phone' => $this->faker->phoneNumber,
            'property_address' => $this->faker->address,
            'property_city' => $this->faker->city,
            'property_state' => 'TX',
            'property_zip' => $this->faker->postcode,
            'property_type' => 'single_family',
            'estimated_value' => 250000,
            'asking_price' => 200000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/leads', $leadData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'uuid',
                    'first_name',
                    'last_name',
                    'email',
                    'ai_score',
                    'status'
                ]
            ]);

        $this->assertDatabaseHas('leads', [
            'first_name' => $leadData['first_name'],
            'last_name' => $leadData['last_name'],
            'email' => $leadData['email'],
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function authenticated_user_can_view_specific_lead()
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/leads/{$lead->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'uuid',
                    'first_name',
                    'last_name',
                    'email',
                    'ai_score'
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_lead()
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'first_name' => 'Updated Name',
            'status' => 'contacted'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/leads/{$lead->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'first_name' => 'Updated Name',
            'status' => 'contacted'
        ]);
    }

    /** @test */
    public function authenticated_user_can_delete_lead()
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/leads/{$lead->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Lead deleted successfully'
            ]);

        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    /** @test */
    public function authenticated_user_can_get_lead_ai_score()
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/leads/{$lead->id}/ai-score");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'lead_id',
                    'ai_score',
                    'motivation_score',
                    'urgency_score',
                    'financial_score',
                    'analysis'
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_leads()
    {
        $response = $this->getJson('/api/leads');
        $response->assertStatus(401);
    }

    /** @test */
    public function lead_validation_works_correctly()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/leads', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name']);
    }

    /** @test */
    public function lead_model_relationships_work()
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);
        
        $this->assertInstanceOf(User::class, $lead->user);
        $this->assertEquals($this->user->id, $lead->user->id);
    }
}
