<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessTicketsAfterPayment;
use App\Models\Event;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Services\Payment\FlutterwaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlutterwaveController extends Controller
{
    public function __construct(
        private readonly FlutterwaveService $flutterwave,
        private readonly OrderService $orderService,
    ) {}

    /**
     * Initiate card payment — redirects to Flutterwave hosted checkout.
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

        $items = collect($cart)->map(fn ($c) => [
            'ticket_type_id' => $c['type_id'],
            'quantity'       => $c['qty'],
        ])->values()->toArray();

        try {
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
                paymentMethod: 'card',
            );

            $result = $this->flutterwave->initiatePayment([
                'order_number' => $order->order_number,
                'amount'       => $order->total,
                'buyer_name'   => $order->buyer_name,
                'buyer_email'  => $order->buyer_email,
                'buyer_phone'  => $order->buyer_phone,
                'event_title'  => $event->title,
            ]);

            $this->clearCheckoutSession();

            // Redirect to Flutterwave hosted page
            return redirect()->away($result['data']['link']);

        } catch (\RuntimeException $e) {
            Log::error('Flutterwave initiation error', ['error' => $e->getMessage()]);

            return back()->withErrors(['payment' => 'Card payment could not be initiated. Please try again or use M-Pesa.']);
        }
    }

    /**
     * Flutterwave redirect callback (after card payment attempt).
     * Flutterwave redirects back here with tx_ref and transaction_id.
     */
    public function callback(Request $request): RedirectResponse
    {
        $status        = $request->get('status');
        $txRef         = $request->get('tx_ref');
        $transactionId = $request->get('transaction_id');

        $order = Order::where('order_number', $txRef)->first();

        if (! $order) {
            return redirect()->route('home')->withErrors(['payment' => 'Order not found.']);
        }

        if ($status !== 'successful') {
            $order->update(['status' => \App\Enums\OrderStatus::Failed]);

            return redirect()->route('checkout.payment', $order->event)
                ->withErrors(['payment' => 'Card payment was not completed. Please try again.']);
        }

        // Verify with Flutterwave API
        try {
            $verification = $this->flutterwave->verifyTransaction($transactionId);
            $data = $verification['data'] ?? null;

            if (
                ! $data ||
                $data['status'] !== 'successful' ||
                $data['tx_ref'] !== $txRef ||
                (float) $data['amount'] < (float) $order->total ||
                $data['currency'] !== 'KES'
            ) {
                $order->update(['status' => \App\Enums\OrderStatus::Failed]);
                Log::warning('Flutterwave verification mismatch', ['order' => $txRef, 'data' => $data]);

                return redirect()->route('home')
                    ->withErrors(['payment' => 'Payment verification failed. Contact support with ref: ' . $txRef]);
            }

            if ($order->status->value !== 'paid') {
                DB::transaction(function () use ($order, $transactionId, $txRef) {
                    $this->orderService->markAsPaid($order, $transactionId, $txRef);
                });

                ProcessTicketsAfterPayment::dispatch($order->fresh());
            }

            return redirect()->route('orders.confirmation', $order);

        } catch (\Throwable $e) {
            Log::error('Flutterwave callback error', ['error' => $e->getMessage(), 'tx_ref' => $txRef]);

            return redirect()->route('home')
                ->withErrors(['payment' => 'Payment processing error. Please contact support.']);
        }
    }

    /**
     * Flutterwave webhook — server-to-server notification.
     */
    public function webhook(Request $request): JsonResponse
    {
        $signature = $request->header('verif-hash', '');

        if (! $this->flutterwave->validateWebhookSignature($request->getContent(), $signature)) {
            Log::warning('Invalid Flutterwave webhook signature');

            return response()->json(['status' => 'rejected'], 401);
        }

        $event  = $request->json('event');
        $data   = $request->json('data');
        $txRef  = $data['tx_ref'] ?? null;

        Log::info('Flutterwave webhook received', ['event' => $event, 'tx_ref' => $txRef]);

        if ($event === 'charge.completed' && $data['status'] === 'successful' && $txRef) {
            $order = Order::where('order_number', $txRef)->first();

            if ($order && $order->status->value !== 'paid') {
                DB::transaction(function () use ($order, $data, $txRef) {
                    $this->orderService->markAsPaid($order, $data['id'], $txRef);
                });

                ProcessTicketsAfterPayment::dispatch($order->fresh());
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function clearCheckoutSession(): void
    {
        session()->forget(['checkout_event_id', 'checkout_cart_key', 'checkout_buyer', 'checkout_promo_id']);
    }
}
