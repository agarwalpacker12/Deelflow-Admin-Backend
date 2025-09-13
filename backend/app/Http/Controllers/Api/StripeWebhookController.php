<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Subscription as StripeSubscription;
use App\Models\Organization;
use App\Models\Subscription;
use Carbon\Carbon;


class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $endpointSecret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        Log::info('Stripe Webhook Payload: ' . json_encode($event->data->object));
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function handleCheckoutSessionCompleted($session)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $organizationId = $session->metadata->organization_id ?? null;
        $packageId      = $session->metadata->package_id ?? null;
        $userId         = $session->metadata->user_id ?? null;

        if (!$organizationId || !$packageId) {
            Log::warning("Stripe webhook missing metadata", (array) $session);
            return;
        }

        $subscriptionId = $session->subscription ?? null;

        $stripeSubscription = \Stripe\Subscription::retrieve($subscriptionId);
        $cardLast4 = null;
        $cardBrand = null;

        if ($stripeSubscription->default_payment_method) {
            $paymentMethod = \Stripe\PaymentMethod::retrieve($stripeSubscription->default_payment_method);
            $cardLast4 = $paymentMethod->card->last4 ?? null;
            $cardBrand = $paymentMethod->card->brand ?? null;
        }

        if (!$cardLast4 && $stripeSubscription->latest_invoice) {
            $invoice = \Stripe\Invoice::retrieve($stripeSubscription->latest_invoice, [
                'expand' => ['payment_intent.payment_method']
            ]);
            if (isset($invoice->payment_intent->payment_method->card)) {
                $cardLast4 = $invoice->payment_intent->payment_method->card->last4;
                $cardBrand = $invoice->payment_intent->payment_method->card->brand;
            }
        }
        
        if ($subscriptionId) {
            $stripeSubscription = StripeSubscription::retrieve($subscriptionId);
            Log::info('handleCheckoutSessionCompleted:' . json_encode($stripeSubscription));
            // Get price ID from the first item
            $priceId = $stripeSubscription->items->data[0]->price->id ?? null;
            $currentPeriodEnd = $stripeSubscription->items->data[0]->current_period_end
            ? Carbon::createFromTimestamp($stripeSubscription->items->data[0]->current_period_end)
            : null;

            Subscription::updateOrCreate(
                ['stripe_subscription_id' => $subscriptionId],
                [
                    'organization_id'    => $organizationId,
                    'user_id'            => $userId,
                    'package_id'         => $packageId,
                    'status'             => $stripeSubscription->status, // active, trialing, etc.
                    'stripe_customer_id' => $stripeSubscription->customer,
                    'stripe_price_id'    => $priceId,
                    'current_period_end' => $currentPeriodEnd,
                    'card_last4'         => $cardLast4,
                    'card_brand'         => $cardBrand
                ]
            );
        }

        Organization::where('id', $organizationId)
            ->update(['subscription_status' => 'active']);
    }

    protected function handlePaymentFailed($invoice)
    {
        $sub = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

        if ($sub) {
            $sub->update(['status' => 'past_due']);
            Organization::where('id', $sub->organization_id)
                ->update(['subscription_status' => 'past_due']);
        }
    }

    protected function handleSubscriptionDeleted($subscription)
    {
        $sub = Subscription::where('stripe_subscription_id', $subscription->id)->first();

        if ($sub) {
            $sub->update(['status' => 'canceled']);
            Organization::where('id', $sub->organization_id)
                ->update(['subscription_status' => 'canceled']);
        }
    }

    protected function handleSubscriptionUpdated($subscription)
    {
        $sub = Subscription::where('stripe_subscription_id', $subscription->id)->first();

        if ($sub) {
            $sub->update([
                'status'             => $subscription->status,
                'current_period_end' => Carbon::createFromTimestamp($subscription->current_period_end),
            ]);

            Organization::where('id', $sub->organization_id)
                ->update(['subscription_status' => $subscription->status]);
        }
    }
}
