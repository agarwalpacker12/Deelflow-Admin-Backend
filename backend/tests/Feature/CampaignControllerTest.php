<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CampaignControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function authenticated_user_can_get_campaigns_list()
    {
        Campaign::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/campaigns');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'name',
                            'campaign_type',
                            'channel',
                            'status',
                            'total_recipients',
                            'sent_count'
                        ]
                    ],
                    'meta'
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_create_campaign()
    {
        $campaignData = [
            'name' => 'Test Campaign',
            'campaign_type' => 'seller_finder',
            'channel' => 'email',
            'subject_line' => 'Test Subject',
            'email_content' => 'Test email content',
            'budget' => 1000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/campaigns', $campaignData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'name',
                    'campaign_type',
                    'channel',
                    'status'
                ]
            ]);

        $this->assertDatabaseHas('campaigns', [
            'name' => $campaignData['name'],
            'campaign_type' => $campaignData['campaign_type'],
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function authenticated_user_can_create_buyer_finder_campaign()
    {
        $campaignData = [
            'name' => 'Test Buyer Campaign',
            'campaign_type' => 'buyer_finder',
            'channel' => 'sms',
            'sms_content' => 'Test sms content',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/campaigns', $campaignData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('campaigns', [
            'name' => $campaignData['name'],
            'campaign_type' => $campaignData['campaign_type'],
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function authenticated_user_can_view_specific_campaign()
    {
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/campaigns/{$campaign->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'name',
                    'campaign_type',
                    'channel'
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_campaign()
    {
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Campaign Name',
            'budget' => 1500
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/campaigns/{$campaign->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'Updated Campaign Name',
            'budget' => 1500
        ]);
    }

    /** @test */
    public function authenticated_user_can_delete_campaign()
    {
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id]);
        $campaign->recipients()->delete();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/campaigns/{$campaign->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Campaign deleted successfully'
            ]);

        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    }

    /** @test */
    public function authenticated_user_can_get_campaign_recipients()
    {
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/campaigns/{$campaign->id}/recipients");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_campaigns()
    {
        $response = $this->getJson('/api/campaigns');
        $response->assertStatus(401);
    }

    /** @test */
    public function campaign_validation_works_correctly()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/campaigns', []);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The name field is required.', $errors);
        $this->assertContains('The campaign type field is required.', $errors);
        $this->assertContains('The channel field is required.', $errors);
    }

    /** @test */
    public function campaign_type_validation_returns_correct_message()
    {
        $campaignData = [
            'name' => 'Test Campaign',
            'campaign_type' => 'invalid_type',
            'channel' => 'email',
            'subject_line' => 'Test Subject',
            'email_content' => 'Test email content',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/campaigns', $campaignData);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The selected campaign type is invalid. Allowed values are: seller_finder, buyer_finder.', $errors);
    }

    /** @test */
    public function email_campaign_requires_email_fields()
    {
        $campaignData = [
            'name' => 'Test Campaign',
            'campaign_type' => 'buyer_finder',
            'channel' => 'email'
            // Missing subject_line and email_content
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/campaigns', $campaignData);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The subject line field is required when channel is email.', $errors);
        $this->assertContains('The email content field is required when channel is email.', $errors);
    }

    /** @test */
    public function sms_campaign_requires_sms_content()
    {
        $campaignData = [
            'name' => 'Test SMS Campaign',
            'campaign_type' => 'seller_finder',
            'channel' => 'sms'
            // Missing sms_content
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/campaigns', $campaignData);

        $response->assertStatus(422);
        $json = $response->json();
        $errors = $json['error']['details']['field_errors'];
        $this->assertContains('The sms content field is required when channel is sms.', $errors);
    }

    /** @test */
    public function campaign_model_relationships_work()
    {
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id]);
        
        $this->assertInstanceOf(User::class, $campaign->user);
        $this->assertEquals($this->user->id, $campaign->user->id);
    }
}
