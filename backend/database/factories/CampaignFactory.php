<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory();
        
        return [
            'user_id' => $user,
            'organization_id' => function (array $attributes) {
                return User::find($attributes['user_id'])->organization_id;
            },
            'name' => $this->faker->company . ' Campaign',
            'campaign_type' => $this->faker->randomElement(['seller_finder', 'buyer_finder']),
            'channel' => $this->faker->randomElement(['email', 'sms', 'voice', 'direct_mail']),
            'target_criteria' => [],
            'geofence_center' => null,
            'geofence_radius' => null,
            'subject_line' => $this->faker->sentence(),
            'preview_text' => $this->faker->text(100),
            'email_content' => $this->faker->paragraphs(3, true),
            'sms_content' => $this->faker->text(160),
            'voice_script' => $this->faker->paragraphs(2, true),
            'landing_page_id' => null,
            'use_ai_personalization' => $this->faker->boolean(),
            'ai_tone' => $this->faker->randomElement(['professional', 'friendly', 'urgent', 'casual']),
            'status' => $this->faker->randomElement(['draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled']),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'started_at' => null,
            'completed_at' => null,
            'total_recipients' => $this->faker->numberBetween(0, 1000),
            'sent_count' => $this->faker->numberBetween(0, 500),
            'open_count' => $this->faker->numberBetween(0, 300),
            'click_count' => $this->faker->numberBetween(0, 100),
            'response_count' => $this->faker->numberBetween(0, 50),
            'conversion_count' => $this->faker->numberBetween(0, 25),
            'budget' => $this->faker->optional()->randomFloat(2, 100, 10000),
            'spent' => $this->faker->randomFloat(2, 0, 5000),
        ];
    }
}
