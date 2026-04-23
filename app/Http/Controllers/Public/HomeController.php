<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Enums\EventStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Cache homepage data for 5 minutes — high-traffic page, data rarely changes
        $featuredEvents = Cache::remember('home:featured_events', 300, fn () =>
            Event::query()
                ->where('status', EventStatus::Published)
                ->where('visibility', 'public')
                ->whereNotNull('featured_at')
                ->where('start_datetime', '>', now())
                ->with(['venue', 'ticketTypes', 'category'])
                ->with('media')
                ->orderByDesc('featured_at')
                ->limit(6)
                ->get()
        );

        $upcomingEvents = Cache::remember('home:upcoming_events', 300, fn () =>
            Event::query()
                ->where('status', EventStatus::Published)
                ->where('visibility', 'public')
                ->where('start_datetime', '>', now())
                ->with(['venue', 'ticketTypes', 'category'])
                ->with('media')
                ->orderBy('start_datetime')
                ->limit(12)
                ->get()
        );

        // Weekend events change daily — shorter TTL
        $thisWeekendEvents = Cache::remember('home:weekend_events', 60, fn () =>
            Event::query()
                ->where('status', EventStatus::Published)
                ->where('visibility', 'public')
                ->whereBetween('start_datetime', [
                    now()->startOfWeek()->addDays(5),             // Saturday
                    now()->startOfWeek()->addDays(6)->endOfDay(), // Sunday end
                ])
                ->with(['venue', 'ticketTypes', 'category'])
                ->with('media')
                ->orderBy('start_datetime')
                ->limit(6)
                ->get()
        );

        // Categories rarely change — cache for 10 minutes
        $categories = Cache::remember('home:categories', 600, fn () =>
            Category::query()
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->withCount(['events' => fn ($q) => $q
                    ->where('status', EventStatus::Published)
                    ->where('start_datetime', '>', now())
                ])
                ->get()
        );

        return view('home', compact(
            'featuredEvents',
            'upcomingEvents',
            'thisWeekendEvents',
            'categories',
        ));
    }
}
