<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpirePendingOrders extends Command
{
    protected $signature   = 'orders:expire-pending';
    protected $description = 'Cancel unpaid orders past their expiry and release reserved inventory';

    public function handle(): int
    {
        $expired = Order::query()
            ->whereIn('status', [OrderStatus::Pending->value, OrderStatus::Processing->value])
            ->where('expires_at', '<', now())
            ->with('items')
            ->get();

        if ($expired->isEmpty()) {
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($expired as $order) {
            DB::transaction(function () use ($order, &$count) {
                // Release reserved inventory for each line item
                foreach ($order->items as $item) {
                    TicketType::where('id', $item->ticket_type_id)
                        ->where('quantity_reserved', '>=', $item->quantity)
                        ->decrement('quantity_reserved', $item->quantity);
                }

                $order->status = OrderStatus::Cancelled;
                $order->save();

                $count++;
            });
        }

        Log::info("orders:expire-pending — cancelled {$count} order(s).");
        $this->info("Cancelled {$count} expired order(s).");

        return self::SUCCESS;
    }
}
