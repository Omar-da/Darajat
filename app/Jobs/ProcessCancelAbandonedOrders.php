<?php

namespace App\Jobs;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class ProcessCancelAbandonedOrders implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try 
        {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $order = $this->order;

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
            Log::info("Order #{$order->id} was marked as canceled due to abandonment.");

        } 
        catch (\Exception $e) 
        {
            // Log any errors (e.g., Payment Intent already canceled or doesn't exist)
            Log::error("Failed to cancel order #{$order->id}: " . $e->getMessage());
        }
    }
}
