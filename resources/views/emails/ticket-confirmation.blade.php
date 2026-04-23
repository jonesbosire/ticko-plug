<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Tickets — {{ $order->event->title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f8; color: #1a1a2e; }
        .wrapper { max-width: 600px; margin: 32px auto; }
        .header { background: linear-gradient(135deg, #7C3AED 0%, #5B21B6 100%); border-radius: 16px 16px 0 0; padding: 32px 40px; text-align: center; }
        .header h1 { color: white; font-size: 24px; font-weight: 800; letter-spacing: -0.02em; }
        .header p { color: rgba(255,255,255,0.8); font-size: 14px; margin-top: 6px; }
        .logo { font-size: 28px; font-weight: 900; color: white; letter-spacing: -0.03em; margin-bottom: 16px; }
        .logo span { color: #F0C427; }
        .body { background: white; padding: 40px; }
        .greeting { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .subtext { color: #64748b; font-size: 14px; line-height: 1.6; margin-bottom: 28px; }
        .ticket { background: #f8f8ff; border: 1.5px solid #e0d9ff; border-radius: 12px; padding: 24px; margin-bottom: 16px; }
        .ticket-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
        .ticket-badge { background: #7C3AED22; color: #7C3AED; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 3px 10px; border-radius: 999px; border: 1px solid #7C3AED44; }
        .ticket-number { font-family: 'Courier New', monospace; font-weight: 700; font-size: 13px; color: #F0C427; letter-spacing: 0.1em; }
        .event-name { font-size: 17px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
        .event-meta { color: #64748b; font-size: 13px; line-height: 1.6; }
        .qr-placeholder { text-align: center; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-top: 16px; }
        .qr-placeholder p { font-size: 12px; color: #94a3b8; margin-top: 8px; }
        .divider { border: none; border-top: 1px dashed #e0d9ff; margin: 20px 0; }
        .order-summary { background: #f8faff; border-radius: 12px; padding: 20px; margin: 24px 0; }
        .order-row { display: flex; justify-content: space-between; font-size: 13px; padding: 5px 0; }
        .order-row.total { font-weight: 700; font-size: 15px; border-top: 1px solid #e2e8f0; margin-top: 8px; padding-top: 12px; color: #7C3AED; }
        .cta { text-align: center; margin: 28px 0; }
        .cta a { background: linear-gradient(135deg, #7C3AED, #5B21B6); color: white; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 32px; border-radius: 12px; display: inline-block; }
        .info-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 16px 20px; margin: 20px 0; font-size: 13px; color: #92400e; line-height: 1.6; }
        .footer { background: #1a1a2e; border-radius: 0 0 16px 16px; padding: 28px 40px; text-align: center; }
        .footer p { color: #475569; font-size: 12px; line-height: 1.8; }
        .footer a { color: #9D5EF0; text-decoration: none; }
        .social { margin: 12px 0; }
        .social a { color: #94a3b8; font-size: 12px; text-decoration: none; margin: 0 8px; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <div class="logo">TICKO<span>·</span>PLUG 🔌</div>
        <h1>You're In! 🎉</h1>
        <p>Payment confirmed · Ticket(s) ready</p>
    </div>

    {{-- Body --}}
    <div class="body">
        <p class="greeting">Hey {{ $order->buyer_name }}! 👋</p>
        <p class="subtext">
            Your payment has been confirmed and your {{ $order->tickets->count() == 1 ? 'ticket is' : 'tickets are' }} ready.
            Show the QR code at the door. Have an amazing time!
        </p>

        {{-- Tickets --}}
        @foreach ($order->tickets as $ticket)
        <div class="ticket">
            <div class="ticket-header">
                <span class="ticket-badge">{{ $ticket->ticketType->name }}</span>
                <span class="ticket-number">{{ $ticket->ticket_number }}</span>
            </div>
            <div class="event-name">{{ $order->event->title }}</div>
            <div class="event-meta">
                📅 {{ $order->event->start_datetime->setTimezone('Africa/Nairobi')->format('D, M j, Y · g:i A') }}<br>
                @if ($order->event->venue)
                    📍 {{ $order->event->venue->name }}, {{ $order->event->venue->city }}
                @elseif ($order->event->is_online)
                    🌐 Online Event
                @endif
            </div>
            <hr class="divider">
            <div style="font-size:12px; color:#64748b;">
                👤 {{ $ticket->attendee_name }} &nbsp;·&nbsp; Show at the door — valid once only
            </div>
        </div>
        @endforeach

        {{-- Order Summary --}}
        <div class="order-summary">
            <div class="order-row"><span style="color:#64748b">Order number</span><strong>{{ $order->order_number }}</strong></div>
            @if ($order->mpesa_receipt_number)
            <div class="order-row"><span style="color:#64748b">M-Pesa receipt</span><strong>{{ $order->mpesa_receipt_number }}</strong></div>
            @endif
            <div class="order-row total"><span>Total paid</span><span>KES {{ number_format($order->total) }}</span></div>
        </div>

        {{-- CTA --}}
        <div class="cta">
            <a href="{{ route('orders.confirmation', $order) }}">View & Download Tickets →</a>
        </div>

        {{-- WhatsApp tip --}}
        <div class="info-box">
            💬 <strong>Get your ticket on WhatsApp too!</strong><br>
            We've sent a confirmation to <strong>{{ $order->buyer_phone }}</strong>.
            Reply <strong>TICKET</strong> on WhatsApp to get your QR code delivered directly.
        </div>

        {{-- Important reminders --}}
        <div class="info-box" style="background:#f0fdf4; border-color:#bbf7d0; color:#14532d;">
            ✅ <strong>Remember:</strong><br>
            • Arrive {{ $order->event->doors_open_at ? $order->event->doors_open_at->setTimezone('Africa/Nairobi')->format('g:i A') . ' (doors open)' : 'on time' }}<br>
            • Have your QR code (digital or printed) ready at entry<br>
            • Each QR code is valid for one scan only<br>
            @if ($order->event->dress_code)
            • Dress code: {{ $order->event->dress_code }}<br>
            @endif
            @if ($order->event->min_age)
            • This event is {{ $order->event->min_age }}+ only — carry valid ID
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="social">
            <a href="#">Instagram</a>
            <a href="#">Twitter</a>
            <a href="#">WhatsApp</a>
        </div>
        <p>
            © {{ date('Y') }} Ticko-Plug · Kenya's Freshest Events Platform<br>
            <a href="{{ route('home') }}">ticko-plug.com</a> ·
            <a href="mailto:{{ config('tickoplug.support_email', 'support@ticko-plug.com') }}">support@ticko-plug.com</a>
        </p>
        <p style="margin-top:10px; color:#334155;">
            You're receiving this because you purchased tickets on Ticko-Plug.<br>
            Questions? Reply to this email or contact support.
        </p>
    </div>

</div>
</body>
</html>
