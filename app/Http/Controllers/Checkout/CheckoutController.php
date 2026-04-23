<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\PromoCode;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function details(Event $event): View|RedirectResponse
    {
        $cartKey = session('checkout_cart_key');
        $cart = $cartKey ? Cache::get($cartKey) : null;

        if (! $cart) {
            return redirect()->route('checkout.select', $event)
                ->withErrors(['cart' => 'Your reservation has expired. Please select your tickets again.']);
        }

        $types = TicketType::whereIn('id', collect($cart)->pluck('type_id'))->get()->keyBy('id');
        $lineItems = collect($cart)->map(fn ($item) => [
            'type'     => $types[$item['type_id']],
            'qty'      => $item['qty'],
            'subtotal' => $types[$item['type_id']]->price * $item['qty'],
        ]);

        $subtotal      = $lineItems->sum('subtotal');
        $platformFee   = $event->organizer_absorbs_fee ? 0 : round($subtotal * (config('tickoplug.platform_fee_percentage', 5) / 100), 2);
        $total         = $subtotal + $platformFee;

        return view('checkout.details', compact('event', 'lineItems', 'subtotal', 'platformFee', 'total'));
    }

    public function storeDetails(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'buyer_name'  => ['required', 'string', 'max:100'],
            'buyer_email' => ['required', 'email', 'max:150'],
            'buyer_phone' => ['required', 'string', 'regex:/^(\+?254|0)[17]\d{8}$/'],
            'promo_code'  => ['nullable', 'string', 'max:50'],
        ], [
            'buyer_phone.regex' => 'Please enter a valid Kenyan phone number (e.g. 0712345678).',
        ]);

        // Validate promo code if provided
        if ($request->filled('promo_code')) {
            $promo = PromoCode::where('code', strtoupper($request->promo_code))
                ->where('event_id', $event->id)
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                ->first();

            if (! $promo) {
                return back()->withErrors(['promo_code' => 'Invalid or expired promo code.'])->withInput();
            }

            if ($promo->usage_limit && $promo->times_used >= $promo->usage_limit) {
                return back()->withErrors(['promo_code' => 'This promo code has reached its usage limit.'])->withInput();
            }

            session(['checkout_promo_id' => $promo->id]);
        } else {
            session()->forget('checkout_promo_id');
        }

        session(['checkout_buyer' => $validated]);

        return redirect()->route('checkout.payment', $event);
    }

    public function payment(Event $event): View|RedirectResponse
    {
        $cartKey = session('checkout_cart_key');
        $cart    = $cartKey ? Cache::get($cartKey) : null;
        $buyer   = session('checkout_buyer');

        if (! $cart || ! $buyer) {
            return redirect()->route('checkout.select', $event);
        }

        $types     = TicketType::whereIn('id', collect($cart)->pluck('type_id'))->get()->keyBy('id');
        $lineItems = collect($cart)->map(fn ($item) => [
            'type'     => $types[$item['type_id']],
            'qty'      => $item['qty'],
            'subtotal' => $types[$item['type_id']]->price * $item['qty'],
        ]);

        $subtotal    = $lineItems->sum('subtotal');
        $promoId     = session('checkout_promo_id');
        $promo       = $promoId ? PromoCode::find($promoId) : null;
        $discount    = $promo ? $this->orderService->calculateDiscount($promo, $subtotal) : 0;
        $afterDiscount = $subtotal - $discount;
        $platformFee = $event->organizer_absorbs_fee ? 0 : round($afterDiscount * (config('tickoplug.platform_fee_percentage', 5) / 100), 2);
        $total       = $afterDiscount + $platformFee;

        // Normalise phone for STK push display
        $phone = $buyer['buyer_phone'];
        $displayPhone = preg_replace('/^0/', '+254', $phone);

        return view('checkout.payment', compact(
            'event', 'lineItems', 'subtotal', 'discount', 'promo',
            'platformFee', 'total', 'buyer', 'displayPhone'
        ));
    }

    public function processing(Order $order): View|RedirectResponse
    {
        abort_if($order->status->value === 'paid', 302, route('orders.confirmation', $order));

        return view('checkout.processing', compact('order'));
    }
}
