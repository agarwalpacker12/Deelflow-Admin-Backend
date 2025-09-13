<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
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
            'address' => fake()->streetAddress(),
            'unit' => fake()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
            'county' => fake()->word(),
            'location' => ['lat' => fake()->latitude(), 'lng' => fake()->longitude()],
            'property_type' => 'single_family',
            'bedrooms' => fake()->numberBetween(1, 5),
            'bathrooms' => fake()->randomFloat(1, 1, 4),
            'square_feet' => fake()->numberBetween(1000, 5000),
            'lot_size' => fake()->randomFloat(2, 0, 5),
            'year_built' => fake()->year(),
            'stories' => fake()->numberBetween(1, 3),
            'garage_spaces' => fake()->numberBetween(0, 4),
            'purchase_price' => fake()->numberBetween(100000, 1000000),
            'arv' => fake()->numberBetween(150000, 1500000),
            'repair_estimate' => fake()->numberBetween(10000, 100000),
            'holding_costs' => fake()->numberBetween(1000, 10000),
            'ai_score' => fake()->numberBetween(0, 100),
            'transaction_type' => 'assignment',
            'status' => 'draft',
        ];
    }
}
