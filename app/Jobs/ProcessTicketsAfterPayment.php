<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\SmsService;
use App\Services\Ticket\TicketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessTicketsAfterPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public readonly Order $order) {}

    public function handle(TicketService $ticketService, SmsService $sms): void
    {
        $order = $this->order->fresh(['items', 'event.venue', 'orderItems.ticketType']);

        // Guard — only process paid orders
        if ($order->status->value !== 'paid') {
            Log::warning('ProcessTicketsAfterPayment: order not paid', ['order' => $order->order_number]);
            return;
        }

        // Guard — tickets already generated
        if ($order->tickets()->exists()) {
            Log::info('ProcessTicketsAfterPayment: tickets already exist', ['order' => $order->order_number]);
            $this->sendNotifications($order, $sms);
            return;
        }

        try {
            // 1. Generate tickets + QR codes
            $tickets = $ticketService->generateForOrder($order);

            Log::info('Tickets generated', [
                'order'   => $order->order_number,
                'tickets' => count($tickets),
            ]);

            // 2. Send email confirmation + ticket PDFs
            $this->sendEmail($order);

            // 3. Send SMS + WhatsApp notifications
            $this->sendNotifications($order, $sms);

        } catch (\Throwable $e) {
            Log::error('ProcessTicketsAfterPayment failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function sendEmail(Order $order): void
    {
        try {
            Mail::to($order->buyer_email)
                ->send(new \App\Mail\TicketConfirmationMail($order));
        } catch (\Throwable $e) {
            Log::error('Ticket confirmation email failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
            // Don't rethrow — SMS fallback below
        }
    }

    private function sendNotifications(Order $order, SmsService $sms): void
    {
        $event = $order->event;

        // ── SMS confirmation ──────────────────────────────
        try {
            $sms->sendPaymentConfirmed(
                phone: $order->buyer_phone,
                buyerName: $order->buyer_name,
                eventName: $event->title,
                mpesaReceipt: $order->mpesa_receipt_number ?? $order->payment_reference ?? $order->order_number,
            );
        } catch (\Throwable $e) {
            Log::warning('SMS notification failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);
        }

        // ── WhatsApp confirmation ─────────────────────────
        try {
            $firstTicket    = $order->tickets()->with('ticketType')->first();
            $ticketTypeName = $firstTicket?->ticketType?->name ?? 'General';
            $totalQty       = $order->tickets()->count();
            $venueName      = $event->venue?->name ?? ($event->is_online ? 'Online Event' : 'TBA');
            $dateStr        = $event->start_datetime->setTimezone('Africa/Nairobi')->format('D, M j · g:i A');

            $sms->sendWhatsAppTicketConfirmation(
                phone: $order->buyer_phone,
                buyerName: $order->buyer_name,
                eventName: $event->title,
                date: $dateStr,
                venue: $venueName,
                ticketType: $ticketTypeName,
                quantity: $totalQty,
                amount: $order->total,
                mpesaReceipt: $order->mpesa_receipt_number ?? $order->payment_reference ?? $order->order_number,
            );
        } catch (\Throwable $e) {
            Log::warning('WhatsApp notification failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);
        }

        // ── Organizer notification (WhatsApp) ─────────────
        try {
            $organizer = $event->organizer;
            if ($organizer?->phone) {
                $sms->sendOrganizerSaleNotification(
                    phone: $organizer->phone,
                    buyerName: $order->buyer_name,
                    quantity: $order->tickets()->count(),
                    ticketType: $order->tickets()->with('ticketType')->first()?->ticketType?->name ?? 'Ticket',
                    eventName: $event->title,
                    organizerAmount: $order->organizer_amount,
                    totalSold: $event->total_tickets_sold,
                    totalCapacity: $event->ticketTypes()->sum('quantity'),
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Organizer notification failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);
        }
    }
}
