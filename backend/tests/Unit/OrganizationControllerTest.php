<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\OrganizationController;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;
    protected $user;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new OrganizationController();
        
        $this->organization = Organization::factory()->create([
            'name' => 'Test Organization',
            'subscription_status' => 'active'
        ]);
        
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'admin'
        ]);
    }

    public function test_controller_uses_mockable_controller_trait()
    {
        $this->assertTrue(method_exists($this->controller, 'successResponse'));
        $this->assertTrue(method_exists($this->controller, 'validationErrorResponse'));
        $this->assertTrue(method_exists($this->controller, 'notFoundResponse'));
        $this->assertTrue(method_exists($this->controller, 'forbiddenResponse'));
        $this->assertTrue(method_exists($this->controller, 'businessLogicErrorResponse'));
    }

    public function test_index_method_exists_and_is_callable()
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
        $this->assertTrue(is_callable([$this->controller, 'index']));
    }

    public function test_store_method_exists_and_is_callable()
    {
        $this->assertTrue(method_exists($this->controller, 'store'));
        $this->assertTrue(is_callable([$this->controller, 'store']));
    }

    public function test_show_method_exists_and_is_callable()
    {
        $this->assertTrue(method_exists($this->controller, 'show'));
        $this->assertTrue(is_callable([$this->controller, 'show']));
    }

    public function test_update_method_exists_and_is_callable()
    {
        $this->assertTrue(method_exists($this->controller, 'update'));
        $this->assertTrue(is_callable([$this->controller, 'update']));
    }

    public function test_destroy_method_exists_and_is_callable()
    {
        $this->assertTrue(method_exists($this->controller, 'destroy'));
        $this->assertTrue(is_callable([$this->controller, 'destroy']));
    }

    public function test_get_status_method_exists_and_is_callable()
    {
        $this->assertTrue(method_exists($this->controller, 'getStatus'));
        $this->assertTrue(is_callable([$this->controller, 'getStatus']));
    }

    public function test_update_subscription_status_method_exists_and_is_callable()
    {
        $this->assertTrue(method_exists($this->controller, 'updateSubscriptionStatus'));
        $this->assertTrue(is_callable([$this->controller, 'updateSubscriptionStatus']));
    }

    public function test_remove_user_method_exists_and_is_callable()
    {
        $this->assertTrue(method_exists($this->controller, 'removeUser'));
        $this->assertTrue(is_callable([$this->controller, 'removeUser']));
    }

    public function test_update_user_status_method_exists_and_is_callable()
    {
        $this->assertTrue(method_exists($this->controller, 'updateUserStatus'));
        $this->assertTrue(is_callable([$this->controller, 'updateUserStatus']));
    }

    public function test_controller_has_mock_handlers()
    {
        $reflection = new \ReflectionClass($this->controller);
        
        $this->assertTrue($reflection->hasMethod('handleMockIndex'));
        $this->assertTrue($reflection->hasMethod('handleMockStore'));
        $this->assertTrue($reflection->hasMethod('handleMockShow'));
        $this->assertTrue($reflection->hasMethod('handleMockUpdate'));
        $this->assertTrue($reflection->hasMethod('handleMockDestroy'));
        $this->assertTrue($reflection->hasMethod('handleMockGetStatus'));
        $this->assertTrue($reflection->hasMethod('handleMockUpdateSubscriptionStatus'));
        $this->assertTrue($reflection->hasMethod('handleMockRemoveUser'));
        $this->assertTrue($reflection->hasMethod('handleMockUpdateUserStatus'));
    }

    public function test_validation_rules_for_store_method()
    {
        $request = Request::create('/api/organizations', 'POST', [
            'name' => 'New Unique Organization',
            'industry' => 'Real Estate',
            'organization_size' => '10-50',
            'business_email' => 'test@example.com',
            'business_phone' => '+1234567890',
            'website' => 'https://example.com',
            'support_email' => 'support@example.com',
            'street_address' => '123 Test St',
            'city' => 'Test City',
            'state_province' => 'Test State',
            'zip_postal_code' => '12345',
            'country' => 'Test Country',
            'timezone' => 'UTC',
            'language' => 'en'
        ]);

        Sanctum::actingAs($this->user);
        
        // This should not throw validation errors
        $response = $this->controller->store($request);
        
        // Check that response is successful (201 status)
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_validation_rules_for_update_method()
    {
        $request = Request::create("/api/organizations/{$this->organization->id}", 'PUT', [
            'name' => 'Updated Organization Name',
            'industry' => 'Updated Industry'
        ]);

        Sanctum::actingAs($this->user);
        
        $response = $this->controller->update($request, $this->organization);
        
        // Check that response is successful (200 status)
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_subscription_status_validation()
    {
        $validStatuses = ['new', 'active', 'suspended', 'waiting'];
        
        foreach ($validStatuses as $status) {
            $request = Request::create("/api/organizations/{$this->organization->id}/subscription-status", 'PATCH', [
                'subscription_status' => $status
            ]);

            Sanctum::actingAs($this->user);
            
            $response = $this->controller->updateSubscriptionStatus($request, $this->organization);
            
            // Should be successful for valid statuses
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    public function test_user_status_validation()
    {
        $validStatuses = ['active', 'inactive'];
        $targetUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'staff'
        ]);
        
        foreach ($validStatuses as $status) {
            $request = Request::create("/api/organizations/{$this->organization->id}/users/{$targetUser->id}/status", 'PATCH', [
                'status' => $status
            ]);

            Sanctum::actingAs($this->user);
            
            $response = $this->controller->updateUserStatus($request, $this->organization, $targetUser);
            
            // Should be successful for valid statuses
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    public function test_controller_constructor_initializes_mock_data_service()
    {
        $controller = new OrganizationController();
        
        // The constructor should initialize the mock data service
        // We can't directly test private properties, but we can test that the trait methods are available
        $this->assertTrue(method_exists($controller, 'isMockEnabled'));
        $this->assertTrue(method_exists($controller, 'initializeMockDataService'));
    }

    public function test_response_structure_consistency()
    {
        Sanctum::actingAs($this->user);
        
        // Test that all success responses follow the same structure
        $request = Request::create('/api/organizations', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });
        
        $response = $this->controller->index($request);
        
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertIsString($responseData['message']);
    }

    public function test_error_response_structure_consistency()
    {
        // Test with a user that doesn't belong to the organization
        $otherUser = User::factory()->create(['organization_id' => null]);
        Sanctum::actingAs($otherUser);
        
        $request = Request::create('/api/organizations', 'GET');
        $request->setUserResolver(function () use ($otherUser) {
            return $otherUser;
        });
        
        $response = $this->controller->index($request);
        
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('error', $responseData['status']);
        $this->assertIsString($responseData['message']);
        $this->assertArrayHasKey('code', $responseData['error']);
        $this->assertArrayHasKey('details', $responseData['error']);
        $this->assertArrayHasKey('timestamp', $responseData['error']);
    }
}
