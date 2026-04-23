<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎟️ Your Tickets — ' . $this->order->event->title . ' | Ticko-Plug',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-confirmation',
        );
    }
}
