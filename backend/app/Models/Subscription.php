<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'package_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'stripe_price_id',
        'status',
        'current_period_end',
        'card_last4',
        'card_brand'
    ];

    public function package()
    {
        return $this->belongsTo(SubscriptionPackage::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
