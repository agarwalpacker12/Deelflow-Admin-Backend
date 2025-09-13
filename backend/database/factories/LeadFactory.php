<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => User::factory(),
            'lead_type' => fake()->randomElement(['buyer', 'seller']),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'alternate_phone' => fake()->optional()->phoneNumber(),
            'property_address' => fake()->streetAddress(),
            'property_city' => fake()->city(),
            'property_state' => fake()->stateAbbr(),
            'property_zip' => fake()->postcode(),
            'property_type' => fake()->randomElement(['single_family', 'townhouse', 'condo', 'duplex', 'multi_family', 'mobile_home']),
            'ai_score' => fake()->numberBetween(0, 100),
            'motivation_score' => fake()->numberBetween(0, 100),
            'urgency_score' => fake()->numberBetween(0, 100),
            'financial_score' => fake()->numberBetween(0, 100),
            'source' => fake()->randomElement(['campaign', 'website', 'referral', 'cold_call', 'social_media', 'manual']),
            'source_details' => [],
            'estimated_value' => fake()->optional()->randomFloat(2, 100000, 1000000),
            'mortgage_balance' => fake()->optional()->randomFloat(2, 50000, 500000),
            'asking_price' => fake()->optional()->randomFloat(2, 100000, 1200000),
            'status' => fake()->randomElement(['new', 'contacted', 'qualified', 'negotiating', 'contract', 'closed', 'dead']),
            'disposition' => fake()->optional()->sentence(),
            'preferred_contact_method' => fake()->randomElement(['phone', 'email', 'text']),
            'best_time_to_call' => fake()->optional()->randomElement(['morning', 'afternoon', 'evening', 'weekends']),
            'ai_insights' => [],
            'conversation_summary' => fake()->optional()->paragraph(),
            'next_action' => fake()->optional()->sentence(),
            'next_action_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'last_contact_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
