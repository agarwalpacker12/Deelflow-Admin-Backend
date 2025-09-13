<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Deal;
use App\Models\DealMilestone;
use App\Models\Lead;
use App\Models\Property;
use App\Models\PropertySave;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_user_achievements()
    {
        $user = User::factory()->create();
        UserAchievement::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(UserAchievement::class, $user->achievements->first());
    }

    public function test_user_has_many_properties()
    {
        $user = User::factory()->create();
        Property::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Property::class, $user->properties->first());
    }

    public function test_user_has_many_property_saves()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        PropertySave::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $this->assertInstanceOf(PropertySave::class, $user->propertySaves->first());
    }

    public function test_user_has_many_leads()
    {
        $user = User::factory()->create();
        Lead::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Lead::class, $user->leads->first());
    }

    public function test_user_has_many_deals_as_wholesaler()
    {
        $user = User::factory()->create();
        Deal::factory()->create(['buyer_id' => $user->id]);

        $this->assertInstanceOf(Deal::class, $user->dealsAsWholesaler->first());
    }

    public function test_user_has_many_deals_as_buyer()
    {
        $user = User::factory()->create();
        Deal::factory()->create(['buyer_id' => $user->id]);

        $this->assertInstanceOf(Deal::class, $user->dealsAsBuyer->first());
    }

    public function test_user_has_many_deals_as_seller()
    {
        $user = User::factory()->create();
        Deal::factory()->create(['seller_id' => $user->id]);

        $this->assertInstanceOf(Deal::class, $user->dealsAsSeller->first());
    }

    public function test_user_has_many_deals_as_funder()
    {
        $user = User::factory()->create();
        Deal::factory()->create(['funder_id' => $user->id]);

        $this->assertInstanceOf(Deal::class, $user->dealsAsFunder->first());
    }

    public function test_user_has_many_ai_conversations()
    {
        $user = User::factory()->create();
        AiConversation::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(AiConversation::class, $user->aiConversations->first());
    }

    public function test_user_has_many_campaigns()
    {
        $user = User::factory()->create();
        Campaign::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Campaign::class, $user->campaigns->first());
    }

    public function test_property_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $property->user);
    }

    public function test_property_has_many_property_saves()
    {
        $property = Property::factory()->create();
        PropertySave::factory()->create(['property_id' => $property->id]);

        $this->assertInstanceOf(PropertySave::class, $property->saves->first());
    }

    public function test_property_has_many_deals()
    {
        $property = Property::factory()->create();
        Deal::factory()->create(['property_id' => $property->id]);

        $this->assertInstanceOf(Deal::class, $property->deals->first());
    }

    public function test_property_has_many_ai_conversations()
    {
        $property = Property::factory()->create();
        AiConversation::factory()->create(['property_id' => $property->id]);

        $this->assertInstanceOf(AiConversation::class, $property->aiConversations->first());
    }

    public function test_lead_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $lead->user);
    }

    public function test_lead_has_many_deals()
    {
        $lead = Lead::factory()->create();
        Deal::factory()->create(['lead_id' => $lead->id]);

        $this->assertInstanceOf(Deal::class, $lead->deals->first());
    }

    public function test_lead_has_many_ai_conversations()
    {
        $lead = Lead::factory()->create();
        AiConversation::factory()->create(['lead_id' => $lead->id]);

        $this->assertInstanceOf(AiConversation::class, $lead->aiConversations->first());
    }

    public function test_deal_belongs_to_a_property()
    {
        $property = Property::factory()->create();
        $deal = Deal::factory()->create(['property_id' => $property->id]);

        $this->assertInstanceOf(Property::class, $deal->property);
    }

    public function test_deal_belongs_to_a_lead()
    {
        $lead = Lead::factory()->create();
        $deal = Deal::factory()->create(['lead_id' => $lead->id]);

        $this->assertInstanceOf(Lead::class, $deal->lead);
    }

    public function test_deal_has_many_milestones()
    {
        $deal = Deal::factory()->create();
        DealMilestone::factory()->create(['deal_id' => $deal->id]);

        $this->assertInstanceOf(DealMilestone::class, $deal->milestones->first());
    }

    public function test_campaign_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $campaign->user);
    }

    public function test_campaign_has_many_recipients()
    {
        $campaign = Campaign::factory()->create();
        CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->assertInstanceOf(CampaignRecipient::class, $campaign->recipients->first());
    }

    public function test_campaign_recipient_belongs_to_a_campaign()
    {
        $campaign = Campaign::factory()->create();
        $recipient = CampaignRecipient::factory()->create(['campaign_id' => $campaign->id]);

        $this->assertInstanceOf(Campaign::class, $recipient->campaign);
    }

    public function test_campaign_recipient_belongs_to_a_lead()
    {
        $lead = Lead::factory()->create();
        $recipient = CampaignRecipient::factory()->create(['lead_id' => $lead->id]);

        $this->assertInstanceOf(Lead::class, $recipient->lead);
    }
}
