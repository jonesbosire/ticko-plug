<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly InventoryService $inventory
    ) {}

    /**
     * Create a pending order with reserved inventory.
     *
     * @param Event $event
     * @param array $items  [['ticket_type_id' => 1, 'quantity' => 2], ...]
     * @param array $buyer  ['name' => '', 'email' => '', 'phone' => '']
     * @param string|null $promoCode
     * @param string $paymentMethod
     */
    public function createOrder(
        Event $event,
        array $items,
        array $buyer,
        ?string $promoCode = null,
        string $paymentMethod = 'mpesa'
    ): Order {
        return DB::transaction(function () use ($event, $items, $buyer, $promoCode, $paymentMethod) {
            // Resolve and validate promo code
            $promo = null;
            if ($promoCode) {
                $promo = PromoCode::where('code', strtoupper($promoCode))
                    ->where(fn ($q) => $q->whereNull('event_id')->orWhere('event_id', $event->id))
                    ->first();

                if (! $promo || ! $promo->is_valid) {
                    throw new \InvalidArgumentException('Invalid or expired promo code.');
                }
            }

            // Calculate totals
            $subtotal = 0;
            $lineItems = [];

            foreach ($items as $item) {
                $ticketType = TicketType::findOrFail($item['ticket_type_id']);

                if ($ticketType->event_id !== $event->id) {
                    throw new \InvalidArgumentException("Ticket type does not belong to this event.");
                }

                // Reserve inventory
                $this->inventory->reserve($ticketType->id, $item['quantity']);

                $unitPrice  = $ticketType->price;
                $lineSubtotal = $unitPrice * $item['quantity'];
                $subtotal  += $lineSubtotal;

                $lineItems[] = [
                    'ticket_type_id' => $ticketType->id,
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $unitPrice,
                    'subtotal'       => $lineSubtotal,
                ];
            }

            // Apply promo discount
            $discountAmount = 0;
            if ($promo) {
                if ($promo->min_order_amount && $subtotal < $promo->min_order_amount) {
                    throw new \InvalidArgumentException("Minimum order amount for this promo is KES " . number_format($promo->min_order_amount));
                }
                $discountAmount = $promo->calculateDiscount($subtotal);
                $promo->increment('used_count');
            }

            $afterDiscount = $subtotal - $discountAmount;

            // Platform fee
            $feePercentage    = $event->platform_fee_override ?? config('tickoplug.platform_fee_percentage');
            $feeFixed         = config('tickoplug.platform_fee_fixed');
            $platformFee      = 0;

            if ($afterDiscount > 0) {
                $ticketCount = array_sum(array_column($items, 'quantity'));
                $platformFee = round(($afterDiscount * $feePercentage / 100) + ($feeFixed * $ticketCount), 2);
            }

            $organizerAmount = $afterDiscount - ($event->organizer_absorbs_fee ? $platformFee : 0);
            $total = $afterDiscount + ($event->organizer_absorbs_fee ? 0 : $platformFee);

            // Create order
            $order = Order::create([
                'order_number'     => $this->generateOrderNumber(),
                'event_id'         => $event->id,
                'user_id'          => auth()->id(),
                'promo_code_id'    => $promo?->id,
                'status'           => OrderStatus::Pending,
                'subtotal'         => $subtotal,
                'discount_amount'  => $discountAmount,
                'platform_fee'     => $platformFee,
                'organizer_amount' => $organizerAmount,
                'total'            => $total,
                'currency'         => 'KES',
                'payment_method'   => $paymentMethod,
                'buyer_name'       => $buyer['name'],
                'buyer_email'      => $buyer['email'],
                'buyer_phone'      => $buyer['phone'],
                'expires_at'       => now()->addMinutes(config('tickoplug.cart_expiry_minutes')),
                'ip_address'       => request()->ip(),
                'user_agent'       => request()->userAgent(),
            ]);

            // Create order items
            foreach ($lineItems as $lineItem) {
                $order->items()->create($lineItem);
            }

            return $order->fresh(['items', 'event']);
        });
    }

    public function generateOrderNumber(): string
    {
        do {
            $number = 'TKT-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    public function markAsPaid(Order $order, string $mpesaReceipt, string $paymentReference): void
    {
        $order->update([
            'status'               => OrderStatus::Paid,
            'paid_at'              => now(),
            'mpesa_receipt_number' => $mpesaReceipt,
            'payment_reference'    => $paymentReference,
        ]);

        // Confirm inventory
        foreach ($order->items as $item) {
            $this->inventory->confirm($item->ticket_type_id, $item->quantity);
        }

        // Update event revenue
        DB::table('events')
            ->where('id', $order->event_id)
            ->increment('total_revenue', $order->organizer_amount);
    }
}
