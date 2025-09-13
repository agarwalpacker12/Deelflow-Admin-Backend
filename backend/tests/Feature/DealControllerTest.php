<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Deal;
use App\Models\Property;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DealControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $property;
    protected $lead;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->property = Property::factory()->create(['user_id' => $this->user->id]);
        $this->lead = Lead::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function authenticated_user_can_get_deals_list()
    {
        Deal::factory()->count(3)->create([
            'property_id' => $this->property->id,
            'lead_id' => $this->lead->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/deals');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'uuid',
                            'property_id',
                            'lead_id',
                            'deal_type',
                            'purchase_price',
                            'status'
                        ]
                    ],
                    'meta'
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_create_deal()
    {
        $dealData = [
            'property_id' => $this->property->id,
            'lead_id' => $this->lead->id,
            'deal_type' => 'wholesale',
            'purchase_price' => 150000,
            'sale_price' => 175000,
            'contract_date' => '2025-01-01',
            'closing_date' => '2025-02-01',
            'earnest_money' => 5000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/deals', $dealData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'uuid',
                    'property_id',
                    'lead_id',
                    'deal_type',
                    'purchase_price',
                    'status'
                ]
            ]);

        $this->assertDatabaseHas('deals', [
            'property_id' => $dealData['property_id'],
            'lead_id' => $dealData['lead_id'],
            'deal_type' => $dealData['deal_type']
        ]);
    }

    /** @test */
    public function authenticated_user_can_view_specific_deal()
    {
        $deal = Deal::factory()->create([
            'property_id' => $this->property->id,
            'lead_id' => $this->lead->id,
            'buyer_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/deals/{$deal->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'uuid',
                    'property_id',
                    'lead_id',
                    'deal_type'
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_deal()
    {
        $deal = Deal::factory()->create([
            'property_id' => $this->property->id,
            'lead_id' => $this->lead->id,
            'buyer_id' => $this->user->id
        ]);

        $updateData = [
            'purchase_price' => 160000,
            'status' => 'pending'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/deals/{$deal->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'purchase_price' => 160000,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function authenticated_user_can_delete_deal()
    {
        $deal = Deal::factory()->create([
            'property_id' => $this->property->id,
            'lead_id' => $this->lead->id,
            'buyer_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/deals/{$deal->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Deal deleted successfully'
            ]);

        $this->assertDatabaseMissing('deals', ['id' => $deal->id]);
    }

    /** @test */
    public function authenticated_user_can_get_deal_milestones()
    {
        $deal = Deal::factory()->create([
            'property_id' => $this->property->id,
            'lead_id' => $this->lead->id,
            'buyer_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/deals/{$deal->id}/milestones");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_deals()
    {
        $response = $this->getJson('/api/deals');
        $response->assertStatus(401);
    }

    /** @test */
    public function deal_validation_works_correctly()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/deals', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ])
            ->assertJsonPath('error.details.field_errors', function ($errors) {
                return is_array($errors) && count($errors) > 0 && 
                       (str_contains(implode(' ', $errors), 'property id') ||
                        str_contains(implode(' ', $errors), 'deal type') ||
                        str_contains(implode(' ', $errors), 'purchase price') ||
                        str_contains(implode(' ', $errors), 'contract date') ||
                        str_contains(implode(' ', $errors), 'closing date'));
            });
    }

    /** @test */
    public function deal_model_relationships_work()
    {
        $deal = Deal::factory()->create([
            'property_id' => $this->property->id,
            'lead_id' => $this->lead->id
        ]);
        
        $this->assertInstanceOf(Property::class, $deal->property);
        $this->assertInstanceOf(Lead::class, $deal->lead);
        $this->assertEquals($this->property->id, $deal->property->id);
        $this->assertEquals($this->lead->id, $deal->lead->id);
    }
}
