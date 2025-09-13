<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mock Data Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file controls the mock data system for the API.
    | When enabled, the API will return realistic mock data instead of
    | querying the database.
    |
    */

    'enabled' => env('MOCK_DATA_ENABLED', false),
    
    'seed' => env('MOCK_DATA_SEED', 12345),
    
    /*
    |--------------------------------------------------------------------------
    | Mock Data Settings
    |--------------------------------------------------------------------------
    */
    
    'pagination' => [
        'default_per_page' => 10,
        'max_per_page' => 100,
    ],
    
    'rate_limiting' => [
        'authenticated_limit' => 1000,
        'unauthenticated_limit' => 100,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Real Estate Mock Data Configuration
    |--------------------------------------------------------------------------
    */
    
    'real_estate' => [
        'cities' => [
            ['name' => 'Austin', 'state' => 'TX', 'county' => 'Travis'],
            ['name' => 'Houston', 'state' => 'TX', 'county' => 'Harris'],
            ['name' => 'Dallas', 'state' => 'TX', 'county' => 'Dallas'],
            ['name' => 'San Antonio', 'state' => 'TX', 'county' => 'Bexar'],
            ['name' => 'Phoenix', 'state' => 'AZ', 'county' => 'Maricopa'],
            ['name' => 'Atlanta', 'state' => 'GA', 'county' => 'Fulton'],
            ['name' => 'Miami', 'state' => 'FL', 'county' => 'Miami-Dade'],
            ['name' => 'Orlando', 'state' => 'FL', 'county' => 'Orange'],
        ],
        
        'property_types' => [
            'single_family',
            'townhouse',
            'condo',
            'duplex',
            'multi_family',
            'mobile_home',
        ],
        
        'transaction_types' => [
            'assignment',
            'double_close',
            'wholesale',
            'fix_and_flip',
            'buy_and_hold',
        ],
        
        'lead_sources' => [
            'website_form',
            'cold_calling',
            'direct_mail',
            'referral',
            'social_media',
            'ppc_ads',
            'seo',
            'networking',
        ],
        
        'lead_statuses' => [
            'new',
            'contacted',
            'qualified',
            'negotiating',
            'contract',
            'closed',
            'dead',
        ],
        
        'deal_statuses' => [
            'draft',
            'active',
            'pending',
            'funded',
            'closing',
            'completed',
            'cancelled',
        ],
        
        
        'subscription_tiers' => [
            'starter',
            'premium',
            'enterprise',
        ],
        
        'campaign_types' => [
            'lead_generation',
            'nurture',
            'follow_up',
            'promotional',
        ],
        
        'campaign_channels' => [
            'email',
            'sms',
            'voice',
            'direct_mail',
            'social_media',
        ],
        
        'ai_conversation_channels' => [
            'chat',
            'sms',
            'email',
            'voice',
            'social',
        ],
    ],
];
