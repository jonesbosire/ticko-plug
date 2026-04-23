<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Enums\EventStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $query = Event::query()
            ->where('status', EventStatus::Published)
            ->where('visibility', 'public')
            ->with(['venue', 'ticketTypes', 'category'])
            ->with('media');

        // Date filter
        match ($request->get('when')) {
            'today'        => $query->whereDate('start_datetime', today()),
            'tomorrow'     => $query->whereDate('start_datetime', today()->addDay()),
            'this_weekend' => $query->whereBetween('start_datetime', [now()->startOfWeek()->addDays(5), now()->startOfWeek()->addDays(6)->endOfDay()]),
            'this_week'    => $query->whereBetween('start_datetime', [now(), now()->endOfWeek()]),
            'this_month'   => $query->whereMonth('start_datetime', now()->month),
            default        => $query->where('start_datetime', '>', now()),
        };

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        // Location / city filter
        if ($request->filled('city')) {
            $query->whereHas('venue', fn ($q) => $q->where('city', 'like', '%' . $request->city . '%'));
        }

        // Price filter
        if ($request->get('price') === 'free') {
            $query->whereHas('ticketTypes', fn ($q) => $q->where('price', 0));
        } elseif ($request->get('price') === 'paid') {
            $query->whereHas('ticketTypes', fn ($q) => $q->where('price', '>', 0));
        }

        // Sort
        match ($request->get('sort', 'date')) {
            'popular'  => $query->orderByDesc('total_tickets_sold'),
            'featured' => $query->orderByDesc('featured_at')->orderBy('start_datetime'),
            default    => $query->orderBy('start_datetime'),
        };

        $events = $query->paginate(16)->withQueryString();

        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('events.index', compact('events', 'categories'));
    }

    public function show(Event $event): View
    {
        abort_unless(
            $event->status === EventStatus::Published || $event->status->value === 'cancelled',
            404
        );

        $event->load([
            'venue',
            'category',
            'ticketTypes' => fn ($q) => $q->orderBy('sort_order'),
            'organizer.organizerProfile',
            'updates' => fn ($q) => $q->latest()->limit(5),
        ]);
        $event->load('media');

        $relatedEvents = Event::query()
            ->where('status', EventStatus::Published)
            ->where('id', '!=', $event->id)
            ->where('category_id', $event->category_id)
            ->where('start_datetime', '>', now())
            ->with(['venue', 'ticketTypes'])
            ->with('media')
            ->orderBy('start_datetime')
            ->limit(4)
            ->get();

        $minPrice = $event->ticketTypes->where('is_visible', true)->min('price');
        $maxPrice = $event->ticketTypes->where('is_visible', true)->max('price');
        $totalAvailable = $event->ticketTypes->sum(fn ($t) => max(0, $t->quantity_total - $t->quantity_sold - $t->quantity_reserved));

        return view('events.show', compact(
            'event',
            'relatedEvents',
            'minPrice',
            'maxPrice',
            'totalAvailable',
        ));
    }

    public function byCategory(Category $category, Request $request): View
    {
        $events = Event::query()
            ->where('status', EventStatus::Published)
            ->where('visibility', 'public')
            ->where('category_id', $category->id)
            ->where('start_datetime', '>', now())
            ->with(['venue', 'ticketTypes', 'category'])
            ->with('media')
            ->orderBy('start_datetime')
            ->paginate(16)
            ->withQueryString();

        $categories = Category::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->get();

        return view('events.index', compact('events', 'categories', 'category'));
    }
}
