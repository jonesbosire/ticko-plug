<?php

namespace App\Services\Ticket;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

class QrCodeService
{
    /**
     * Generate a HMAC-signed secret for the QR code.
     * Never expose ticket_id — only the secret.
     */
    public function generateSecret(int $ticketId, string $ticketNumber, int $eventId): string
    {
        return hash_hmac(
            'sha256',
            implode('|', [$ticketId, $ticketNumber, $eventId]),
            config('app.key')
        );
    }

    /**
     * Verify a QR code secret belongs to a ticket for this event.
     */
    public function verify(string $secret, int $eventId): ?Ticket
    {
        $ticket = Ticket::where('qr_code_secret', $secret)
            ->where('event_id', $eventId)
            ->first();

        if (! $ticket) {
            return null;
        }

        // Recompute to ensure integrity (defends against DB tampering)
        $expected = $this->generateSecret($ticket->id, $ticket->ticket_number, $ticket->event_id);
        if (! hash_equals($expected, $secret)) {
            Log::warning('QR secret integrity check failed', ['ticket_id' => $ticket->id]);

            return null;
        }

        return $ticket;
    }

    /**
     * Generate QR code image (SVG or PNG) for embedding in ticket PDF.
     */
    public function generateQrImage(string $secret, int $size = 200): string
    {
        return \QrCode::size($size)->generate($secret);
    }
}
