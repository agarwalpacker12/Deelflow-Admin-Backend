<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the relationships between User, Property, Lead, and Deal.
     */
    public function test_user_property_lead_deal_relationships(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $lead = Lead::factory()->create(['user_id' => $user->id]);

        $deal = Deal::factory()->create([
            'property_id' => $property->id,
            'lead_id' => $lead->id,
        ]);

        $this->assertInstanceOf(User::class, $property->user);
        $this->assertEquals($user->id, $property->user->id);

        $this->assertInstanceOf(User::class, $lead->user);
        $this->assertEquals($user->id, $lead->user->id);

        $this->assertInstanceOf(Property::class, $deal->property);
        $this->assertEquals($property->id, $deal->property->id);

        $this->assertInstanceOf(Lead::class, $deal->lead);
        $this->assertEquals($lead->id, $deal->lead->id);

    }
}
