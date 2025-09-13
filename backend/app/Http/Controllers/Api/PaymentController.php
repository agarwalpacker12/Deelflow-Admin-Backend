<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\MockableController;
use App\Models\User;
use App\Models\SubscriptionPackage;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Invoice;
use App\Models\Subscription;

class PaymentController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }


    public function getSubscriptionPacks()
    {
        $packs = SubscriptionPackage::orderBy('amount', 'asc')->get();

        return $this->successResponse($packs, 'Subscription packages retrieved successfully', 200);
    }

    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:subscription_packages,id',
        ]);

        $user = $request->user();

        if (!$user->organization) {
            return $this->notFoundResponse('organization');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $package = SubscriptionPackage::findOrFail($request->package_id);

        $session = Session::create([
            'customer' => $user->stripe_customer_id,
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $package->stripe_price_id,
                'quantity' => 1,
            ]],
            'success_url' => env('FRONTEND_URL')."/app/payment-success?session_id={CHECKOUT_SESSION_ID}')",
            'cancel_url' => env('FRONTEND_URL')."/app//payment-cancel",
            'metadata' => [
                'organization_id' => $user->organization->id,
                'user_id' => $user->id,
                'package_id' => $package->id,
            ],
        ]);

        return $this->successResponse(['redirect_url' => $session->url],"Checkout session created successfully", 200);
    }

    public function createCustomPortalSession(Request $request){
        $user = $request->user();
        
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = PortalSession::create([
            'customer' => $user->stripe_customer_id,
            'return_url' => env("FRONTEND_URL")."/app/billing",
        ]);

         return $this->successResponse(['redirect_url' => $session->url],"User portal session created successfully", 200);
    }


    public function InvoiceList(Request $request){
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = $request->user();

        $invoices = Invoice::all([
            'customer' => $user->stripe_customer_id,
            'limit' => 10,
        ]);
        return $this->successResponse($invoices->data, 'Invoices retrieved successfully', 200);
    }

    public function Subscription(Request $request){
        $user = $request->user();
        $subscription = Subscription::with(['package','organization'])->where('organization_id', $user->organization->id)->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
        return $this->successResponse($subscription, 'Subscription retrieved successfully', 200);
    }


    
}
