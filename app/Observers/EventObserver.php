<?php

namespace App\Observers;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;

class EventObserver
{
    /**
     * Bust homepage and sitemap caches whenever an event is saved.
     * Runs after both create and update.
     */
    public function saved(Event $event): void
    {
        Cache::forget('home:featured_events');
        Cache::forget('home:upcoming_events');
        Cache::forget('home:weekend_events');
        Cache::forget('sitemap:events');
        Cache::forget('sitemap:index');
    }

    public function deleted(Event $event): void
    {
        $this->saved($event);
    }
}
