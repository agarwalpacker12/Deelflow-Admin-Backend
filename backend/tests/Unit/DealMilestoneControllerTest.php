<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\DealMilestone;
use App\Models\Deal;
use App\Models\Property;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DealMilestoneControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $deal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);
        $this->deal = Deal::factory()->create([
            'property_id' => $property->id,
            'lead_id' => $lead->id,
            'buyer_id' => $this->user->id
        ]);
        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function index_method_returns_paginated_milestones()
    {
        DealMilestone::factory()->count(15)->create(['deal_id' => $this->deal->id]);
        
        $response = $this->getJson('/api/deal-milestones');
        
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
        $response = $this->postJson('/api/deal-milestones', []);
        
        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ]);

        $json = $response->json();
        $this->assertArrayHasKey('error', $json);
        $this->assertArrayHasKey('details', $json['error']);
        $this->assertArrayHasKey('field_errors', $json['error']['details']);
        
        $errors = $json['error']['details']['field_errors'];
        $errorString = implode(' ', $errors);
        $this->assertTrue(
            str_contains($errorString, 'deal id') ||
            str_contains($errorString, 'milestone type') ||
            str_contains($errorString, 'title')
        );
    }

    /** @test */
    public function store_method_creates_milestone_successfully()
    {
        $milestoneData = [
            'deal_id' => $this->deal->id,
            'milestone_type' => 'contract_signed',
            'title' => 'Contract Signed',
            'description' => 'Purchase contract has been signed',
            'due_date' => '2025-02-01'
        ];
        
        $response = $this->postJson('/api/deal-milestones', $milestoneData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'deal_id', 'milestone_type', 'title']
            ]);
        
        $this->assertDatabaseHas('deal_milestones', [
            'deal_id' => $this->deal->id,
            'milestone_type' => 'contract_signed',
            'title' => 'Contract Signed'
        ]);
    }

    /** @test */
    public function complete_method_marks_milestone_as_completed()
    {
        $milestone = DealMilestone::factory()->create([
            'deal_id' => $this->deal->id,
            'completed_at' => null
        ]);
        
        $response = $this->patchJson("/api/deal-milestones/{$milestone->id}/complete");
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $milestone->id
                ]
            ]);
        
        $this->assertDatabaseHas('deal_milestones', [
            'id' => $milestone->id
        ]);
        
        // Check that completed_at is not null
        $updatedMilestone = DealMilestone::find($milestone->id);
        $this->assertNotNull($updatedMilestone->completed_at);
    }

    /** @test */
    public function show_method_returns_existing_milestone()
    {
        $milestone = DealMilestone::factory()->create(['deal_id' => $this->deal->id]);
        
        $response = $this->getJson("/api/deal-milestones/{$milestone->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $milestone->id
                ]
            ]);
    }

    /** @test */
    public function update_method_updates_milestone_successfully()
    {
        $milestone = DealMilestone::factory()->create(['deal_id' => $this->deal->id]);
        
        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description'
        ];
        
        $response = $this->putJson("/api/deal-milestones/{$milestone->id}", $updateData);
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $milestone->id,
                    'title' => 'Updated Title'
                ]
            ]);
    }

    /** @test */
    public function destroy_method_deletes_milestone_successfully()
    {
        $milestone = DealMilestone::factory()->create(['deal_id' => $this->deal->id]);
        
        $response = $this->deleteJson("/api/deal-milestones/{$milestone->id}");
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
        
        $this->assertDatabaseMissing('deal_milestones', ['id' => $milestone->id]);
    }
}
