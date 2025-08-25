<?php

namespace App\Http\Controllers\App;

use App\Enums\OrderStatusEnum;
use App\Mail\PaymentNotification;
use App\Mail\SendOTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Illuminate\Support\Str;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

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
            Log::error('Webhook invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Webhook invalid signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle only the events we care about
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentFailure($event->data->object);
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
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();

        if (!$order) {
            Log::error('Webhook: Order not found for PaymentIntent: ' . $paymentIntent->id);
            return;
        }

        if ($order->status === OrderStatusEnum::PAID) {
            Log::info('Webhook: Ignoring duplicate succeeded webhook for order #' . $order->id);
            return;
        }

        if ($order->status === OrderStatusEnum::CANCELED) {
            Log::warning('Webhook: Payment succeeded for CANCELED order #' . $order->id . '. Access was NOT granted. Manual review may be needed.');
            return;
        }

        $order->update([
            'status' => OrderStatusEnum::PAID,
        ]);
        $course = $order->course;
        $user = $order->user;

        $user->followed_courses()->attach($course);
        $course->increment('num_of_students_enrolled');

        Mail::to($user->email)->send(new PaymentNotification(true, $course->title));
    }

    private function handlePaymentFailure($paymentIntent)
    {
        Log::error('Payment failed: ' . $paymentIntent->id);

        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();

        if ($order)
            $order->update(['status' => OrderStatusEnum::FAILED]);

        $user = $order->user;
        $course = $order->course;

        if (isset($user))
            Mail::to($user->email)->send(new PaymentNotification(false, $course->title));
    }
}
