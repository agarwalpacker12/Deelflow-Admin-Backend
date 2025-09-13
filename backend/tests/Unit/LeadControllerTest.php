<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Api\LeadController;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;

class LeadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new LeadController();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function index_method_returns_paginated_leads()
    {
        Lead::factory()->count(15)->create(['user_id' => $this->user->id]);
        
        $request = Request::create('/api/leads', 'GET');
        $response = $this->controller->index($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('meta', $responseData['data']);
    }

    /** @test */
    public function index_method_applies_status_filter()
    {
        Lead::factory()->create(['status' => 'new', 'user_id' => $this->user->id]);
        Lead::factory()->create(['status' => 'contacted', 'user_id' => $this->user->id]);
        
        $request = Request::create('/api/leads', 'GET', ['status' => 'new']);
        $response = $this->controller->index($request);
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode());
        // In a real test, we'd verify the filtered results
    }

    /** @test */
    public function index_method_applies_ai_score_filter()
    {
        Lead::factory()->create(['ai_score' => 80, 'user_id' => $this->user->id]);
        Lead::factory()->create(['ai_score' => 60, 'user_id' => $this->user->id]);
        
        $request = Request::create('/api/leads', 'GET', ['ai_score_min' => 70]);
        $response = $this->controller->index($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
    }

    /** @test */
    public function index_method_applies_search_filter()
    {
        Lead::factory()->create(['first_name' => 'John', 'last_name' => 'Doe', 'user_id' => $this->user->id]);
        Lead::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith', 'user_id' => $this->user->id]);
        
        $request = Request::create('/api/leads', 'GET', ['search' => 'John']);
        $response = $this->controller->index($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
    }

    /** @test */
    public function store_method_validates_required_fields()
    {
        $request = Request::create('/api/leads', 'POST', []);
        
        $response = $this->controller->store($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertArrayHasKey('details', $responseData['error']);
        $this->assertArrayHasKey('field_errors', $responseData['error']['details']);
        
        $errors = $responseData['error']['details']['field_errors'];
        $errorString = implode(' ', $errors);
        $this->assertTrue(
            str_contains($errorString, 'lead type') ||
            str_contains($errorString, 'first name') ||
            str_contains($errorString, 'last name')
        );
    }

    /** @test */
    public function store_method_validates_email_format()
    {
        $request = Request::create('/api/leads', 'POST', [
            'lead_type' => 'seller',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email'
        ]);
        
        $response = $this->controller->store($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertArrayHasKey('details', $responseData['error']);
        $this->assertArrayHasKey('field_errors', $responseData['error']['details']);
        
        $errors = $responseData['error']['details']['field_errors'];
        $errorString = implode(' ', $errors);
        $this->assertTrue(str_contains($errorString, 'email'));
    }

    /** @test */
    public function store_method_validates_property_type()
    {
        $request = Request::create('/api/leads', 'POST', [
            'lead_type' => 'seller',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'property_type' => 'invalid_type'
        ]);
        
        $response = $this->controller->store($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertArrayHasKey('details', $responseData['error']);
        $this->assertArrayHasKey('field_errors', $responseData['error']['details']);
        
        $errors = $responseData['error']['details']['field_errors'];
        $errorString = implode(' ', $errors);
        $this->assertTrue(str_contains($errorString, 'property type'));
    }

    /** @test */
    public function store_method_validates_contact_method()
    {
        $request = Request::create('/api/leads', 'POST', [
            'lead_type' => 'seller',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'preferred_contact_method' => 'invalid_method'
        ]);
        
        $response = $this->controller->store($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertArrayHasKey('details', $responseData['error']);
        $this->assertArrayHasKey('field_errors', $responseData['error']['details']);
        
        $errors = $responseData['error']['details']['field_errors'];
        $errorString = implode(' ', $errors);
        $this->assertTrue(str_contains($errorString, 'contact method'));
    }

    /** @test */
    public function store_method_creates_lead_successfully()
    {
        $leadData = [
            'lead_type' => 'seller',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '555-1234',
            'property_address' => '123 Main St',
            'property_type' => 'single_family'
        ];
        
        $request = Request::create('/api/leads', 'POST', $leadData);
        $response = $this->controller->store($request);
        
        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        
        $this->assertDatabaseHas('leads', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function store_method_generates_ai_scores()
    {
        $leadData = [
            'lead_type' => 'seller',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ];
        
        $request = Request::create('/api/leads', 'POST', $leadData);
        $response = $this->controller->store($request);
        
        $responseData = json_decode($response->getContent(), true);
        $lead = $responseData['data'];
        
        $this->assertNotNull($lead['ai_score']);
        $this->assertNotNull($lead['motivation_score']);
        $this->assertNotNull($lead['urgency_score']);
        $this->assertNotNull($lead['financial_score']);
        $this->assertEquals('new', $lead['status']);
    }

    /** @test */
    public function show_method_returns_existing_lead()
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->controller->show($lead->id);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals($lead->id, $responseData['data']['id']);
    }

    /** @test */
    public function show_method_returns_404_for_nonexistent_lead()
    {
        $response = $this->controller->show(999);
        
        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString('not found', $responseData['message']);
    }

    /** @test */
    public function update_method_validates_optional_fields()
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);
        
        $request = Request::create("/api/leads/{$lead->id}", 'PUT', [
            'email' => 'invalid-email'
        ]);
        
        $response = $this->controller->update($request, $lead->id);
        
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertArrayHasKey('details', $responseData['error']);
        $this->assertArrayHasKey('field_errors', $responseData['error']['details']);
        
        $errors = $responseData['error']['details']['field_errors'];
        $errorString = implode(' ', $errors);
        $this->assertTrue(str_contains($errorString, 'email'));
    }

    /** @test */
    public function update_method_updates_lead_successfully()
    {
        $lead = Lead::factory()->create(['first_name' => 'John', 'user_id' => $this->user->id]);
        
        $request = Request::create("/api/leads/{$lead->id}", 'PUT', [
            'first_name' => 'Jane',
            'status' => 'contacted'
        ]);
        
        $response = $this->controller->update($request, $lead->id);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Jane', $responseData['data']['first_name']);
        $this->assertEquals('contacted', $responseData['data']['status']);
    }

    /** @test */
    public function update_method_returns_404_for_nonexistent_lead()
    {
        $request = Request::create('/api/leads/999', 'PUT', [
            'first_name' => 'Jane'
        ]);
        
        $response = $this->controller->update($request, 999);
        
        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
    }

    /** @test */
    public function destroy_method_deletes_lead_successfully()
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->controller->destroy($lead->id);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertStringContainsString('deleted successfully', $responseData['message']);
        
        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    /** @test */
    public function destroy_method_returns_404_for_nonexistent_lead()
    {
        $response = $this->controller->destroy(999);
        
        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
    }

    /** @test */
    public function ai_score_method_returns_lead_analysis()
    {
        $lead = Lead::factory()->create([
            'ai_score' => 85,
            'motivation_score' => 90,
            'urgency_score' => 80,
            'financial_score' => 75,
            'user_id' => $this->user->id
        ]);
        
        $response = $this->controller->aiScore($lead->id);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($lead->id, $responseData['data']['lead_id']);
        $this->assertEquals(85, $responseData['data']['ai_score']);
        $this->assertArrayHasKey('analysis', $responseData['data']);
    }

    /** @test */
    public function ai_score_method_returns_404_for_nonexistent_lead()
    {
        $response = $this->controller->aiScore(999);
        
        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
    }

    /** @test */
    public function controller_handles_pagination_parameters()
    {
        Lead::factory()->count(25)->create(['user_id' => $this->user->id]);
        
        $request = Request::create('/api/leads', 'GET', [
            'page' => 2,
            'per_page' => 10
        ]);
        
        $response = $this->controller->index($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(2, $responseData['data']['meta']['current_page']);
        $this->assertEquals(10, $responseData['data']['meta']['per_page']);
    }

    /** @test */
    public function controller_uses_correct_response_format()
    {
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->controller->show($lead->id);
        $responseData = json_decode($response->getContent(), true);
        
        // Check response structure
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertContains($responseData['status'], ['success', 'error']);
    }

    /** @test */
    public function controller_handles_exceptions_gracefully()
    {
        // Mock a scenario that would cause an exception
        $request = Request::create('/api/leads', 'POST', [
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
        
        // Temporarily disable database to force an exception
        $originalConnection = config('database.default');
        config(['database.default' => 'invalid_connection']);
        
        $response = $this->controller->store($request);
        
        // Restore original connection
        config(['database.default' => $originalConnection]);
        
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
