<?php

namespace App\Http\Controllers\App;

use App\Enums\OrderStatusEnum;
use App\Mail\PaymentNotification;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Order;
use App\Models\PlatformStatistics;
use App\Models\User;
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
        $order = Order::with(['course', 'student.followed_courses', 'teacher.moreDetail'])->where('payment_intent_id', $paymentIntent->id)->first();

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
        $student = $order->student;
        $teacher = $order->teacher;
        $price = $course->price;

        $commission = $price * 5 / 100;
        $platform = PlatformStatistics::getStats();
        $platform->commission += $commission;
        $platform->total_profit += $price;
        $platform->save();

        $teacher->moreDetail->balance += $price - $commission;
        $teacher->moreDetail->save();

        $student->followed_courses()->attach($course);
        $course->increment('num_of_students_enrolled');

        Mail::to('omaraldalati3@gmail.com')->send(new PaymentNotification($student, $order));
    }

    private function handlePaymentFailure($paymentIntent)
    {
        Log::error('Payment failed: ' . $paymentIntent->id);

        $order = Order::with('student')->where('payment_intent_id', $paymentIntent->id)->first();

        if ($order)
            $order->update(['status' => OrderStatusEnum::FAILED]);

        $student = $order->student;

        if (isset($student)) 
            Mail::to('omaraldalati3@gmail.com')->send(new PaymentNotification($student, $order));
    }

}
