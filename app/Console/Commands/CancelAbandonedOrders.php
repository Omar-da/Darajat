<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Jobs\ProcessCancelAbandonedOrders;
use App\Models\Order;
use Illuminate\Console\Command;

class CancelAbandonedOrders extends Command
{
    protected $signature = 'orders:cancel-abandoned';
    protected $description = 'Mark orders that have been pending too long as canceled';

    public function handle()
    {
        // 1. Find pending orders older than 24 hours
        $abandonedOrders = Order::where('status', OrderStatusEnum::PENDING)
                                ->where('created_at', '<', now()->subDay()) // Changed to 24 hours
                                ->get();

        foreach ($abandonedOrders as $order) {
            ProcessCancelAbandonedOrders::dispatch($order)->onQueue('cancel_abandoned_orders');
        }
    }
}