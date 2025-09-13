<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    protected $table = 'subscription_packages'; // table name

    protected $fillable = [
        'name',
        'description',
        'amount',
        'currency',
        'interval',
        'stripe_product_id',
        'stripe_price_id',
    ];
}
