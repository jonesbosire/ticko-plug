<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessTicketsAfterPayment;
use App\Models\Event;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Services\Payment\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    public function __construct(
        private readonly MpesaService $mpesa,
        private readonly OrderService $orderService,
    ) {}

    /**
     * Initiate STK push — called from checkout/payment view.
     */
    public function initiate(Request $request, Event $event): RedirectResponse
    {
        $cartKey = session('checkout_cart_key');
        $cart    = $cartKey ? Cache::get($cartKey) : null;
        $buyer   = session('checkout_buyer');

        if (! $cart || ! $buyer) {
            return redirect()->route('checkout.select', $event)
                ->withErrors(['cart' => 'Your session expired. Please start again.']);
        }

        // Build items array for OrderService
        $items = collect($cart)->map(fn ($c) => [
            'ticket_type_id' => $c['type_id'],
            'quantity'       => $c['qty'],
        ])->values()->toArray();

        try {
            // Create pending order
            $order = $this->orderService->createOrder(
                event: $event,
                items: $items,
                buyer: [
                    'name'  => $buyer['buyer_name'],
                    'email' => $buyer['buyer_email'],
                    'phone' => $buyer['buyer_phone'],
                ],
                promoCode: session('checkout_promo_id')
                    ? \App\Models\PromoCode::find(session('checkout_promo_id'))?->code
                    : null,
                paymentMethod: 'mpesa',
            );

            // If free ticket, skip STK push
            if ($order->total == 0) {
                $this->orderService->markAsPaid($order, 'FREE-' . $order->order_number, 'free');
                ProcessTicketsAfterPayment::dispatch($order);
                $this->clearCheckoutSession();

                return redirect()->route('orders.confirmation', $order);
            }

            // Initiate STK push
            $stkResponse = $this->mpesa->initiateStkPush(
                phone: $buyer['buyer_phone'],
                amount: $order->total,
                orderNumber: $order->order_number,
                description: 'Ticko-Plug Tickets',
            );

            // Store checkout request ID for callback matching
            $order->update([
                'payment_reference' => $stkResponse['CheckoutRequestID'],
            ]);

            Cache::put(
                'mpesa_checkout_' . $stkResponse['CheckoutRequestID'],
                $order->order_number,
                now()->addMinutes(10)
            );

            $this->clearCheckoutSession();

            return redirect()->route('checkout.processing', $order);

        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            Log::error('M-Pesa initiation error', ['error' => $e->getMessage()]);

            return back()->withErrors(['payment' => 'Could not initiate M-Pesa payment. Please try again.']);
        }
    }

    /**
     * STK Push callback — called by Safaricom servers.
     * MUST return 200 immediately regardless of outcome.
     */
    public function stkCallback(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('M-Pesa STK Callback received', $payload);

        try {
            $body       = $payload['Body']['stkCallback'] ?? null;
            $resultCode = $body['ResultCode'] ?? null;
            $checkoutId = $body['CheckoutRequestID'] ?? null;

            if (! $checkoutId) {
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            }

            // Find the order via checkout ID
            $orderNumber = Cache::get('mpesa_checkout_' . $checkoutId);
            if (! $orderNumber) {
                Log::warning('M-Pesa callback: order not found for checkout ID', ['checkout_id' => $checkoutId]);

                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            }

            $order = Order::where('order_number', $orderNumber)->first();
            if (! $order) {
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            }

            // Idempotency — already processed
            if ($order->status->value === 'paid') {
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            }

            if ($resultCode === 0) {
                // Payment successful — extract metadata
                $metadata = collect($body['CallbackMetadata']['Item'] ?? [])
                    ->keyBy('Name')
                    ->map(fn ($item) => $item['Value'] ?? null);

                $mpesaReceipt = $metadata->get('MpesaReceiptNumber', 'UNKNOWN');
                $transactionId = $metadata->get('TransactionDate', now()->format('YmdHis'));

                DB::transaction(function () use ($order, $mpesaReceipt, $checkoutId) {
                    $this->orderService->markAsPaid($order, $mpesaReceipt, $checkoutId);
                });

                // Dispatch ticket generation + notifications async
                ProcessTicketsAfterPayment::dispatch($order->fresh());

                Cache::forget('mpesa_checkout_' . $checkoutId);

            } else {
                // Payment failed
                $order->update(['status' => \App\Enums\OrderStatus::Failed]);

                // Release reserved inventory
                foreach ($order->items as $item) {
                    app(\App\Services\Order\InventoryService::class)->release(
                        $item->ticket_type_id,
                        $item->quantity
                    );
                }

                Log::info('M-Pesa payment failed', [
                    'order'       => $orderNumber,
                    'result_code' => $resultCode,
                    'result_desc' => $body['ResultDesc'] ?? 'Unknown',
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('M-Pesa callback processing error', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
        }

        // Always return 200 to Safaricom
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    /**
     * C2B Validation — Safaricom asks us to validate the payment.
     */
    public function c2bValidate(Request $request): JsonResponse
    {
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * C2B Confirmation — Safaricom confirms completed payment.
     */
    public function c2bConfirm(Request $request): JsonResponse
    {
        Log::info('M-Pesa C2B Confirmation', $request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    private function clearCheckoutSession(): void
    {
        session()->forget(['checkout_event_id', 'checkout_cart_key', 'checkout_buyer', 'checkout_promo_id']);
    }
}
