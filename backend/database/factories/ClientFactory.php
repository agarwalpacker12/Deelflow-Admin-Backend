<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clientType = fake()->randomElement(['seller', 'buyer']);
        
        return [
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => User::factory(),
            'client_type' => $clientType,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'alternate_phone' => fake()->optional()->phoneNumber(),
            'date_of_birth' => fake()->optional()->dateTimeBetween('-70 years', '-18 years'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
            'country' => 'USA',
            'occupation' => fake()->optional()->jobTitle(),
            'employer' => fake()->optional()->company(),
            'annual_income' => fake()->optional()->randomFloat(2, 30000, 500000),
            'net_worth' => fake()->optional()->randomFloat(2, 50000, 2000000),
            'liquid_assets' => fake()->optional()->randomFloat(2, 10000, 500000),
            'credit_score' => fake()->optional()->numberBetween(300, 850),
            'has_financing_preapproval' => fake()->boolean(30),
            'financing_amount' => fake()->optional()->randomFloat(2, 100000, 1000000),
            'investment_criteria' => $clientType === 'buyer' ? $this->generateInvestmentCriteria() : [],
            'investment_goals' => $clientType === 'buyer' ? $this->generateInvestmentGoals() : [],
            'investment_experience' => $clientType === 'buyer' ? fake()->randomElement(['beginner', 'intermediate', 'expert']) : null,
            'owned_properties' => $clientType === 'seller' ? $this->generateOwnedProperties() : [],
            'selling_motivation' => $clientType === 'seller' ? fake()->randomElement([
                'Financial distress', 'Relocation', 'Downsizing', 'Investment liquidation', 
                'Divorce', 'Inheritance', 'Job change', 'Retirement'
            ]) : null,
            'selling_timeline' => $clientType === 'seller' ? fake()->randomElement([
                'ASAP', '30 days', '60 days', '90 days', '6 months', 'Flexible'
            ]) : null,
            'preferred_contact_method' => fake()->randomElement(['phone', 'email', 'text']),
            'best_time_to_call' => fake()->optional()->randomElement([
                'Morning (8-12)', 'Afternoon (12-5)', 'Evening (5-8)', 'Weekends only'
            ]),
            'communication_notes' => [],
            'status' => fake()->randomElement(['prospect', 'active', 'closed', 'inactive']),
            'source' => fake()->randomElement(['campaign', 'referral', 'website', 'cold_call', 'networking', 'social_media']),
            'notes' => fake()->optional()->paragraph(),
            'relationship_score' => fake()->numberBetween(0, 100),
            'last_contact_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'next_followup_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'custom_fields' => [],
            'tags' => fake()->randomElements(['hot-lead', 'qualified', 'cash-buyer', 'motivated-seller', 'first-time', 'investor'], fake()->numberBetween(0, 3)),
        ];
    }

    /**
     * Generate investment criteria for buyers
     */
    private function generateInvestmentCriteria(): array
    {
        return [
            'property_types' => fake()->randomElements(['single_family', 'duplex', 'multi_family', 'condo', 'townhouse'], fake()->numberBetween(1, 3)),
            'min_price' => fake()->numberBetween(50000, 200000),
            'max_price' => fake()->numberBetween(300000, 1000000),
            'preferred_locations' => fake()->randomElements(['Downtown', 'Suburbs', 'Rural', 'Waterfront'], fake()->numberBetween(1, 2)),
            'min_bedrooms' => fake()->numberBetween(2, 4),
            'min_bathrooms' => fake()->numberBetween(1, 3),
            'condition_preference' => fake()->randomElement(['move_in_ready', 'light_rehab', 'heavy_rehab', 'any']),
        ];
    }

    /**
     * Generate investment goals for buyers
     */
    private function generateInvestmentGoals(): array
    {
        return [
            'primary_goal' => fake()->randomElement(['flip', 'rental', 'wholesale', 'buy_and_hold', 'primary_residence']),
            'target_roi' => fake()->numberBetween(10, 30),
            'timeline' => fake()->randomElement(['immediate', '30_days', '60_days', '90_days', 'flexible']),
            'cash_available' => fake()->randomFloat(2, 50000, 500000),
        ];
    }

    /**
     * Generate owned properties for sellers
     */
    private function generateOwnedProperties(): array
    {
        $propertyCount = fake()->numberBetween(1, 3);
        $properties = [];
        
        for ($i = 0; $i < $propertyCount; $i++) {
            $properties[] = [
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'property_type' => fake()->randomElement(['single_family', 'duplex', 'condo', 'townhouse']),
                'estimated_value' => fake()->randomFloat(2, 100000, 800000),
                'mortgage_balance' => fake()->randomFloat(2, 0, 400000),
                'rental_income' => fake()->optional()->randomFloat(2, 800, 3000),
            ];
        }
        
        return $properties;
    }

    /**
     * Create a seller client
     */
    public function seller(): static
    {
        return $this->state(fn (array $attributes) => [
            'client_type' => 'seller',
            'investment_criteria' => [],
            'investment_goals' => [],
            'investment_experience' => null,
            'owned_properties' => $this->generateOwnedProperties(),
            'selling_motivation' => fake()->randomElement([
                'Financial distress', 'Relocation', 'Downsizing', 'Investment liquidation', 
                'Divorce', 'Inheritance', 'Job change', 'Retirement'
            ]),
            'selling_timeline' => fake()->randomElement([
                'ASAP', '30 days', '60 days', '90 days', '6 months', 'Flexible'
            ]),
        ]);
    }

    /**
     * Create a buyer client
     */
    public function buyer(): static
    {
        return $this->state(fn (array $attributes) => [
            'client_type' => 'buyer',
            'investment_criteria' => $this->generateInvestmentCriteria(),
            'investment_goals' => $this->generateInvestmentGoals(),
            'investment_experience' => fake()->randomElement(['beginner', 'intermediate', 'expert']),
            'owned_properties' => [],
            'selling_motivation' => null,
            'selling_timeline' => null,
        ]);
    }
}
