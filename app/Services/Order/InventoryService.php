<?php

namespace App\Services\Order;

use App\Models\TicketType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Reserve tickets (hold during checkout session).
     * Returns true on success, throws on failure.
     */
    public function reserve(int $ticketTypeId, int $quantity): bool
    {
        $updated = DB::table('ticket_types')
            ->where('id', $ticketTypeId)
            ->whereRaw('(quantity_total - quantity_sold - quantity_reserved) >= ?', [$quantity])
            ->update(['quantity_reserved' => DB::raw("quantity_reserved + {$quantity}")]);

        if (! $updated) {
            throw new \RuntimeException("Not enough tickets available for ticket type #{$ticketTypeId}.");
        }

        return true;
    }

    /**
     * Release reserved tickets (cart expired or checkout abandoned).
     */
    public function release(int $ticketTypeId, int $quantity): void
    {
        DB::table('ticket_types')
            ->where('id', $ticketTypeId)
            ->update(['quantity_reserved' => DB::raw("GREATEST(0, quantity_reserved - {$quantity})")]);
    }

    /**
     * Confirm sale (move from reserved → sold).
     */
    public function confirm(int $ticketTypeId, int $quantity): void
    {
        DB::table('ticket_types')
            ->where('id', $ticketTypeId)
            ->update([
                'quantity_sold'     => DB::raw("quantity_sold + {$quantity}"),
                'quantity_reserved' => DB::raw("GREATEST(0, quantity_reserved - {$quantity})"),
            ]);

        // Update event denormalized counter
        $ticketType = TicketType::find($ticketTypeId);
        if ($ticketType) {
            DB::table('events')
                ->where('id', $ticketType->event_id)
                ->update(['total_tickets_sold' => DB::raw("total_tickets_sold + {$quantity}")]);
        }
    }

    /**
     * Revert a confirmed sale (refund).
     */
    public function revert(int $ticketTypeId, int $quantity): void
    {
        DB::table('ticket_types')
            ->where('id', $ticketTypeId)
            ->update(['quantity_sold' => DB::raw("GREATEST(0, quantity_sold - {$quantity})")]);

        $ticketType = TicketType::find($ticketTypeId);
        if ($ticketType) {
            DB::table('events')
                ->where('id', $ticketType->event_id)
                ->update(['total_tickets_sold' => DB::raw("GREATEST(0, total_tickets_sold - {$quantity})")]);
        }
    }

    public function checkAvailability(int $ticketTypeId, int $quantity): bool
    {
        $ticketType = TicketType::find($ticketTypeId);
        if (! $ticketType) {
            return false;
        }

        return $ticketType->available_quantity >= $quantity;
    }
}
