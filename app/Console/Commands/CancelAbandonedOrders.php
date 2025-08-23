<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelAbandonedOrders extends Command
{
    protected $signature = 'orders:cancel-abandoned';
    protected $description = 'Mark orders that have been pending too long as canceled';

    public function handle()
    {
        // Find orders that have been 'pending' for more than 24 hours
        $abandonedOrders = Order::where('status', OrderStatusEnum::PENDING)
                                ->where('created_at', '<', now()->subHour())
                                ->get();

        foreach ($abandonedOrders as $order) {
            $order->update(['status' => OrderStatusEnum::CANCELED]);
            Log::info("Order #{$order->id} was marked as canceled due to abandonment.");
        }

        $this->info("Marked {$abandonedOrders->count()} orders as canceled.");
    }
}