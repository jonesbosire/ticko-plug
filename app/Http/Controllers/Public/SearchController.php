<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Enums\EventStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->get('q', '');
        $events = collect();

        if (strlen($query) >= 2) {
            $events = Event::query()
                ->where('status', EventStatus::Published)
                ->where('visibility', 'public')
                ->where('start_datetime', '>', now())
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('tagline', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhereHas('venue', fn ($q) => $q->where('name', 'like', "%{$query}%")->orWhere('city', 'like', "%{$query}%"))
                      ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$query}%"));
                })
                ->with(['venue', 'ticketTypes', 'category'])
                ->with('media')
                ->orderBy('start_datetime')
                ->paginate(16)
                ->withQueryString();
        }

        return view('search', compact('events', 'query'));
    }
}
