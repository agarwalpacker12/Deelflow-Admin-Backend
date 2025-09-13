<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deal>
 */
class DealFactory extends Factory
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
            'property_id' => Property::factory(),
            'lead_id' => Lead::factory(),
            'buyer_id' => User::factory(),
            'seller_id' => User::factory(),
            'funder_id' => User::factory(),
            'deal_type' => 'assignment',
            'purchase_price' => fake()->numberBetween(100000, 1000000),
            'sale_price' => fake()->numberBetween(110000, 1100000),
            'assignment_fee' => fake()->numberBetween(5000, 50000),
            'status' => 'draft',
            'organization_id' => function (array $attributes) {
                // If buyer_id is provided, use their organization
                if (isset($attributes['buyer_id'])) {
                    $user = User::find($attributes['buyer_id']);
                    return $user ? $user->organization_id : \App\Models\Organization::factory();
                }
                return \App\Models\Organization::factory();
            },
        ];
    }
}
