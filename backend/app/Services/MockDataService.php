<?php

namespace App\Services;

use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MockDataService
{
    protected $faker;
    protected $config;
    protected static $mockUsers = [];
    protected static $mockProperties = [];
    protected static $mockLeads = [];
    protected static $mockDeals = [];
    protected static $mockCampaigns = [];
    protected static $mockAiConversations = [];
    protected static $mockUserAchievements = [];
    protected static $mockPropertySaves = [];
    protected static $mockDealMilestones = [];
    protected static $mockCampaignRecipients = [];

    public function __construct()
    {
        $this->faker = Faker::create();
        $this->faker->seed(config('mockdata.seed', 12345));
        $this->config = config('mockdata.real_estate');
        
        // Initialize mock data if not already done
        if (empty(static::$mockUsers)) {
            $this->initializeMockData();
        }
    }

    protected function initializeMockData()
    {
        // Generate base users
        for ($i = 1; $i <= 50; $i++) {
            static::$mockUsers[$i] = $this->generateUser($i);
        }

        // Generate properties
        for ($i = 1; $i <= 100; $i++) {
            static::$mockProperties[$i] = $this->generateProperty($i);
        }

        // Generate leads
        for ($i = 1; $i <= 200; $i++) {
            static::$mockLeads[$i] = $this->generateLead($i);
        }

        // Generate deals
        for ($i = 1; $i <= 75; $i++) {
            static::$mockDeals[$i] = $this->generateDeal($i);
        }

        // Generate campaigns
        for ($i = 1; $i <= 30; $i++) {
            static::$mockCampaigns[$i] = $this->generateCampaign($i);
        }

        // Generate AI conversations
        for ($i = 1; $i <= 150; $i++) {
            static::$mockAiConversations[$i] = $this->generateAiConversation($i);
        }

        // Generate user achievements
        for ($i = 1; $i <= 100; $i++) {
            static::$mockUserAchievements[$i] = $this->generateUserAchievement($i);
        }

        // Generate property saves
        for ($i = 1; $i <= 80; $i++) {
            static::$mockPropertySaves[$i] = $this->generatePropertySave($i);
        }

        // Generate deal milestones
        for ($i = 1; $i <= 200; $i++) {
            static::$mockDealMilestones[$i] = $this->generateDealMilestone($i);
        }

        // Generate campaign recipients
        for ($i = 1; $i <= 300; $i++) {
            static::$mockCampaignRecipients[$i] = $this->generateCampaignRecipient($i);
        }
    }

    public function generateUser($id)
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $role = $this->faker->randomElement($this->config['user_roles']);
        
        return [
            'id' => $id,
            'uuid' => $this->faker->uuid,
            'email' => strtolower($firstName . '.' . $lastName . '@' . $this->faker->safeEmailDomain),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $this->faker->phoneNumber,
            'role' => $role,
            'level' => $this->faker->numberBetween(1, 10),
            'points' => $this->faker->numberBetween(0, 5000),
            'subscription_tier' => $this->faker->randomElement($this->config['subscription_tiers']),
            'subscription_status' => $this->faker->randomElement(['active', 'inactive', 'cancelled']),
            'is_verified' => $this->faker->boolean(80),
            'is_active' => $this->faker->boolean(90),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    public function generateProperty($id)
    {
        $city = $this->faker->randomElement($this->config['cities']);
        $propertyType = $this->faker->randomElement($this->config['property_types']);
        $transactionType = $this->faker->randomElement($this->config['transaction_types']);
        
        $purchasePrice = $this->faker->numberBetween(50000, 500000);
        $arv = $purchasePrice * $this->faker->randomFloat(2, 1.2, 1.8);
        $repairEstimate = $this->faker->numberBetween(5000, 50000);
        $holdingCosts = $this->faker->numberBetween(1000, 10000);
        $profitPotential = $arv - $purchasePrice - $repairEstimate - $holdingCosts;
        
        return [
            'id' => $id,
            'uuid' => $this->faker->uuid,
            'user_id' => $this->faker->numberBetween(1, 50),
            'address' => $this->faker->streetAddress,
            'unit' => $this->faker->optional(0.3)->randomElement(['Unit A', 'Unit B', 'Apt 1', 'Apt 2']),
            'city' => $city['name'],
            'state' => $city['state'],
            'zip' => $this->faker->postcode,
            'county' => $city['county'],
            'property_type' => $propertyType,
            'bedrooms' => $this->faker->numberBetween(1, 6),
            'bathrooms' => $this->faker->randomFloat(1, 1, 4),
            'square_feet' => $this->faker->numberBetween(800, 4000),
            'lot_size' => $this->faker->randomFloat(2, 0.1, 2.0),
            'year_built' => $this->faker->numberBetween(1950, 2020),
            'purchase_price' => $purchasePrice,
            'arv' => $arv,
            'repair_estimate' => $repairEstimate,
            'holding_costs' => $holdingCosts,
            'profit_potential' => $profitPotential,
            'ai_score' => $this->faker->numberBetween(60, 100),
            'transaction_type' => $transactionType,
            'assignment_fee' => $transactionType === 'assignment' ? $this->faker->numberBetween(5000, 25000) : null,
            'status' => $this->faker->randomElement(['draft', 'active', 'pending', 'sold']),
            'view_count' => $this->faker->numberBetween(0, 100),
            'save_count' => $this->faker->numberBetween(0, 20),
            'inquiry_count' => $this->faker->numberBetween(0, 15),
            'images' => [
                'https://example.com/images/property' . $id . '_1.jpg',
                'https://example.com/images/property' . $id . '_2.jpg',
            ],
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    public function generateLead($id)
    {
        $city = $this->faker->randomElement($this->config['cities']);
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        
        return [
            'id' => $id,
            'uuid' => $this->faker->uuid,
            'user_id' => $this->faker->numberBetween(1, 50),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($firstName . '.' . $lastName . '@' . $this->faker->safeEmailDomain),
            'phone' => $this->faker->phoneNumber,
            'property_address' => $this->faker->streetAddress,
            'property_city' => $city['name'],
            'property_state' => $city['state'],
            'property_zip' => $this->faker->postcode,
            'property_type' => $this->faker->randomElement($this->config['property_types']),
            'ai_score' => $this->faker->numberBetween(50, 100),
            'motivation_score' => $this->faker->numberBetween(40, 100),
            'urgency_score' => $this->faker->numberBetween(30, 100),
            'financial_score' => $this->faker->numberBetween(50, 100),
            'source' => $this->faker->randomElement($this->config['lead_sources']),
            'estimated_value' => $this->faker->numberBetween(100000, 600000),
            'mortgage_balance' => $this->faker->numberBetween(50000, 400000),
            'asking_price' => $this->faker->numberBetween(80000, 500000),
            'status' => $this->faker->randomElement($this->config['lead_statuses']),
            'preferred_contact_method' => $this->faker->randomElement(['phone', 'email', 'text']),
            'next_action' => $this->faker->randomElement([
                'Schedule property visit',
                'Follow up call',
                'Send contract',
                'Property analysis',
                'Market research'
            ]),
            'next_action_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    public function generateDeal($id)
    {
        $dealType = $this->faker->randomElement($this->config['transaction_types']);
        $purchasePrice = $this->faker->numberBetween(100000, 400000);
        $salePrice = $purchasePrice * $this->faker->randomFloat(2, 1.05, 1.3);
        $assignmentFee = $dealType === 'assignment' ? $this->faker->numberBetween(5000, 25000) : null;
        
        return [
            'id' => $id,
            'uuid' => $this->faker->uuid,
            'property_id' => $this->faker->numberBetween(1, 100),
            'lead_id' => $this->faker->numberBetween(1, 200),
            'buyer_id' => $this->faker->numberBetween(1, 50),
            'seller_id' => $this->faker->numberBetween(1, 50),
            'funder_id' => $this->faker->numberBetween(1, 50),
            'deal_type' => $dealType,
            'purchase_price' => $purchasePrice,
            'sale_price' => $salePrice,
            'assignment_fee' => $assignmentFee,
            'contract_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'closing_date' => $this->faker->dateTimeBetween('now', '+60 days')->format('Y-m-d'),
            'inspection_period' => $this->faker->numberBetween(7, 21),
            'earnest_money' => $this->faker->numberBetween(1000, 10000),
            'status' => $this->faker->randomElement($this->config['deal_statuses']),
            'contract_terms' => [
                'financing_contingency' => $this->faker->boolean(),
                'inspection_contingency' => $this->faker->boolean(80),
                'appraisal_contingency' => $this->faker->boolean(60),
            ],
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    public function generateCampaign($id)
    {
        $totalRecipients = $this->faker->numberBetween(100, 1000);
        $sentCount = $this->faker->numberBetween(50, $totalRecipients);
        $openCount = $this->faker->numberBetween(0, $sentCount);
        $clickCount = $this->faker->numberBetween(0, $openCount);
        $responseCount = $this->faker->numberBetween(0, $clickCount);
        $conversionCount = $this->faker->numberBetween(0, $responseCount);
        
        return [
            'id' => $id,
            'user_id' => $this->faker->numberBetween(1, 50),
            'name' => $this->faker->city . ' ' . $this->faker->randomElement(['Distressed Properties', 'Investment Opportunities', 'Quick Sale']) . ' Q' . $this->faker->numberBetween(1, 4),
            'campaign_type' => $this->faker->randomElement($this->config['campaign_types']),
            'channel' => $this->faker->randomElement($this->config['campaign_channels']),
            'target_criteria' => [
                'location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
                'property_type' => $this->faker->randomElement($this->config['property_types']),
                'equity_min' => $this->faker->numberBetween(25000, 100000),
            ],
            'subject_line' => $this->faker->randomElement([
                'We Buy Houses Fast - Cash Offer in 24 Hours',
                'Sell Your House Quickly - No Repairs Needed',
                'Cash for Your Property - Close in 7 Days',
                'Stop Foreclosure - We Can Help',
            ]),
            'status' => $this->faker->randomElement(['draft', 'scheduled', 'active', 'completed', 'paused']),
            'scheduled_at' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d\TH:i:s.u\Z'),
            'total_recipients' => $totalRecipients,
            'sent_count' => $sentCount,
            'open_count' => $openCount,
            'click_count' => $clickCount,
            'response_count' => $responseCount,
            'conversion_count' => $conversionCount,
            'budget' => $this->faker->numberBetween(500, 5000),
            'spent' => $this->faker->numberBetween(100, 4000),
            'created_at' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    public function generateAiConversation($id)
    {
        return [
            'id' => $id,
            'uuid' => $this->faker->uuid,
            'user_id' => $this->faker->numberBetween(1, 50),
            'lead_id' => $this->faker->numberBetween(1, 200),
            'property_id' => $this->faker->optional(0.7)->numberBetween(1, 100),
            'channel' => $this->faker->randomElement($this->config['ai_conversation_channels']),
            'external_id' => 'ext_' . $this->faker->uuid,
            'sentiment_score' => $this->faker->numberBetween(20, 100),
            'urgency_score' => $this->faker->numberBetween(30, 100),
            'motivation_score' => $this->faker->numberBetween(40, 100),
            'qualification_score' => $this->faker->numberBetween(50, 100),
            'extracted_data' => [
                'timeline' => $this->faker->randomElement(['ASAP', '30 days', '60 days', '90 days']),
                'motivation' => $this->faker->randomElement(['divorce', 'financial_distress', 'relocation', 'inheritance']),
                'price_flexibility' => $this->faker->randomElement(['high', 'moderate', 'low']),
            ],
            'identified_pain_points' => $this->faker->randomElements([
                'Financial stress', 'Time pressure', 'Property condition', 'Market uncertainty', 'Family situation'
            ], $this->faker->numberBetween(1, 3)),
            'status' => $this->faker->randomElement(['active', 'completed', 'transferred', 'abandoned']),
            'outcome' => $this->faker->randomElement(['qualified_lead', 'not_interested', 'follow_up_needed', 'appointment_scheduled']),
            'next_steps' => $this->faker->randomElement([
                'Schedule property visit',
                'Send market analysis',
                'Follow up in 1 week',
                'Transfer to human agent',
            ]),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    public function generateUserAchievement($id)
    {
        $achievementTypes = [
            'first_lead' => ['name' => 'First Lead Generated', 'points' => 50],
            'first_deal' => ['name' => 'First Deal Closed', 'points' => 100],
            'lead_milestone' => ['name' => 'Lead Generation Master', 'points' => 75],
            'deal_milestone' => ['name' => 'Deal Closer', 'points' => 150],
            'revenue_milestone' => ['name' => 'Revenue Achievement', 'points' => 200],
        ];
        
        $type = $this->faker->randomKey($achievementTypes);
        $achievement = $achievementTypes[$type];
        
        return [
            'id' => $id,
            'user_id' => $this->faker->numberBetween(1, 50),
            'achievement_type' => $type,
            'achievement_name' => $achievement['name'],
            'points_earned' => $achievement['points'],
            'metadata' => [
                'deal_id' => $type === 'deal_milestone' ? $this->faker->numberBetween(1, 75) : null,
                'deal_value' => $type === 'deal_milestone' ? $this->faker->numberBetween(5000, 25000) : null,
                'lead_count' => $type === 'lead_milestone' ? $this->faker->numberBetween(10, 100) : null,
            ],
            'earned_at' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    public function generatePropertySave($id)
    {
        return [
            'id' => $id,
            'user_id' => $this->faker->numberBetween(1, 50),
            'property_id' => $this->faker->numberBetween(1, 100),
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    public function generateDealMilestone($id)
    {
        $milestoneTypes = [
            'contract_signed' => 'Contract Signed',
            'inspection' => 'Property Inspection',
            'appraisal' => 'Property Appraisal',
            'financing' => 'Financing Approval',
            'title_search' => 'Title Search',
            'closing_prep' => 'Closing Preparation',
            'final_walkthrough' => 'Final Walkthrough',
            'closing' => 'Closing',
        ];
        
        $type = $this->faker->randomKey($milestoneTypes);
        
        return [
            'id' => $id,
            'deal_id' => $this->faker->numberBetween(1, 75),
            'milestone_type' => $type,
            'title' => $milestoneTypes[$type],
            'description' => 'Complete ' . strtolower($milestoneTypes[$type]) . ' process',
            'due_date' => $this->faker->dateTimeBetween('now', '+60 days')->format('Y-m-d'),
            'completed_at' => $this->faker->optional(0.6)->dateTimeBetween('-30 days', 'now')?->format('Y-m-d\TH:i:s.u\Z'),
            'is_critical' => $this->faker->boolean(40),
            'created_at' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    public function generateCampaignRecipient($id)
    {
        return [
            'id' => $id,
            'campaign_id' => $this->faker->numberBetween(1, 30),
            'lead_id' => $this->faker->numberBetween(1, 200),
            'sent_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now')?->format('Y-m-d\TH:i:s.u\Z'),
            'opened_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now')?->format('Y-m-d\TH:i:s.u\Z'),
            'clicked_at' => $this->faker->optional(0.1)->dateTimeBetween('-1 month', 'now')?->format('Y-m-d\TH:i:s.u\Z'),
            'responded_at' => $this->faker->optional(0.05)->dateTimeBetween('-1 month', 'now')?->format('Y-m-d\TH:i:s.u\Z'),
            'created_at' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    // Getter methods for accessing mock data
    public function getUsers($filters = [], $page = 1, $perPage = 10)
    {
        return $this->paginateAndFilter(static::$mockUsers, $filters, $page, $perPage);
    }

    public function getUser($id)
    {
        return static::$mockUsers[$id] ?? null;
    }

    public function getProperties($filters = [], $page = 1, $perPage = 10)
    {
        return $this->paginateAndFilter(static::$mockProperties, $filters, $page, $perPage);
    }

    public function getProperty($id)
    {
        return static::$mockProperties[$id] ?? null;
    }

    public function getLeads($filters = [], $page = 1, $perPage = 10)
    {
        return $this->paginateAndFilter(static::$mockLeads, $filters, $page, $perPage);
    }

    public function getLead($id)
    {
        return static::$mockLeads[$id] ?? null;
    }

    public function getDeals($filters = [], $page = 1, $perPage = 10)
    {
        return $this->paginateAndFilter(static::$mockDeals, $filters, $page, $perPage);
    }

    public function getDeal($id)
    {
        return static::$mockDeals[$id] ?? null;
    }

    public function getCampaigns($filters = [], $page = 1, $perPage = 10)
    {
        return $this->paginateAndFilter(static::$mockCampaigns, $filters, $page, $perPage);
    }

    public function getCampaign($id)
    {
        return static::$mockCampaigns[$id] ?? null;
    }

    public function getAiConversations($filters = [], $page = 1, $perPage = 10)
    {
        return $this->paginateAndFilter(static::$mockAiConversations, $filters, $page, $perPage);
    }

    public function getAiConversation($id)
    {
        return static::$mockAiConversations[$id] ?? null;
    }

    public function getUserAchievements($filters = [], $page = 1, $perPage = 10)
    {
        return $this->paginateAndFilter(static::$mockUserAchievements, $filters, $page, $perPage);
    }

    public function getPropertySaves($filters = [], $page = 1, $perPage = 10)
    {
        return $this->paginateAndFilter(static::$mockPropertySaves, $filters, $page, $perPage);
    }

    public function getDealMilestones($filters = [], $page = 1, $perPage = 10)
    {
        return $this->paginateAndFilter(static::$mockDealMilestones, $filters, $page, $perPage);
    }

    public function getCampaignRecipients($filters = [], $page = 1, $perPage = 10)
    {
        return $this->paginateAndFilter(static::$mockCampaignRecipients, $filters, $page, $perPage);
    }

    protected function paginateAndFilter($data, $filters = [], $page = 1, $perPage = 10)
    {
        // Apply filters
        $filteredData = $this->applyFilters($data, $filters);
        
        // Calculate pagination
        $total = count($filteredData);
        $lastPage = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($filteredData, $offset, $perPage, true);
        
        return [
            'data' => array_values($items),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
            ],
            'links' => [
                'first' => '/api/endpoint?page=1',
                'last' => "/api/endpoint?page={$lastPage}",
                'prev' => $page > 1 ? "/api/endpoint?page=" . ($page - 1) : null,
                'next' => $page < $lastPage ? "/api/endpoint?page=" . ($page + 1) : null,
            ],
        ];
    }

    protected function applyFilters($data, $filters)
    {
        if (empty($filters)) {
            return $data;
        }

        return array_filter($data, function ($item) use ($filters) {
            foreach ($filters as $key => $value) {
                if ($key === 'search') {
                    // Simple search implementation
                    $searchFields = ['first_name', 'last_name', 'email', 'address', 'city', 'name'];
                    $found = false;
                    foreach ($searchFields as $field) {
                        if (isset($item[$field]) && stripos($item[$field], $value) !== false) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) return false;
                } elseif (isset($item[$key])) {
                    if (is_array($value)) {
                        if (!in_array($item[$key], $value)) return false;
                    } else {
                        if ($item[$key] != $value) return false;
                    }
                }
            }
            return true;
        });
    }

    public function createUser($data)
    {
        $id = max(array_keys(static::$mockUsers)) + 1;
        $user = array_merge($this->generateUser($id), $data, ['id' => $id]);
        static::$mockUsers[$id] = $user;
        return $user;
    }

    public function createProperty($data)
    {
        $id = max(array_keys(static::$mockProperties)) + 1;
        $property = array_merge($this->generateProperty($id), $data, ['id' => $id]);
        static::$mockProperties[$id] = $property;
        return $property;
    }

    public function createLead($data)
    {
        $id = max(array_keys(static::$mockLeads)) + 1;
        $lead = array_merge($this->generateLead($id), $data, ['id' => $id]);
        static::$mockLeads[$id] = $lead;
        return $lead;
    }

    public function createDeal($data)
    {
        $id = max(array_keys(static::$mockDeals)) + 1;
        $deal = array_merge($this->generateDeal($id), $data, ['id' => $id]);
        static::$mockDeals[$id] = $deal;
        return $deal;
    }

    public function createCampaign($data)
    {
        $id = max(array_keys(static::$mockCampaigns)) + 1;
        $campaign = array_merge($this->generateCampaign($id), $data, ['id' => $id]);
        static::$mockCampaigns[$id] = $campaign;
        return $campaign;
    }

    public function createAiConversation($data)
    {
        $id = max(array_keys(static::$mockAiConversations)) + 1;
        $aiConversation = array_merge($this->generateAiConversation($id), $data, ['id' => $id]);
        static::$mockAiConversations[$id] = $aiConversation;
        return $aiConversation;
    }

    public function createUserAchievement($data)
    {
        $id = max(array_keys(static::$mockUserAchievements)) + 1;
        $achievement = array_merge($this->generateUserAchievement($id), $data, ['id' => $id]);
        static::$mockUserAchievements[$id] = $achievement;
        return $achievement;
    }

    public function createPropertySave($data)
    {
        $id = max(array_keys(static::$mockPropertySaves)) + 1;
        $propertySave = array_merge($this->generatePropertySave($id), $data, ['id' => $id]);
        static::$mockPropertySaves[$id] = $propertySave;
        return $propertySave;
    }

    public function createDealMilestone($data)
    {
        $id = max(array_keys(static::$mockDealMilestones)) + 1;
        $milestone = array_merge($this->generateDealMilestone($id), $data, ['id' => $id]);
        static::$mockDealMilestones[$id] = $milestone;
        return $milestone;
    }

    public function createCampaignRecipient($data)
    {
        $id = max(array_keys(static::$mockCampaignRecipients)) + 1;
        $recipient = array_merge($this->generateCampaignRecipient($id), $data, ['id' => $id]);
        static::$mockCampaignRecipients[$id] = $recipient;
        return $recipient;
    }

    public function updateUser($id, $data)
    {
        if (isset(static::$mockUsers[$id])) {
            static::$mockUsers[$id] = array_merge(static::$mockUsers[$id], $data);
            static::$mockUsers[$id]['updated_at'] = now()->format('Y-m-d\TH:i:s.u\Z');
            return static::$mockUsers[$id];
        }
        return null;
    }

    public function updateProperty($id, $data)
    {
        if (isset(static::$mockProperties[$id])) {
            static::$mockProperties[$id] = array_merge(static::$mockProperties[$id], $data);
            static::$mockProperties[$id]['updated_at'] = now()->format('Y-m-d\TH:i:s.u\Z');
            return static::$mockProperties[$id];
        }
        return null;
    }

    public function updateLead($id, $data)
    {
        if (isset(static::$mockLeads[$id])) {
            static::$mockLeads[$id] = array_merge(static::$mockLeads[$id], $data);
            static::$mockLeads[$id]['updated_at'] = now()->format('Y-m-d\TH:i:s.u\Z');
            return static::$mockLeads[$id];
        }
        return null;
    }

    public function updateDeal($id, $data)
    {
        if (isset(static::$mockDeals[$id])) {
            static::$mockDeals[$id] = array_merge(static::$mockDeals[$id], $data);
            static::$mockDeals[$id]['updated_at'] = now()->format('Y-m-d\TH:i:s.u\Z');
            return static::$mockDeals[$id];
        }
        return null;
    }

    public function updateCampaign($id, $data)
    {
        if (isset(static::$mockCampaigns[$id])) {
            static::$mockCampaigns[$id] = array_merge(static::$mockCampaigns[$id], $data);
            static::$mockCampaigns[$id]['updated_at'] = now()->format('Y-m-d\TH:i:s.u\Z');
            return static::$mockCampaigns[$id];
        }
        return null;
    }

    public function deleteUser($id)
    {
        if (isset(static::$mockUsers[$id])) {
            unset(static::$mockUsers[$id]);
            return true;
        }
        return false;
    }

    public function deleteProperty($id)
    {
        if (isset(static::$mockProperties[$id])) {
            unset(static::$mockProperties[$id]);
            return true;
        }
        return false;
    }

    public function deleteLead($id)
    {
        if (isset(static::$mockLeads[$id])) {
            unset(static::$mockLeads[$id]);
            return true;
        }
        return false;
    }

    public function deleteDeal($id)
    {
        if (isset(static::$mockDeals[$id])) {
            unset(static::$mockDeals[$id]);
            return true;
        }
        return false;
    }

    public function deleteCampaign($id)
    {
        if (isset(static::$mockCampaigns[$id])) {
            unset(static::$mockCampaigns[$id]);
            return true;
        }
        return false;
    }

    public function deletePropertySave($id)
    {
        if (isset(static::$mockPropertySaves[$id])) {
            unset(static::$mockPropertySaves[$id]);
            return true;
        }
        return false;
    }

    public function deleteDealMilestone($id)
    {
        if (isset(static::$mockDealMilestones[$id])) {
            unset(static::$mockDealMilestones[$id]);
            return true;
        }
        return false;
    }

    // Special methods for AI analysis
    public function getLeadAiScore($leadId)
    {
        $lead = $this->getLead($leadId);
        if (!$lead) return null;

        return [
            'lead_id' => $leadId,
            'ai_score' => $lead['ai_score'],
            'motivation_score' => $lead['motivation_score'],
            'urgency_score' => $lead['urgency_score'],
            'financial_score' => $lead['financial_score'],
            'analysis' => [
                'motivation_factors' => $this->faker->randomElements([
                    'Divorce situation', 'Financial distress', 'Job relocation', 'Inheritance', 'Downsizing'
                ], $this->faker->numberBetween(1, 3)),
                'urgency_indicators' => $this->faker->randomElements([
                    'Needs to sell within 30 days', 'Foreclosure pending', 'Already moved', 'Financial deadline'
                ], $this->faker->numberBetween(1, 2)),
                'financial_capability' => $this->faker->randomElement([
                    'Strong equity position', 'Moderate equity', 'Limited equity', 'Underwater mortgage'
                ])
            ]
        ];
    }

    public function getPropertyAiAnalysis($propertyId)
    {
        $property = $this->getProperty($propertyId);
        if (!$property) return null;

        return [
            'property_id' => $propertyId,
            'ai_score' => $property['ai_score'],
            'market_analysis' => [
                'comparable_sales' => [
                    [
                        'address' => $this->faker->streetAddress,
                        'sale_price' => $this->faker->numberBetween(200000, 400000),
                        'date' => $this->faker->dateTimeBetween('-6 months', '-1 month')->format('Y-m-d')
                    ],
                    [
                        'address' => $this->faker->streetAddress,
                        'sale_price' => $this->faker->numberBetween(200000, 400000),
                        'date' => $this->faker->dateTimeBetween('-6 months', '-1 month')->format('Y-m-d')
                    ]
                ],
                'market_trends' => $this->faker->randomElement([
                    'Appreciating market with 8% YoY growth',
                    'Stable market with 3% YoY growth',
                    'Declining market with -2% YoY change'
                ]),
                'days_on_market_avg' => $this->faker->numberBetween(15, 45)
            ],
            'repair_analysis' => [
                'estimated_repairs' => $property['repair_estimate'],
                'priority_items' => $this->faker->randomElements([
                    'Roof repair', 'HVAC system', 'Kitchen updates', 'Bathroom renovation', 'Flooring', 'Paint'
                ], $this->faker->numberBetween(2, 4)),
                'timeline_estimate' => $this->faker->randomElement([
                    '4-6 weeks', '6-8 weeks', '8-12 weeks'
                ])
            ],
            'investment_metrics' => [
                'profit_potential' => $property['profit_potential'],
                'roi_percentage' => round(($property['profit_potential'] / $property['purchase_price']) * 100, 1),
                'break_even_price' => $property['purchase_price'] + $property['repair_estimate'] + $property['holding_costs']
            ]
        ];
    }

    public function getUserAchievementsSummary($userId)
    {
        $userAchievements = array_filter(static::$mockUserAchievements, function($achievement) use ($userId) {
            return $achievement['user_id'] == $userId;
        });

        $totalPoints = array_sum(array_column($userAchievements, 'points_earned'));
        $currentLevel = min(10, floor($totalPoints / 500) + 1);
        $pointsToNextLevel = ($currentLevel * 500) - $totalPoints;

        return [
            'total_points' => $totalPoints,
            'current_level' => $currentLevel,
            'points_to_next_level' => max(0, $pointsToNextLevel),
            'total_achievements' => count($userAchievements)
        ];
    }
}
