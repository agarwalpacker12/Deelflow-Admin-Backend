<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiConversation>
 */
class AiConversationFactory extends Factory
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
            'lead_id' => Lead::factory(),
            'property_id' => Property::factory(),
            'channel' => $this->faker->randomElement(['chat', 'sms', 'email', 'voice', 'social']),
            'status' => $this->faker->randomElement(['active', 'completed', 'transferred', 'archived']),
        ];
    }
}
