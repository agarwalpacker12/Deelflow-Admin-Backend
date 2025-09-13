<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CampaignRecipient;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CampaignRecipientControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $campaign;
    protected $lead;
    protected $otherUser;
    protected $otherCampaign;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->campaign = Campaign::factory()->create(['user_id' => $this->user->id]);
        $this->lead = Lead::factory()->create(['user_id' => $this->user->id]);
        
        // Create another user and campaign for authorization tests
        $this->otherUser = User::factory()->create();
        $this->otherCampaign = Campaign::factory()->create(['user_id' => $this->otherUser->id]);
        
        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function index_method_returns_paginated_recipients()
    {
        CampaignRecipient::factory()->count(15)->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id
        ]);

        $response = $this->getJson('/api/campaign-recipients');

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
    public function index_method_filters_by_campaign_id()
    {
        $campaign2 = Campaign::factory()->create(['user_id' => $this->user->id]);
        $lead2 = Lead::factory()->create(['user_id' => $this->user->id]);

        CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id
        ]);

        CampaignRecipient::factory()->create([
            'campaign_id' => $campaign2->id,
            'lead_id' => $lead2->id
        ]);

        $response = $this->getJson("/api/campaign-recipients?campaign_id={$this->campaign->id}");

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->campaign->id, $data[0]['campaign_id']);
    }

    /** @test */
    public function index_method_filters_by_sent_status()
    {
        CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id,
            'sent_at' => now()
        ]);

        $lead2 = Lead::factory()->create(['user_id' => $this->user->id]);
        CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $lead2->id,
            'sent_at' => null
        ]);

        $response = $this->getJson('/api/campaign-recipients?sent=true');
        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertNotNull($data[0]['sent_at']);

        $response = $this->getJson('/api/campaign-recipients?sent=false');
        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertNull($data[0]['sent_at']);
    }

    /** @test */
    public function index_method_filters_by_opened_status()
    {
        CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id,
            'opened_at' => now()
        ]);

        $lead2 = Lead::factory()->create(['user_id' => $this->user->id]);
        CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $lead2->id,
            'opened_at' => null
        ]);

        $response = $this->getJson('/api/campaign-recipients?opened=true');
        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertNotNull($data[0]['opened_at']);
    }

    /** @test */
    public function index_method_only_shows_user_owned_recipients()
    {
        // Create recipient for current user's campaign
        CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id
        ]);

        // Create recipient for other user's campaign
        $otherLead = Lead::factory()->create(['user_id' => $this->otherUser->id]);
        CampaignRecipient::factory()->create([
            'campaign_id' => $this->otherCampaign->id,
            'lead_id' => $otherLead->id
        ]);

        $response = $this->getJson('/api/campaign-recipients');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->campaign->id, $data[0]['campaign_id']);
    }

    /** @test */
    public function store_method_validates_required_fields()
    {
        $response = $this->postJson('/api/campaign-recipients', []);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The campaign id field is required.', $errors);
        $this->assertContains('The lead ids field is required.', $errors);
    }

    /** @test */
    public function store_method_validates_campaign_exists()
    {
        $response = $this->postJson('/api/campaign-recipients', [
            'campaign_id' => 99999,
            'lead_ids' => [$this->lead->id]
        ]);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The selected campaign id is invalid.', $errors);
    }

    /** @test */
    public function store_method_validates_leads_exist()
    {
        $response = $this->postJson('/api/campaign-recipients', [
            'campaign_id' => $this->campaign->id,
            'lead_ids' => [99999]
        ]);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The selected lead_ids.0 is invalid.', $errors);
    }

    /** @test */
    public function store_method_validates_lead_ids_is_array()
    {
        $response = $this->postJson('/api/campaign-recipients', [
            'campaign_id' => $this->campaign->id,
            'lead_ids' => 'not-an-array'
        ]);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The lead ids field must be an array.', $errors);
    }

    /** @test */
    public function store_method_requires_at_least_one_lead()
    {
        $response = $this->postJson('/api/campaign-recipients', [
            'campaign_id' => $this->campaign->id,
            'lead_ids' => []
        ]);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        
        // Laravel treats empty array as missing field, so we expect "required" error
        $this->assertContains('The lead ids field is required.', $errors);
    }

    /** @test */
    public function store_method_creates_recipients_successfully()
    {
        $lead2 = Lead::factory()->create(['user_id' => $this->user->id]);
        
        $recipientData = [
            'campaign_id' => $this->campaign->id,
            'lead_ids' => [$this->lead->id, $lead2->id],
        ];

        $response = $this->postJson('/api/campaign-recipients', $recipientData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => '2 recipients added successfully'
            ]);

        $this->assertDatabaseHas('campaign_recipients', [
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id,
        ]);

        $this->assertDatabaseHas('campaign_recipients', [
            'campaign_id' => $this->campaign->id,
            'lead_id' => $lead2->id,
        ]);
    }

    /** @test */
    public function store_method_prevents_duplicate_recipients()
    {
        // Create existing recipient
        CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id
        ]);

        $lead2 = Lead::factory()->create(['user_id' => $this->user->id]);
        
        $recipientData = [
            'campaign_id' => $this->campaign->id,
            'lead_ids' => [$this->lead->id, $lead2->id], // First one already exists
        ];

        $response = $this->postJson('/api/campaign-recipients', $recipientData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => '1 recipients added successfully (1 were already recipients)'
            ]);

        // Should only have 2 total recipients (1 existing + 1 new)
        $this->assertEquals(2, CampaignRecipient::where('campaign_id', $this->campaign->id)->count());
    }

    /** @test */
    public function store_method_updates_campaign_total_recipients()
    {
        // Reset campaign total recipients to 0
        $this->campaign->update(['total_recipients' => 0]);
        $this->assertEquals(0, $this->campaign->fresh()->total_recipients);

        $recipientData = [
            'campaign_id' => $this->campaign->id,
            'lead_ids' => [$this->lead->id],
        ];

        $response = $this->postJson('/api/campaign-recipients', $recipientData);

        $response->assertStatus(201);
        $this->assertEquals(1, $this->campaign->fresh()->total_recipients);
    }

    /** @test */
    public function store_method_rejects_other_users_campaign()
    {
        $recipientData = [
            'campaign_id' => $this->otherCampaign->id,
            'lead_ids' => [$this->lead->id],
        ];

        $response = $this->postJson('/api/campaign-recipients', $recipientData);

        $response->assertStatus(404)
            ->assertJsonStructure([
                'status',
                'message',
                'error'
            ])
            ->assertJson([
                'status' => 'error'
            ]);
    }

    /** @test */
    public function show_method_returns_existing_recipient()
    {
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id
        ]);

        $response = $this->getJson("/api/campaign-recipients/{$recipient->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $recipient->id,
                    'campaign_id' => $this->campaign->id,
                    'lead_id' => $this->lead->id
                ]
            ]);
    }

    /** @test */
    public function show_method_returns_404_for_nonexistent_recipient()
    {
        $response = $this->getJson('/api/campaign-recipients/99999');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'status',
                'message',
                'error'
            ])
            ->assertJson([
                'status' => 'error'
            ]);
    }

    /** @test */
    public function show_method_rejects_other_users_recipient()
    {
        $otherLead = Lead::factory()->create(['user_id' => $this->otherUser->id]);
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $this->otherCampaign->id,
            'lead_id' => $otherLead->id
        ]);

        $response = $this->getJson("/api/campaign-recipients/{$recipient->id}");

        $response->assertStatus(404)
            ->assertJsonStructure([
                'status',
                'message',
                'error'
            ])
            ->assertJson([
                'status' => 'error'
            ]);
    }

    /** @test */
    public function update_method_updates_recipient_successfully()
    {
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id,
            'open_count' => 0,
            'click_count' => 0
        ]);

        $updateData = [
            'sent_at' => now()->toDateTimeString(),
            'opened_at' => now()->toDateTimeString(),
            'open_count' => 2,
            'click_count' => 1
        ];

        $response = $this->putJson("/api/campaign-recipients/{$recipient->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $recipient->id,
                    'open_count' => 2,
                    'click_count' => 1
                ]
            ]);

        $this->assertDatabaseHas('campaign_recipients', [
            'id' => $recipient->id,
            'open_count' => 2,
            'click_count' => 1
        ]);
    }

    /** @test */
    public function update_method_validates_date_fields()
    {
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id
        ]);

        $updateData = [
            'sent_at' => 'invalid-date',
        ];

        $response = $this->putJson("/api/campaign-recipients/{$recipient->id}", $updateData);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The sent at field must be a valid date.', $errors);
    }

    /** @test */
    public function update_method_validates_integer_fields()
    {
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id
        ]);

        $updateData = [
            'open_count' => 'not-a-number',
            'click_count' => -1
        ];

        $response = $this->putJson("/api/campaign-recipients/{$recipient->id}", $updateData);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The open count field must be an integer.', $errors);
        $this->assertContains('The click count field must be at least 0.', $errors);
    }

    /** @test */
    public function update_method_rejects_other_users_recipient()
    {
        $otherLead = Lead::factory()->create(['user_id' => $this->otherUser->id]);
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $this->otherCampaign->id,
            'lead_id' => $otherLead->id
        ]);

        $updateData = [
            'open_count' => 1
        ];

        $response = $this->putJson("/api/campaign-recipients/{$recipient->id}", $updateData);

        $response->assertStatus(404)
            ->assertJsonStructure([
                'status',
                'message',
                'error'
            ])
            ->assertJson([
                'status' => 'error'
            ]);
    }

    /** @test */
    public function destroy_method_deletes_recipient_successfully()
    {
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id
        ]);

        // Update campaign total recipients to 1
        $this->campaign->update(['total_recipients' => 1]);

        $response = $this->deleteJson("/api/campaign-recipients/{$recipient->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Campaign recipient removed successfully'
            ]);

        $this->assertDatabaseMissing('campaign_recipients', ['id' => $recipient->id]);
        $this->assertEquals(0, $this->campaign->fresh()->total_recipients);
    }

    /** @test */
    public function destroy_method_updates_campaign_total_recipients()
    {
        $recipient1 = CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id
        ]);

        $lead2 = Lead::factory()->create(['user_id' => $this->user->id]);
        $recipient2 = CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $lead2->id
        ]);

        // Update campaign total recipients to 2
        $this->campaign->update(['total_recipients' => 2]);

        $response = $this->deleteJson("/api/campaign-recipients/{$recipient1->id}");

        $response->assertStatus(200);
        $this->assertEquals(1, $this->campaign->fresh()->total_recipients);
    }

    /** @test */
    public function destroy_method_returns_404_for_nonexistent_recipient()
    {
        $response = $this->deleteJson('/api/campaign-recipients/99999');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'status',
                'message',
                'error'
            ])
            ->assertJson([
                'status' => 'error'
            ]);
    }

    /** @test */
    public function destroy_method_rejects_other_users_recipient()
    {
        $otherLead = Lead::factory()->create(['user_id' => $this->otherUser->id]);
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $this->otherCampaign->id,
            'lead_id' => $otherLead->id
        ]);

        $response = $this->deleteJson("/api/campaign-recipients/{$recipient->id}");

        $response->assertStatus(404)
            ->assertJsonStructure([
                'status',
                'message',
                'error'
            ])
            ->assertJson([
                'status' => 'error'
            ]);
    }

    /** @test */
    public function all_methods_load_relationships()
    {
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $this->lead->id
        ]);

        // Test show method includes relationships
        $response = $this->getJson("/api/campaign-recipients/{$recipient->id}");
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('campaign', $data);
        $this->assertArrayHasKey('lead', $data);

        // Test update method includes relationships
        $response = $this->putJson("/api/campaign-recipients/{$recipient->id}", ['open_count' => 1]);
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('campaign', $data);
        $this->assertArrayHasKey('lead', $data);
    }
}
