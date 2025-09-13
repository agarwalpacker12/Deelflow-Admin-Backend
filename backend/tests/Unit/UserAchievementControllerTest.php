<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Api\UserAchievementController;
use App\Models\UserAchievement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;

class UserAchievementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new UserAchievementController();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function index_method_returns_paginated_achievements()
    {
        UserAchievement::factory()->count(15)->create(['user_id' => $this->user->id]);
        
        $request = Request::create('/api/user-achievements', 'GET');
        $response = $this->controller->index($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('meta', $responseData['data']);
    }

    /** @test */
    public function store_method_validates_required_fields()
    {
        $request = Request::create('/api/user-achievements', 'POST', []);
        
        $response = $this->controller->store($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('achievement_type', $responseData['errors']);
        $this->assertArrayHasKey('achievement_name', $responseData['errors']);
        $this->assertArrayHasKey('points_earned', $responseData['errors']);
    }

    /** @test */
    public function store_method_creates_achievement_successfully()
    {
        $achievementData = [
            'achievement_type' => 'deal_closed',
            'achievement_name' => 'First Deal Closed',
            'description' => 'Closed your first wholesale deal',
            'points_earned' => 100
        ];
        
        $request = Request::create('/api/user-achievements', 'POST', $achievementData);
        $response = $this->controller->store($request);
        
        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        
        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $this->user->id,
            'achievement_type' => 'deal_closed',
            'achievement_name' => 'First Deal Closed',
            'points_earned' => 100
        ]);
    }

    /** @test */
    public function show_method_returns_existing_achievement()
    {
        $achievement = UserAchievement::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->controller->show($achievement->id);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals($achievement->id, $responseData['data']['id']);
    }

    /** @test */
    public function update_method_returns_405_not_supported()
    {
        $achievement = UserAchievement::factory()->create(['user_id' => $this->user->id]);
        
        $request = Request::create("/api/user-achievements/{$achievement->id}", 'PUT', []);
        $response = $this->controller->update($request, $achievement->id);
        
        $this->assertEquals(405, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString('not supported', $responseData['message']);
    }

    /** @test */
    public function destroy_method_deletes_achievement_successfully()
    {
        $achievement = UserAchievement::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->controller->destroy($achievement->id);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        
        $this->assertDatabaseMissing('user_achievements', ['id' => $achievement->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
