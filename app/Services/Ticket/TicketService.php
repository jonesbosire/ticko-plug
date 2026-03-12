<?php

namespace App\Services\Ticket;

use App\Enums\TicketStatus;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Str;

class TicketService
{
    public function __construct(
        private readonly QrCodeService $qrCodeService
    ) {}

    /**
     * Generate all tickets for a paid order.
     */
    public function generateForOrder(Order $order): array
    {
        $tickets = [];

        foreach ($order->items as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $ticketNumber = $this->generateTicketNumber();

                // Create placeholder to get the ID for HMAC
                $ticket = Ticket::create([
                    'order_id'       => $order->id,
                    'order_item_id'  => $item->id,
                    'event_id'       => $order->event_id,
                    'ticket_type_id' => $item->ticket_type_id,
                    'user_id'        => $order->user_id,
                    'ticket_number'  => $ticketNumber,
                    'qr_code_secret' => Str::random(64), // temp, will be replaced
                    'attendee_name'  => $order->buyer_name,
                    'attendee_email' => $order->buyer_email,
                    'attendee_phone' => $order->buyer_phone,
                    'status'         => TicketStatus::Active,
                ]);

                // Set the real HMAC-signed secret
                $secret = $this->qrCodeService->generateSecret($ticket->id, $ticketNumber, $order->event_id);
                $ticket->update(['qr_code_secret' => $secret]);

                $tickets[] = $ticket->fresh();
            }
        }

        return $tickets;
    }

    public function generateTicketNumber(): string
    {
        do {
            $number = 'TK-' . strtoupper(Str::random(8));
        } while (Ticket::where('ticket_number', $number)->exists());

        return $number;
    }
}
