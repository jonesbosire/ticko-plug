@extends('layouts.app')
@section('title', 'You\'re In! — ' . $order->event->title)

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12">

    {{-- ═══════════════════════════════════
         SUCCESS HEADER
    ═══════════════════════════════════ --}}
    <div class="text-center mb-10 ticket-reveal">
        {{-- Confetti emoji row --}}
        <div class="text-3xl mb-4 space-x-1 select-none">🎉 🎟️ 🔌 🎶 🎉</div>

        <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto mb-5"
             style="background:rgba(34,197,94,0.2); border:2px solid rgba(34,197,94,0.5)">✅</div>

        <h1 class="section-heading text-4xl mb-3">
            You're <span class="gradient-text">In!</span>
        </h1>
        <p class="text-lg mb-1" style="color:var(--color-brand-muted)">
            {{ $order->event->title }}
        </p>
        <p class="text-sm" style="color:var(--color-brand-subtle)">
            📅 {{ $order->event->start_datetime->format('D, M j, Y · g:i A') }}
        </p>
    </div>

    {{-- ═══════════════════════════════════
         TICKET(S)
    ═══════════════════════════════════ --}}
    <div class="space-y-4 mb-8">
        @foreach ($order->tickets as $ticket)
        <div class="ticket-reveal overflow-hidden rounded-2xl"
             style="background:var(--color-brand-surface); border:1.5px solid rgba(124,58,237,0.4)">

            {{-- Top strip --}}
            <div class="px-6 pt-5 pb-4 border-b" style="border-color:rgba(124,58,237,0.2); background:linear-gradient(135deg, rgba(124,58,237,0.1), transparent)">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="ticket-badge mb-2">{{ $ticket->ticketType->name }}</p>
                        <h2 class="font-bold text-base">{{ $order->event->title }}</h2>
                        <p class="text-sm mt-1" style="color:var(--color-brand-muted)">{{ $order->event->start_datetime->format('D, M j, Y · g:i A') }}</p>
                        @if ($order->event->venue)
                            <p class="text-sm" style="color:var(--color-brand-muted)">📍 {{ $order->event->venue->name }}, {{ $order->event->venue->city }}</p>
                        @endif
                    </div>
                    {{-- QR Code --}}
                    <div class="shrink-0 p-2 rounded-xl" style="background:white">
                        {!! SimpleSoftware\QrCode\Facades\QrCode::format('svg')
                            ->size(90)
                            ->generate($ticket->qr_code_secret) !!}
                    </div>
                </div>
            </div>

            {{-- Bottom strip --}}
            <div class="px-6 py-4">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <p class="text-xs mb-0.5" style="color:var(--color-brand-muted)">Ticket #</p>
                        <p class="font-mono font-bold text-sm tracking-widest" style="color:var(--color-brand-accent)">{{ $ticket->ticket_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs mb-0.5" style="color:var(--color-brand-muted)">Attendee</p>
                        <p class="font-semibold text-sm">{{ $ticket->attendee_name }}</p>
                    </div>
                    <a href="{{ route('orders.ticket.download', [$order, $ticket]) }}"
                       class="btn-ghost text-xs py-2 px-4">
                        ⬇️ Download PDF
                    </a>
                </div>
            </div>

            {{-- Tear perforations --}}
            <div class="relative" style="height:1px; background:repeating-linear-gradient(90deg, transparent, transparent 8px, rgba(42,42,64,0.8) 8px, rgba(42,42,64,0.8) 10px)"></div>
        </div>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════
         ORDER DETAILS
    ═══════════════════════════════════ --}}
    <div class="p-5 rounded-2xl mb-6" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
        <h3 class="font-semibold mb-4 text-sm uppercase tracking-wider" style="color:var(--color-brand-muted)">Order Details</h3>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span style="color:var(--color-brand-muted)">Order number</span>
                <span class="font-mono font-bold">{{ $order->order_number }}</span>
            </div>
            @if ($order->mpesa_receipt_number)
            <div class="flex justify-between">
                <span style="color:var(--color-brand-muted)">M-Pesa receipt</span>
                <span class="font-mono">{{ $order->mpesa_receipt_number }}</span>
            </div>
            @endif
            <div class="flex justify-between">
                <span style="color:var(--color-brand-muted)">Amount paid</span>
                <span class="font-bold" style="color:var(--color-brand-success)">KES {{ number_format($order->total) }}</span>
            </div>
            <div class="flex justify-between">
                <span style="color:var(--color-brand-muted)">Tickets sent to</span>
                <span>{{ $order->buyer_email }}</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════
         NOTIFICATION REMINDER
    ═══════════════════════════════════ --}}
    <div class="p-4 rounded-xl mb-8 flex items-start gap-3"
         style="background:rgba(37,211,102,0.08); border:1px solid rgba(37,211,102,0.25)">
        <span class="text-xl shrink-0">📲</span>
        <div>
            <p class="font-semibold text-sm mb-0.5">Your ticket has been sent!</p>
            <p class="text-xs leading-relaxed" style="color:var(--color-brand-muted)">
                Check your <strong class="text-white">WhatsApp</strong> ({{ $order->buyer_phone }}) and email ({{ $order->buyer_email }}) for your ticket confirmation. You can also reply <strong class="text-white">TICKET</strong> on WhatsApp to get your QR code.
            </p>
        </div>
    </div>

    {{-- ═══════════════════════════════════
         ACTIONS
    ═══════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('orders.show', $order) }}" class="btn-ghost flex-1 justify-center text-sm">
            View Order
        </a>
        <a href="{{ route('events.show', $order->event) }}" class="btn-ghost flex-1 justify-center text-sm">
            Back to Event
        </a>
        <a href="{{ route('events.index') }}" class="btn-primary flex-1 justify-center text-sm">
            Discover More Events
        </a>
    </div>

</div>
@endsection
