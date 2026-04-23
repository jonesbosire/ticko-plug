<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckInSession;
use App\Models\Event;
use App\Services\SmsService;
use App\Services\Ticket\CheckInService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckInController extends Controller
{
    public function __construct(
        private readonly CheckInService $checkInService,
        private readonly SmsService $sms,
    ) {}

    /**
     * Scan a QR code secret.
     * Throttled at 120/min per IP.
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'secret'   => ['required', 'string', 'size:64'],
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'device'   => ['nullable', 'string', 'max:100'],
        ]);

        // Rate-limit per device token to prevent brute-force
        $deviceKey = 'scan_' . ($request->header('X-Device-Token') ?? $request->ip());
        if (Cache::has("scan_flood_{$deviceKey}")) {
            return response()->json([
                'success' => false,
                'message' => '⏳ Too many scans. Wait a moment.',
            ], 429);
        }
        Cache::put("scan_flood_{$deviceKey}", 1, now()->addMilliseconds(500));

        $result = $this->checkInService->scan(
            secret: $validated['secret'],
            eventId: $validated['event_id'],
            scannedById: auth()->id(),
            deviceName: $validated['device'] ?? $request->userAgent(),
        );

        // Send SMS confirmation on successful check-in
        if ($result['success'] && $result['ticket']) {
            $ticket = $result['ticket'];
            dispatch(function () use ($ticket) {
                $this->sms->sendCheckedIn(
                    phone: $ticket->attendee_phone,
                    eventName: $ticket->event->title,
                );
            })->afterResponse();
        }

        $ticket = $result['ticket'];

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'ticket'  => $ticket ? [
                'ticket_number'  => $ticket->ticket_number,
                'attendee_name'  => $ticket->attendee_name,
                'ticket_type'    => $ticket->ticketType?->name,
                'checked_in_at'  => $ticket->checked_in_at?->format('H:i d/m/Y'),
            ] : null,
        ], $result['success'] ? 200 : 422);
    }

    /**
     * Start a check-in session for an event gate/door.
     */
    public function startSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id'    => ['required', 'integer', 'exists:events,id'],
            'gate_name'   => ['required', 'string', 'max:100'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $session = CheckInSession::create([
            'event_id'        => $validated['event_id'],
            'staff_user_id'   => auth()->id(),
            'gate_name'       => $validated['gate_name'],
            'device_name'     => $validated['device_name'] ?? $request->userAgent(),
            'is_active'       => true,
            'started_at'      => now(),
            'total_checked_in'=> 0,
        ]);

        return response()->json([
            'success'    => true,
            'session_id' => $session->id,
            'gate_name'  => $session->gate_name,
            'event_id'   => $session->event_id,
        ]);
    }

    /**
     * End a check-in session.
     */
    public function endSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'integer', 'exists:check_in_sessions,id'],
        ]);

        $session = CheckInSession::findOrFail($validated['session_id']);

        abort_if($session->staff_user_id !== auth()->id(), 403);

        $session->update([
            'is_active' => false,
            'ended_at'  => now(),
        ]);

        return response()->json([
            'success'          => true,
            'total_checked_in' => $session->total_checked_in,
            'duration_minutes' => $session->started_at->diffInMinutes(now()),
        ]);
    }
}
