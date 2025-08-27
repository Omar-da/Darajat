<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class CancelAbandonedOrders extends Command
{
    protected $signature = 'orders:cancel-abandoned';
    protected $description = 'Mark orders that have been pending too long as canceled';

    public function handle()
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
    
        // 1. Find pending orders older than 24 hours
        $abandonedOrders = Order::where('status', OrderStatusEnum::PENDING)
                                ->where('created_at', '<', now()->subDay()) // Changed to 24 hours
                                ->get();

        $canceledCount = 0;

        foreach ($abandonedOrders as $order) {
            try {
                // 2. Check if the order has a Stripe Payment Intent ID
                if ($order->payment_intent_id) {
                    // 3. Retrieve the Payment Intent from Stripe to get its live status
                    $paymentIntent = $stripe->paymentIntents->retrieve($order->payment_intent_id);
                    
                    // 4. Only cancel if it's still in a cancelable state
                    if ($paymentIntent->status === 'requires_payment_method') {
                        // 5. Cancel the Payment Intent on Stripe
                        $stripe->paymentIntents->cancel($order->payment_intent_id);
                        Log::info("Stripe Payment Intent #{$order->payment_intent_id} was canceled.");
                    }
                }

                // 6. Update your local database
                $order->update(['status' => OrderStatusEnum::CANCELED]);
                $canceledCount++;
                Log::info("Order #{$order->id} was marked as canceled due to abandonment.");

            } catch (\Exception $e) {
                // Log any errors (e.g., Payment Intent already canceled or doesn't exist)
                Log::error("Failed to cancel order #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully marked {$canceledCount} orders as canceled.");
    }
}