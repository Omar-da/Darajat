<?php

namespace App\Http\Controllers\app;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\PaymentIntent;
use App\Models\Payment;
use App\Models\Order;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        // Verify webhook signature
        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Webhook invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Webhook invalid signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle only the events we care about
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($event->data->object);
                break;
                
            case 'payment_intent.amount_capturable_updated':
                $this->handlePaymentCapture($event->data->object);
                break;
                
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailure($event->data->object);
                break;
                
            case 'payment_intent.canceled':
                $this->handlePaymentCanceled($event->data->object);
                break;
                
            default:
                // Ignore other events
                Log::info('Ignoring webhook event: ' . $event->type);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function handlePaymentSuccess($paymentIntent)
    {
        return response()->json('hello success');
        Log::info('Payment succeeded: ' . $paymentIntent->id);

        // Update database
        Payment::updateOrCreate(
            ['stripe_payment_id' => $paymentIntent->id],
            [
                'user_id' => $paymentIntent->metadata->user_id ?? null,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'status' => 'succeeded',
                'paid_at' => now(),
            ]
        );

        // Fulfill order
        if (isset($paymentIntent->metadata->order_id)) {
            Order::where('id', $paymentIntent->metadata->order_id)->update(['status' => 'paid', 'paid_at' => now()]);
        }

        // Send confirmation email, etc.
    }

    private function handlePaymentCapture($paymentIntent)
    {
        Log::info('Payment capture updated: ' . $paymentIntent->id);
        
        // For delayed payments (like manual review)
        Payment::updateOrCreate(
            ['stripe_payment_id' => $paymentIntent->id],
            [
                'status' => 'capturable',
                'amount_capturable' => $paymentIntent->amount_capturable / 100,
            ]
        );
    }

    private function handlePaymentFailure($paymentIntent)
    {
        return response()->json('hello success');

        Log::error('Payment failed: ' . $paymentIntent->id);
        
        $error = $paymentIntent->last_payment_error->message ?? 'Unknown error';

        Payment::updateOrCreate(
            ['stripe_payment_id' => $paymentIntent->id],
            [
                'status' => 'failed',
                'error' => $error,
                'failed_at' => now(),
            ]
        );

        // Notify user of failure
        if (isset($paymentIntent->metadata->user_id)) {
            // Send failure notification
        }
    }

    private function handlePaymentCanceled($paymentIntent)
    {
        Log::info('Payment canceled: ' . $paymentIntent->id);

        Payment::updateOrCreate(
            ['stripe_payment_id' => $paymentIntent->id],
            [
                'status' => 'canceled',
                'canceled_at' => now(),
            ]
        );
    }
}
