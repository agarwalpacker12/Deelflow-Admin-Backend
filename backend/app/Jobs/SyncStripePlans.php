<?php


namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use App\Models\SubscriptionPackage;

class SyncStripePlans implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Fetch all active products
        $products = Product::all(['active' => true]);

        foreach ($products->data as $product) {
            // Fetch all prices for the product
            $prices = Price::all([
                'product' => $product->id,
                'active' => true,
            ]);

            foreach ($prices->data as $price) {
                SubscriptionPackage::updateOrCreate(
                    ['stripe_price_id' => $price->id],
                    [
                        'name' => $product->name,
                        'description' => $product->description ?? '',
                        'amount' => $price->unit_amount / 100,
                        'currency' => $price->currency,
                        'interval' => $price->recurring?->interval,
                        'stripe_product_id' => $product->id,
                    ]
                );
            }
        }
    }
}

