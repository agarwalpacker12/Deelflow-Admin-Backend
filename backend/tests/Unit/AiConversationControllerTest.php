<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AiConversation;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiConversationControllerTest extends TestCase
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
    public function index_method_returns_paginated_conversations()
    {
        AiConversation::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/ai-conversations');

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
        $response = $this->postJson('/api/ai-conversations', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'error' => [
                    'code' => 'VALIDATION_ERROR'
                ]
            ])
            ->assertJsonPath('error.details.field_errors', function ($errors) {
                return is_array($errors) && count($errors) > 0 && 
                       str_contains($errors[0], 'channel field is required');
            });
    }

    /** @test */
    public function store_method_creates_conversation_successfully()
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);
        $conversationData = [
            'lead_id' => $lead->id,
            'channel' => 'chat',
            'messages' => [['role' => 'staff', 'content' => 'Hello']]
        ];

        $response = $this->postJson('/api/ai-conversations', $conversationData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'uuid', 'lead_id', 'channel']
            ]);

        $this->assertDatabaseHas('ai_conversations', [
            'lead_id' => $lead->id,
            'user_id' => $this->user->id,
            'channel' => 'chat'
        ]);
    }

    /** @test */
    public function show_method_returns_existing_conversation()
    {
        $conversation = AiConversation::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/ai-conversations/{$conversation->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $conversation->id
                ]
            ]);
    }

    /** @test */
    public function update_method_updates_conversation_successfully()
    {
        $conversation = AiConversation::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'status' => 'completed',
            'outcome' => 'qualified_lead'
        ];

        $response = $this->putJson("/api/ai-conversations/{$conversation->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $conversation->id,
                    'status' => 'completed'
                ]
            ]);
    }

    /** @test */
    public function destroy_method_deletes_conversation_successfully()
    {
        $conversation = AiConversation::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/ai-conversations/{$conversation->id}");

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('ai_conversations', ['id' => $conversation->id]);
    }
}
