<?php

namespace App\Services\Ticket;

use App\Enums\TicketStatus;
use App\Models\CheckInSession;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    public function __construct(
        private readonly QrCodeService $qrCodeService
    ) {}

    /**
     * Scan and validate a QR code secret.
     *
     * Returns ['success' => bool, 'message' => string, 'ticket' => Ticket|null]
     */
    public function scan(string $secret, int $eventId, ?int $scannedById = null, ?string $deviceName = null): array
    {
        $ticket = $this->qrCodeService->verify($secret, $eventId);

        if (! $ticket) {
            return $this->result(false, '❌ Invalid ticket. QR code not recognised.', null);
        }

        return match ($ticket->status) {
            TicketStatus::Used => $this->result(
                false,
                "⚠️ Already checked in at " . $ticket->checked_in_at?->format('H:i') . " on " . $ticket->checked_in_at?->format('d M Y') . ".",
                $ticket
            ),
            TicketStatus::Cancelled => $this->result(false, '❌ This ticket has been cancelled.', $ticket),
            TicketStatus::Refunded  => $this->result(false, '❌ This ticket was refunded.', $ticket),
            TicketStatus::Active    => $this->checkIn($ticket, $scannedById, $deviceName),
            default                 => $this->result(false, '❌ Ticket status is invalid.', $ticket),
        };
    }

    private function checkIn(Ticket $ticket, ?int $scannedById, ?string $deviceName): array
    {
        // Optional: enforce check-in window
        $event = $ticket->event;
        $doorsOpen = $event->doors_open_at ?? $event->start_datetime;
        $windowHours = config('tickoplug.checkin_window_hours', 2);

        if (now()->lt($doorsOpen->subHours($windowHours))) {
            return $this->result(false, "⏰ Check-in opens " . $doorsOpen->subHours($windowHours)->format('H:i') . " on event day.", $ticket);
        }

        DB::transaction(function () use ($ticket, $scannedById, $deviceName) {
            $ticket->update([
                'status'          => TicketStatus::Used,
                'checked_in_at'   => now(),
                'checked_in_by'   => $scannedById,
                'check_in_device' => $deviceName,
            ]);

            // Increment session counter
            CheckInSession::where('event_id', $ticket->event_id)
                ->where('is_active', true)
                ->increment('total_checked_in');
        });

        return $this->result(
            true,
            "✅ Welcome, {$ticket->attendee_name}! ({$ticket->ticketType->name})",
            $ticket->fresh()
        );
    }

    private function result(bool $success, string $message, ?Ticket $ticket): array
    {
        return compact('success', 'message', 'ticket');
    }
}
