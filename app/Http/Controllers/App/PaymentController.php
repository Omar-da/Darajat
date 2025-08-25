<?php

namespace App\Http\Controllers\App;

use App\Enums\CourseStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::find($request->course_id);
        if ($course->status !== CourseStatusEnum::APPROVED)
            return ['message' => __('msg.can_not_enroll_in_course') . $course->status->label() . __('msg.status'), 'code' => 403];

        $amount = $course->price * 100;

        // 1. CREATE THE ORDER RECORD FIRST
        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $amount,
            'currency' => 'usd',
            'status' => OrderStatusEnum::PENDING,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)), // Generate your number
            'course_name' => $course->title
        ]);

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        try {
            // 2. CREATE THE STRIPE PAYMENT INTENT
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ],
            ]);

            // 3. UPDATE THE ORDER WITH STRIPE'S PAYMENT_INTENT_ID
            $order->update(['payment_intent_id' => $paymentIntent->id]);

            // 4. RETURN THE CLIENT SECRET TO FLUTTER
            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'orderNumber' => $order->order_number, // Optional: for display in the UI
            ]);

        } catch (\Exception $e) {
            // If Stripe fails, update the order status to reflect the error
            $order->update(['status' => OrderStatusEnum::FAILED, 'notes' => $e->getMessage()]);

            return response()->json(['error' => 'Payment setup failed'], 500);
        }
    }

    public function cancelProcess(Order $order)
    {
        if ($order->user_id !== auth()->id() || $order->status !== OrderStatusEnum::PENDING)
            return response()->json(['error' => 'Cannot cancel this order'], 400);

        $order->update(['status' => OrderStatusEnum::CANCELED]);

        return response()->json(['message' => 'Order canceled']);
    }
}
