<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Enums\EventStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckInScannerController extends Controller
{
    public function show(Request $request, Event $event): View
    {
        // Only event organizer, staff, or admin can access scanner
        $user = auth()->user();
        abort_unless(
            $user->hasRole(['super_admin', 'admin']) ||
            $event->organizer_id === $user->id ||
            $event->staff()->where('user_id', $user->id)->exists(),
            403,
            'You do not have access to this event\'s check-in scanner.'
        );

        $stats = [
            'total_tickets'   => Ticket::where('event_id', $event->id)->whereIn('status', ['active', 'used'])->count(),
            'checked_in'      => Ticket::where('event_id', $event->id)->where('status', 'used')->count(),
            'remaining'       => Ticket::where('event_id', $event->id)->where('status', 'active')->count(),
        ];
        $stats['percentage'] = $stats['total_tickets'] > 0
            ? round(($stats['checked_in'] / $stats['total_tickets']) * 100, 1)
            : 0;

        return view('scanner.show', compact('event', 'stats'));
    }
}
