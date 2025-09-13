<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Deal;
use App\Models\DealMilestone;
use App\Models\AiConversation;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\PropertySave;
use App\Models\UserAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AllControllersIntegrationTest extends TestCase
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
    public function all_controller_models_exist_and_are_properly_connected()
    {
        // Test AuthController -> User Model
        $this->assertTrue(class_exists(User::class));
        $this->assertTrue(method_exists($this->user, 'createToken'));
        
        // Test LeadController -> Lead Model
        $this->assertTrue(class_exists(Lead::class));
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);
        $this->assertInstanceOf(User::class, $lead->user);
        
        // Test PropertyController -> Property Model
        $this->assertTrue(class_exists(Property::class));
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        $this->assertInstanceOf(User::class, $property->user);
        
        // Test DealController -> Deal Model
        $this->assertTrue(class_exists(Deal::class));
        $deal = Deal::factory()->create([
            'property_id' => $property->id,
            'lead_id' => $lead->id,
            'wholesaler_id' => $this->user->id
        ]);
        $this->assertInstanceOf(Property::class, $deal->property);
        $this->assertInstanceOf(Lead::class, $deal->lead);
        $this->assertInstanceOf(User::class, $deal->wholesaler);
        
        // Test DealMilestoneController -> DealMilestone Model
        $this->assertTrue(class_exists(DealMilestone::class));
        $milestone = DealMilestone::factory()->create(['deal_id' => $deal->id]);
        $this->assertInstanceOf(Deal::class, $milestone->deal);
        
        // Test AiConversationController -> AiConversation Model
        $this->assertTrue(class_exists(AiConversation::class));
        $conversation = AiConversation::factory()->create([
            'user_id' => $this->user->id,
            'lead_id' => $lead->id,
            'property_id' => $property->id
        ]);
        $this->assertInstanceOf(User::class, $conversation->user);
        $this->assertInstanceOf(Lead::class, $conversation->lead);
        $this->assertInstanceOf(Property::class, $conversation->property);
        
        // Test CampaignController -> Campaign Model
        $this->assertTrue(class_exists(Campaign::class));
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id]);
        $this->assertInstanceOf(User::class, $campaign->user);
        
        // Test CampaignRecipientController -> CampaignRecipient Model
        $this->assertTrue(class_exists(CampaignRecipient::class));
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id
        ]);
        $this->assertInstanceOf(Campaign::class, $recipient->campaign);
        $this->assertInstanceOf(Lead::class, $recipient->lead);
        
        // Test PropertySaveController -> PropertySave Model
        $this->assertTrue(class_exists(PropertySave::class));
        $save = PropertySave::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $property->id
        ]);
        $this->assertInstanceOf(User::class, $save->user);
        $this->assertInstanceOf(Property::class, $save->property);
        
        // Test UserAchievementController -> UserAchievement Model
        $this->assertTrue(class_exists(UserAchievement::class));
        $achievement = UserAchievement::factory()->create(['user_id' => $this->user->id]);
        $this->assertInstanceOf(User::class, $achievement->user);
    }

    /** @test */
    public function all_api_routes_are_accessible_and_return_proper_responses()
    {
        // Create test data
        $lead = Lead::factory()->create(['user_id' => $this->user->id]);
        $property = Property::factory()->create(['user_id' => $this->user->id]);
        $deal = Deal::factory()->create([
            'property_id' => $property->id,
            'lead_id' => $lead->id,
            'wholesaler_id' => $this->user->id
        ]);
        $milestone = DealMilestone::factory()->create(['deal_id' => $deal->id]);
        $conversation = AiConversation::factory()->create([
            'user_id' => $this->user->id,
            'lead_id' => $lead->id
        ]);
        $campaign = Campaign::factory()->create(['user_id' => $this->user->id]);
        $recipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id
        ]);
        $save = PropertySave::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $property->id
        ]);
        $achievement = UserAchievement::factory()->create(['user_id' => $this->user->id]);

        $headers = ['Authorization' => 'Bearer ' . $this->token];

        // Test all GET routes
        $routes = [
            '/api/user' => 200,
            '/api/leads' => 200,
            "/api/leads/{$lead->id}" => 200,
            "/api/leads/{$lead->id}/ai-score" => 200,
            '/api/properties' => 200,
            "/api/properties/{$property->id}" => 200,
            "/api/properties/{$property->id}/ai-analysis" => 200,
            '/api/deals' => 200,
            "/api/deals/{$deal->id}" => 200,
            "/api/deals/{$deal->id}/milestones" => 200,
            '/api/deal-milestones' => 200,
            "/api/deal-milestones/{$milestone->id}" => 200,
            '/api/ai-conversations' => 200,
            "/api/ai-conversations/{$conversation->id}" => 200,
            '/api/campaigns' => 200,
            "/api/campaigns/{$campaign->id}" => 200,
            "/api/campaigns/{$campaign->id}/recipients" => 200,
            '/api/campaign-recipients' => 200,
            "/api/campaign-recipients/{$recipient->id}" => 200,
            '/api/property-saves' => 200,
            "/api/property-saves/{$save->id}" => 200,
            '/api/user-achievements' => 200,
            "/api/user-achievements/{$achievement->id}" => 200,
        ];

        foreach ($routes as $route => $expectedStatus) {
            $response = $this->withHeaders($headers)->getJson($route);
            $response->assertStatus($expectedStatus, "Route {$route} failed with status {$response->status()}");
            
            // Verify response structure
            $response->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
        }
    }

    /** @test */
    public function all_controllers_handle_authentication_properly()
    {
        $routes = [
            '/api/leads',
            '/api/properties',
            '/api/deals',
            '/api/deal-milestones',
            '/api/ai-conversations',
            '/api/campaigns',
            '/api/campaign-recipients',
            '/api/property-saves',
            '/api/user-achievements',
        ];

        foreach ($routes as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(401, "Route {$route} should require authentication");
        }
    }

    /** @test */
    public function all_controllers_handle_validation_properly()
    {
        $headers = ['Authorization' => 'Bearer ' . $this->token];

        $postRoutes = [
            '/api/leads' => ['first_name', 'last_name'],
            '/api/properties' => ['address', 'city', 'state', 'zip', 'property_type', 'purchase_price', 'arv'],
            '/api/deals' => ['property_id', 'deal_type', 'purchase_price', 'contract_date', 'closing_date'],
            '/api/deal-milestones' => ['deal_id', 'milestone_type', 'title', 'due_date'],
            '/api/ai-conversations' => ['channel'],
            '/api/campaigns' => ['name', 'campaign_type', 'channel'],
            '/api/campaign-recipients' => ['campaign_id', 'lead_ids'],
            '/api/property-saves' => ['property_id'],
            '/api/user-achievements' => ['achievement_type', 'achievement_name', 'points_earned'],
        ];

        foreach ($postRoutes as $route => $requiredFields) {
            $response = $this->withHeaders($headers)->postJson($route, []);
            $response->assertStatus(422, "Route {$route} should validate required fields");
            
            foreach ($requiredFields as $field) {
                $response->assertJsonPath("errors.{$field}", function ($errors) {
                    return is_array($errors) && count($errors) > 0;
                }, "Route {$route} should require field {$field}");
            }
        }
    }

    /** @test */
    public function all_models_have_proper_traits_and_methods()
    {
        // Test User model has HasApiTokens trait
        $userTraits = class_uses_recursive(User::class);
        $this->assertContains('Laravel\Sanctum\HasApiTokens', $userTraits);
        
        // Test all models have HasFactory trait
        $models = [
            User::class,
            Lead::class,
            Property::class,
            Deal::class,
            DealMilestone::class,
            AiConversation::class,
            Campaign::class,
            CampaignRecipient::class,
            PropertySave::class,
            UserAchievement::class,
        ];

        foreach ($models as $modelClass) {
            $traits = class_uses_recursive($modelClass);
            $this->assertContains('Illuminate\Database\Eloquent\Factories\HasFactory', $traits, 
                "Model {$modelClass} should have HasFactory trait");
        }
    }

    /** @test */
    public function all_controllers_use_mockable_controller_trait()
    {
        $controllers = [
            'App\Http\Controllers\Api\AuthController',
            'App\Http\Controllers\Api\LeadController',
            'App\Http\Controllers\Api\PropertyController',
            'App\Http\Controllers\Api\DealController',
            'App\Http\Controllers\Api\DealMilestoneController',
            'App\Http\Controllers\Api\AiConversationController',
            'App\Http\Controllers\Api\CampaignController',
            'App\Http\Controllers\Api\CampaignRecipientController',
            'App\Http\Controllers\Api\PropertySaveController',
            'App\Http\Controllers\Api\UserAchievementController',
        ];

        foreach ($controllers as $controllerClass) {
            $this->assertTrue(class_exists($controllerClass), "Controller {$controllerClass} should exist");
            
            $traits = class_uses_recursive($controllerClass);
            $this->assertContains('App\Traits\MockableController', $traits, 
                "Controller {$controllerClass} should use MockableController trait");
        }
    }

    /** @test */
    public function crud_operations_work_for_all_controllers()
    {
        $headers = ['Authorization' => 'Bearer ' . $this->token];

        // Test Lead CRUD
        $leadData = [
            'first_name' => 'Test',
            'last_name' => 'Lead',
            'email' => 'test@example.com',
            'property_type' => 'single_family'
        ];
        $leadResponse = $this->withHeaders($headers)->postJson('/api/leads', $leadData);
        $leadResponse->assertStatus(201);
        $leadId = $leadResponse->json('data.id');

        // Test Property CRUD
        $propertyData = [
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TX',
            'zip' => '12345',
            'property_type' => 'single_family',
            'purchase_price' => 100000,
            'arv' => 150000,
            'transaction_type' => 'wholesale'
        ];
        $propertyResponse = $this->withHeaders($headers)->postJson('/api/properties', $propertyData);
        $propertyResponse->assertStatus(201);
        $propertyId = $propertyResponse->json('data.id');

        // Test Deal CRUD
        $dealData = [
            'property_id' => $propertyId,
            'lead_id' => $leadId,
            'deal_type' => 'wholesale',
            'purchase_price' => 100000,
            'contract_date' => '2025-01-01',
            'closing_date' => '2025-02-01'
        ];
        $dealResponse = $this->withHeaders($headers)->postJson('/api/deals', $dealData);
        $dealResponse->assertStatus(201);
        $dealId = $dealResponse->json('data.id');

        // Test Campaign CRUD
        $campaignData = [
            'name' => 'Test Campaign',
            'campaign_type' => 'lead_generation',
            'channel' => 'email',
            'subject_line' => 'Test Subject',
            'email_content' => 'Test Content'
        ];
        $campaignResponse = $this->withHeaders($headers)->postJson('/api/campaigns', $campaignData);
        $campaignResponse->assertStatus(201);

        // Test PropertySave CRUD
        $saveData = ['property_id' => $propertyId];
        $saveResponse = $this->withHeaders($headers)->postJson('/api/property-saves', $saveData);
        $saveResponse->assertStatus(201);

        // Verify all created successfully
        $this->assertDatabaseHas('leads', ['id' => $leadId]);
        $this->assertDatabaseHas('properties', ['id' => $propertyId]);
        $this->assertDatabaseHas('deals', ['id' => $dealId]);
    }
}
