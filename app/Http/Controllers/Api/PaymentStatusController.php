<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;

class PaymentStatusController extends Controller
{
    public function status(string $orderNumber): \Illuminate\Http\JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->select('order_number', 'status', 'paid_at')
            ->firstOrFail();

        return response()->json([
            'order_number' => $order->order_number,
            'status'       => $order->status->value,
            'paid_at'      => $order->paid_at?->toISOString(),
        ]);
    }
}
