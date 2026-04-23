<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            background: #080811;
            color: #F8F8FF;
            width: 595px;
            height: 283px;
        }
        .ticket {
            width: 100%;
            height: 283px;
            display: flex;
            background: #12121E;
            border: 2px solid #7C3AED;
            border-radius: 12px;
            overflow: hidden;
        }
        /* Left section */
        .left {
            flex: 1;
            padding: 24px 28px;
            background: linear-gradient(135deg, #12121E 0%, #1C1C2E 100%);
            border-right: 2px dashed #2A2A40;
        }
        .brand {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #9D5EF0;
            margin-bottom: 12px;
        }
        .event-name {
            font-size: 20px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.02em;
            color: #F8F8FF;
            margin-bottom: 14px;
        }
        .meta {
            font-size: 11px;
            color: #94A3B8;
            line-height: 1.8;
            margin-bottom: 16px;
        }
        .meta strong { color: #F8F8FF; }
        .badge {
            display: inline-block;
            background: rgba(240,196,39,0.2);
            border: 1px solid rgba(240,196,39,0.5);
            color: #F0C427;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 3px 10px;
            border-radius: 999px;
            margin-bottom: 14px;
        }
        .attendee {
            font-size: 12px;
            color: #94A3B8;
        }
        .attendee strong { color: #F8F8FF; font-size: 14px; display: block; }
        .ticket-num {
            margin-top: 12px;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            font-size: 11px;
            color: #475569;
            letter-spacing: 0.1em;
        }
        /* Right section */
        .right {
            width: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #080811;
            gap: 12px;
        }
        .qr-wrapper {
            background: white;
            padding: 8px;
            border-radius: 8px;
        }
        .qr-wrapper img { display: block; }
        .scan-text {
            font-size: 10px;
            color: #475569;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .valid-badge {
            font-size: 10px;
            color: #22C55E;
            font-weight: 700;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="ticket">
    <div class="left">
        <div class="brand">🔌 Ticko-Plug · Official Ticket</div>

        <div class="badge">{{ $ticket->ticketType->name }}</div>

        <div class="event-name">{{ $ticket->order->event->title }}</div>

        <div class="meta">
            <strong>{{ $ticket->order->event->start_datetime->setTimezone('Africa/Nairobi')->format('l, F j, Y') }}</strong>
            {{ $ticket->order->event->start_datetime->setTimezone('Africa/Nairobi')->format('g:i A') }}
            @if ($ticket->order->event->doors_open_at)
                · Doors {{ $ticket->order->event->doors_open_at->setTimezone('Africa/Nairobi')->format('g:i A') }}
            @endif
            <br>
            @if ($ticket->order->event->venue)
                📍 {{ $ticket->order->event->venue->name }}, {{ $ticket->order->event->venue->city }}
            @elseif ($ticket->order->event->is_online)
                🌐 Online Event
            @endif
        </div>

        <div class="attendee">
            Attendee
            <strong>{{ $ticket->attendee_name }}</strong>
        </div>

        <div class="ticket-num">{{ $ticket->ticket_number }}</div>
    </div>

    <div class="right">
        <div class="qr-wrapper">
            {!! SimpleSoftware\QrCode\Facades\QrCode::format('svg')
                ->size(120)
                ->errorCorrection('H')
                ->generate($ticket->qr_code_secret) !!}
        </div>
        <div class="scan-text">Scan at entry</div>
        <div class="valid-badge">✓ Valid · Single Use</div>
    </div>
</div>
</body>
</html>
