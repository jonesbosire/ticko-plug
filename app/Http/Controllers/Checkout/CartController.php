<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use App\Enums\EventStatus;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(Event $event): View
    {
        abort_if($event->status !== EventStatus::Published, 404);

        $event->load([
            'venue',
            'ticketTypes' => fn ($q) => $q->where('is_visible', true)->orderBy('sort_order'),
        ]);
        $event->load('media');

        return view('checkout.select', compact('event'));
    }

    public function reserve(Request $request, Event $event): RedirectResponse
    {
        abort_if($event->status !== EventStatus::Published, 404);

        $validated = $request->validate([
            'tickets'           => ['required', 'array'],
            'tickets.*.type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'tickets.*.qty'     => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        $selections = collect($validated['tickets'])->filter(fn ($t) => $t['qty'] > 0);

        if ($selections->isEmpty()) {
            return back()->withErrors(['tickets' => 'Please select at least one ticket.']);
        }

        foreach ($selections as $sel) {
            $type = TicketType::findOrFail($sel['type_id']);
            abort_if($type->event_id !== $event->id, 403);

            if (! $type->is_visible) {
                return back()->withErrors(['tickets' => "Ticket type \"{$type->name}\" is no longer available."]);
            }

            if (($type->sale_starts_at && $type->sale_starts_at->isFuture()) ||
                ($type->sale_ends_at   && $type->sale_ends_at->isPast())) {
                return back()->withErrors(['tickets' => "Ticket type \"{$type->name}\" is not currently on sale."]);
            }

            if ($type->quantity_total > 0) {
                $available = $type->quantity_total - $type->quantity_sold - $type->quantity_reserved;
                if ($sel['qty'] > $available) {
                    return back()->withErrors(['tickets' => "Only {$available} ticket(s) remaining for \"{$type->name}\"."]);
                }
            }

            if ($type->max_per_order && $sel['qty'] > $type->max_per_order) {
                return back()->withErrors(['tickets' => "Maximum {$type->max_per_order} ticket(s) per order for \"{$type->name}\"."]);
            }
        }

        $cartKey = 'cart_' . $event->id . '_' . session()->getId();
        Cache::put($cartKey, $selections->values()->toArray(), now()->addMinutes(15));
        session(['checkout_event_id' => $event->id, 'checkout_cart_key' => $cartKey]);

        return redirect()->route('checkout.details', $event);
    }
}
