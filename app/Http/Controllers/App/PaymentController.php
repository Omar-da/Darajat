<?php

namespace App\Http\Controllers\App;

use App\Enums\CourseStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class PaymentController
{
    public function createPaymentIntent(Request $request)
    {
        $student = auth('api')->user();

        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::find($request->course_id);
        if ($course->status !== CourseStatusEnum::APPROVED)
            return ['message' => __('msg.can_not_enroll_in_course') . $course->status->label() . __('msg.status'), 'code' => 403];
        $amount = $course->price * 100;

        // 1. FIND THE EXISTING ORDER (if any)
        $existingOrder = Order::where([
            'course_id' => $course->id,
            'student_id' => $student->id
        ])->first();

        // 2. IF AN ORDER EXISTS, CHECK ITS STATUS ON STRIPE
        if ($existingOrder) {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            
            // Retrieve the LIVE status from Stripe
            $existingPaymentIntent = $stripe->paymentIntents->retrieve($existingOrder->payment_intent_id);
            
            // Analyze the status
            $intentStatus = $existingPaymentIntent->status;
            
            if ($intentStatus == 'requires_payment_method') {
                // Scenario 1: Previous attempt was abandoned.
                // Create a NEW Payment Intent for the EXISTING order.
                $newPaymentIntent = $stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'order_id' => $existingOrder->id,
                    'order_number' => $existingOrder->order_number,
                    'student_id' => $student->id,
                    'teacher_id' => $course->teacher_id,
                    'course_id' => $course->id,
                ],
            ]);
                $existingOrder->update(['payment_intent_id' => $newPaymentIntent->id]);
                return response()->json(['clientSecret' => $newPaymentIntent->client_secret]);
            }
            elseif (in_array($intentStatus, ['requires_action', 'requires_confirmation', 'processing'])) {
                // Scenario 2: Payment is in progress. Return the existing secret.
                return response()->json(['clientSecret' => $existingPaymentIntent->client_secret]);
            }
            elseif ($intentStatus == 'succeeded') {
                // Payment already completed. Fulfill the order and return an error.
                $existingOrder->update(['status' => OrderStatusEnum::PAID]);
                return ['message' => 'This order has already been paid.', 'code' => 409];
            }
        }

        // 3. IF NO ORDER EXISTS, OR EXISTING ORDER IS INVALID, CREATE A NEW ONE
        // YOUR ORIGINAL CODE FOR CREATING A NEW ORDER AND PAYMENT INTENT GOES HERE
        $order = Order::create([
            'student_id' => $student->id,
            'teacher_id' => $course->teacher_id,
            'course_id' => $course->id,
            'amount' => $amount,
            'currency' => 'usd',
            'status' => OrderStatusEnum::PENDING,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)), // Generate your number
            'course_name' => $course->title
        ]);

        $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'student_id' => $student->id,
                    'teacher_id' => $course->teacher_id,
                    'course_id' => $course->id,
                ],
            ]);

        $order->update(['payment_intent_id' => $paymentIntent->id]);
        
        return response()->json(['clientSecret' => $paymentIntent->client_secret]);
    }

    public function cancelProcess(Order $order)
    {
        if ($order->student_id !== auth('api')->id() || $order->status !== OrderStatusEnum::PENDING)
        return response()->json(['error' => 'Cannot cancel this order'], 400);

        $order->update(['status' => OrderStatusEnum::CANCELED]);

        return response()->json(['message' => 'Order canceled']);
    }
    
    public function getHistoryForStudent()
    {
        $history = Order::where('student_id', auth('api')->id())
            ->orderBy('purchase_date', 'desc')
            ->get();

        return response()->json(['history' => $history]);
    }
    
    public function getHistoryForTeacher()
    {
        $history = Order::where('teacher_id', auth('api')->id())
            ->orderBy('purchase_date', 'desc')
            ->get();

        return response()->json(['history' => $history]);
    }
}
