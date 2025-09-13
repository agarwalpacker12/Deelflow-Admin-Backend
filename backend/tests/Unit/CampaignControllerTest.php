<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Lead;
use App\Models\CampaignRecipient;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CampaignControllerTest extends TestCase
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
    public function index_method_returns_paginated_campaigns()
    {
        Campaign::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/campaigns');

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
        $response = $this->postJson('/api/campaigns', []);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The name field is required.', $errors);
        $this->assertContains('The campaign type field is required.', $errors);
        $this->assertContains('The channel field is required.', $errors);
    }

    /** @test */
    public function store_method_creates_campaign_successfully()
    {
        $campaignData = [
            'name' => 'Test Campaign',
            'campaign_type' => 'seller_finder',
            'channel' => 'email',
            'subject_line' => 'Test Subject',
            'email_content' => 'Test Content'
        ];

        $response = $this->postJson('/api/campaigns', $campaignData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'name', 'user_id']
            ]);

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Test Campaign',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function store_method_creates_buyer_finder_campaign_successfully()
    {
        $campaignData = [
            'name' => 'Test Buyer Campaign',
            'campaign_type' => 'buyer_finder',
            'channel' => 'sms',
            'sms_content' => 'Test sms content'
        ];

        $response = $this->postJson('/api/campaigns', $campaignData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Test Buyer Campaign',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function show_method_returns_existing_campaign()
    {
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/campaigns/{$campaign->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $campaign->id
                ]
            ]);
    }

    /** @test */
    public function recipients_method_returns_campaign_recipients()
    {
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id]);
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);
        CampaignRecipient::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id
        ]);

        $response = $this->getJson("/api/campaigns/{$campaign->id}/recipients");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function update_method_updates_campaign_successfully()
    {
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id, 'status' => 'draft']);

        $updateData = [
            'name' => 'Updated Campaign Name',
        ];

        $response = $this->putJson("/api/campaigns/{$campaign->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $campaign->id,
                    'name' => 'Updated Campaign Name'
                ]
            ]);
    }

    /** @test */
    public function destroy_method_deletes_campaign_successfully()
    {
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id, 'status' => 'draft']);

        $response = $this->deleteJson("/api/campaigns/{$campaign->id}");

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    }
}
