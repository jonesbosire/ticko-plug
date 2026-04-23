@extends('layouts.app')

@section('title', $event->title)
@section('description', $event->tagline ?? Str::limit(strip_tags($event->description), 160))
@section('keywords', implode(', ', array_merge(
    [$event->title, $event->category->name ?? '', $event->venue->city ?? 'Nairobi', 'tickets', 'Kenya'],
    is_array($event->tags) ? $event->tags : []
)))
@section('og_type', 'event')
@section('og_image', $event->getFirstMediaUrl('banner') ?: asset('images/og-default.jpg'))
@section('canonical', route('events.show', $event))

@php
    $minPrice = $event->ticketTypes->where('is_visible', true)->min('price') ?? 0;
    $maxPrice = $event->ticketTypes->where('is_visible', true)->max('price') ?? 0;
    $availabilityStatus = $event->ticketTypes->where('is_visible', true)
        ->sum(fn ($t) => max(0, $t->quantity_total - $t->quantity_sold - $t->quantity_reserved)) > 0
        ? 'https://schema.org/InStock'
        : 'https://schema.org/SoldOut';
@endphp

@push('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Event",
    "name": "{{ addslashes($event->title) }}",
    "description": "{{ addslashes(Str::limit(strip_tags($event->description), 250)) }}",
    "startDate": "{{ $event->start_datetime->toIso8601String() }}",
    "endDate": "{{ $event->end_datetime->toIso8601String() }}",
    "url": "{{ route('events.show', $event) }}",
    "image": ["{{ $event->getFirstMediaUrl('banner') ?: asset('images/og-default.jpg') }}"],
    "eventStatus": "{{ $event->status->value === 'cancelled' ? 'https://schema.org/EventCancelled' : ($event->status->value === 'postponed' ? 'https://schema.org/EventPostponed' : 'https://schema.org/EventScheduled') }}",
    "eventAttendanceMode": "{{ $event->is_online ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode' }}",
    @if (!$event->is_online && $event->venue)
    "location": {
        "@@type": "Place",
        "name": "{{ addslashes($event->venue->name) }}",
        "address": {
            "@@type": "PostalAddress",
            "streetAddress": "{{ addslashes($event->venue->address_line1 ?? '') }}",
            "addressLocality": "{{ addslashes($event->venue->city ?? 'Nairobi') }}",
            "addressCountry": "KE"
        }
    },
    @else
    "location": { "@@type": "VirtualLocation", "url": "{{ $event->online_event_url ?? route('events.show', $event) }}" },
    @endif
    "organizer": {
        "@@type": "Organization",
        "name": "{{ addslashes($event->organizer->name ?? 'Ticko-Plug') }}"
    },
    "offers": {
        "@@type": "Offer",
        "url": "{{ route('checkout.select', $event) }}",
        "priceCurrency": "KES",
        "price": {{ $minPrice }},
        "availability": "{{ $availabilityStatus }}",
        "validFrom": "{{ now()->toIso8601String() }}"
    }
}
</script>
@endpush

@section('content')

@php
    $banner = $event->getFirstMediaUrl('banner') ?: $event->getFirstMediaUrl('images');
    $isCancelled = $event->status->value === 'cancelled';
    $isPostponed = $event->status->value === 'postponed';
@endphp

{{-- ═══════════════════════════════════════════════
     HERO BANNER
═══════════════════════════════════════════════ --}}
<div class="relative w-full overflow-hidden" style="max-height:520px">
    @if ($banner)
        <img src="{{ $banner }}" alt="{{ $event->title }}"
             class="w-full object-cover" style="max-height:520px; filter:brightness(0.7)">
    @else
        <div class="w-full flex items-center justify-center text-9xl"
             style="height:360px; background:linear-gradient(135deg, {{ $event->category->color ?? '#7C3AED' }}33, #12121E)">
            🎪
        </div>
    @endif

    {{-- Gradient overlay --}}
    <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(8,8,17,1) 0%, rgba(8,8,17,0.2) 60%, transparent 100%)"></div>

    {{-- Back button --}}
    <div class="absolute top-6 left-6">
        <a href="{{ url()->previous() }}" class="btn-ghost text-sm py-2 px-4"
           style="background:rgba(8,8,17,0.6); backdrop-filter:blur(8px)">
            ← Back
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     CONTENT
═══════════════════════════════════════════════ --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-10 pb-20">
    <div class="flex flex-col lg:flex-row gap-10">

        {{-- ── LEFT: Event Info ── --}}
        <div class="flex-1 min-w-0">

            {{-- Category + Status --}}
            <div class="flex items-center gap-3 mb-4 flex-wrap">
                @if ($event->category)
                    <span class="ticket-badge" style="background:{{ $event->category->color ?? '#7C3AED' }}22; border-color:{{ $event->category->color ?? '#7C3AED' }}55; color:{{ $event->category->color ?? '#F0C427' }}">
                        {{ $event->category->name }}
                    </span>
                @endif
                @if ($isCancelled)
                    <span class="badge badge-danger">Cancelled</span>
                @elseif ($isPostponed)
                    <span class="badge badge-warning">Postponed</span>
                @elseif ($event->featured_at)
                    <span class="ticket-badge">⭐ Featured</span>
                @endif
                @if ($event->is_online)
                    <span class="badge badge-info">🌐 Online Event</span>
                @endif
            </div>

            {{-- Title --}}
            <h1 class="section-heading text-4xl md:text-5xl mb-4 leading-tight">{{ $event->title }}</h1>

            @if ($event->tagline)
                <p class="text-lg mb-6" style="color:var(--color-brand-muted)">{{ $event->tagline }}</p>
            @endif

            {{-- Cancellation notice --}}
            @if ($isCancelled && $event->cancellation_reason)
                <div class="p-4 rounded-xl mb-6" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3)">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">⚠️</span>
                        <div>
                            <p class="font-semibold mb-1" style="color:#EF4444">This event has been cancelled</p>
                            <p class="text-sm" style="color:var(--color-brand-muted)">{{ $event->cancellation_reason }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Key Details --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">

                {{-- Date & Time --}}
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
                    <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center" style="background:rgba(124,58,237,0.2)">
                        📅
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:var(--color-brand-muted)">Date & Time</p>
                        <p class="font-semibold text-sm">{{ $event->start_datetime->format('l, F j, Y') }}</p>
                        <p class="text-sm" style="color:var(--color-brand-muted)">
                            {{ $event->start_datetime->format('g:i A') }}
                            @if ($event->end_datetime)
                                – {{ $event->end_datetime->format('g:i A') }}
                            @endif
                        </p>
                        @if ($event->doors_open_at)
                            <p class="text-xs mt-1" style="color:var(--color-brand-subtle)">Doors open {{ $event->doors_open_at->format('g:i A') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Venue --}}
                @if ($event->venue)
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
                    <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center" style="background:rgba(240,196,39,0.15)">
                        📍
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:var(--color-brand-muted)">Venue</p>
                        <p class="font-semibold text-sm">{{ $event->venue->name }}</p>
                        <p class="text-sm" style="color:var(--color-brand-muted)">{{ $event->venue->address }}, {{ $event->venue->city }}</p>
                        @if ($event->venue->google_maps_url)
                            <a href="{{ $event->venue->google_maps_url }}" target="_blank" rel="noopener"
                               class="text-xs mt-1 inline-block" style="color:var(--color-brand-primary)">
                                Get directions →
                            </a>
                        @endif
                    </div>
                </div>
                @elseif ($event->is_online)
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
                    <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center" style="background:rgba(56,189,248,0.15)">
                        🌐
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:var(--color-brand-muted)">Location</p>
                        <p class="font-semibold text-sm">Online Event</p>
                        <p class="text-sm" style="color:var(--color-brand-muted)">Link sent after purchase</p>
                    </div>
                </div>
                @endif

                {{-- Organizer --}}
                @if ($event->organizer?->organizerProfile)
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
                    <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center overflow-hidden" style="background:rgba(124,58,237,0.2)">
                        🎪
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:var(--color-brand-muted)">Organizer</p>
                        <p class="font-semibold text-sm">{{ $event->organizer->organizerProfile->organization_name }}</p>
                    </div>
                </div>
                @endif

                {{-- Extra details --}}
                @if ($event->dress_code || $event->min_age)
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
                    <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center" style="background:rgba(249,115,22,0.15)">
                        ℹ️
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:var(--color-brand-muted)">Details</p>
                        @if ($event->min_age)
                            <p class="text-sm font-medium">{{ $event->min_age }}+ only</p>
                        @endif
                        @if ($event->dress_code)
                            <p class="text-sm" style="color:var(--color-brand-muted)">Dress: {{ $event->dress_code }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- Description --}}
            <div class="mb-10">
                <h2 class="text-xl font-bold mb-4">About This Event</h2>
                <div class="prose prose-invert max-w-none text-sm leading-relaxed"
                     style="color:var(--color-brand-muted)">
                    {!! nl2br(e($event->description)) !!}
                </div>
            </div>

            {{-- Tags --}}
            @if ($event->tags && count($event->tags) > 0)
                <div class="flex flex-wrap gap-2 mb-10">
                    @foreach ($event->tags as $tag)
                        <span class="px-3 py-1 rounded-full text-xs font-medium"
                              style="background:var(--color-brand-elevated); border:1px solid var(--color-brand-border); color:var(--color-brand-muted)">
                            #{{ $tag }}
                        </span>
                    @endforeach
                </div>
            @endif

            {{-- Event Updates --}}
            @if ($event->updates && $event->updates->isNotEmpty())
                <div class="mb-10">
                    <h2 class="text-xl font-bold mb-4">Updates</h2>
                    <div class="space-y-3">
                        @foreach ($event->updates as $update)
                            <div class="p-4 rounded-xl" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-sm">{{ $update->title }}</span>
                                    <span class="text-xs" style="color:var(--color-brand-muted)">{{ $update->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm" style="color:var(--color-brand-muted)">{{ $update->body }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- ── RIGHT: Ticket Widget (sticky) ── --}}
        <div class="w-full lg:w-96 shrink-0">
            <div class="sticky top-24">
                <div class="rounded-2xl overflow-hidden" style="background:var(--color-brand-surface); border:1.5px solid var(--color-brand-border)">

                    {{-- Widget header --}}
                    <div class="px-6 py-5 border-b" style="border-color:var(--color-brand-border)">
                        <div class="flex items-baseline justify-between">
                            <div>
                                @if ($totalAvailable > 0)
                                    @if ($minPrice == 0)
                                        <span class="text-2xl font-bold price-tag free">Free</span>
                                    @else
                                        <div>
                                            <span class="text-xs" style="color:var(--color-brand-muted)">From</span>
                                            <span class="text-2xl font-bold price-tag">KES {{ number_format($minPrice) }}</span>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-lg font-bold" style="color:var(--color-brand-danger)">Sold Out</span>
                                @endif
                            </div>
                            @if ($totalAvailable > 0 && $totalAvailable <= 20)
                                <span class="badge badge-warning text-xs">Only {{ $totalAvailable }} left</span>
                            @endif
                        </div>
                    </div>

                    {{-- Ticket Types --}}
                    @if (!$isCancelled && $totalAvailable > 0)
                        <div class="px-6 py-4">
                            <div class="space-y-3">
                                @foreach ($event->ticketTypes->where('is_visible', true) as $type)
                                    @php
                                        $available = $type->quantity_total > 0 ? max(0, $type->quantity_total - $type->quantity_sold - $type->quantity_reserved) : PHP_INT_MAX;
                                        $isTierSoldOut = $type->quantity_total > 0 && $available === 0;
                                        $saleEnded = $type->sale_ends_at && $type->sale_ends_at->isPast();
                                        $saleNotStarted = $type->sale_starts_at && $type->sale_starts_at->isFuture();
                                        $unavailable = $isTierSoldOut || $saleEnded || $saleNotStarted;
                                    @endphp
                                    <div class="p-4 rounded-xl transition-colors"
                                         style="background:var(--color-brand-elevated); border:1px solid {{ $unavailable ? 'var(--color-brand-border)' : 'rgba(124,58,237,0.3)' }}; opacity:{{ $unavailable ? '0.5' : '1' }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-sm">{{ $type->name }}</p>
                                                @if ($type->description)
                                                    <p class="text-xs mt-0.5 truncate" style="color:var(--color-brand-muted)">{{ $type->description }}</p>
                                                @endif
                                                @if ($saleNotStarted)
                                                    <p class="text-xs mt-1" style="color:var(--color-brand-warning)">Sales start {{ $type->sale_starts_at->format('M j, g:i A') }}</p>
                                                @elseif ($type->sale_ends_at && !$saleEnded)
                                                    <p class="text-xs mt-1" style="color:var(--color-brand-subtle)">Sale ends {{ $type->sale_ends_at->format('M j') }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right shrink-0">
                                                <p class="font-bold text-sm {{ $type->price == 0 ? 'price-tag free' : 'price-tag' }}">
                                                    {{ $type->price == 0 ? 'FREE' : 'KES ' . number_format($type->price) }}
                                                </p>
                                                @if ($isTierSoldOut)
                                                    <p class="text-xs mt-0.5" style="color:var(--color-brand-danger)">Sold out</p>
                                                @elseif ($available <= 10 && $type->quantity_total > 0)
                                                    <p class="text-xs mt-0.5" style="color:var(--color-brand-warning)">{{ $available }} left</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- CTA --}}
                            <a href="{{ route('checkout.select', $event) }}" class="btn-primary w-full justify-center mt-4 text-base">
                                🎟️ Get Tickets
                            </a>

                            <p class="text-xs text-center mt-3" style="color:var(--color-brand-subtle)">
                                Secure checkout · Pay with M-Pesa or Card
                            </p>
                        </div>
                    @elseif ($isCancelled)
                        <div class="px-6 py-6 text-center">
                            <p class="text-sm" style="color:var(--color-brand-muted)">This event has been cancelled. No new tickets are available.</p>
                        </div>
                    @else
                        <div class="px-6 py-6 text-center">
                            <p class="font-semibold mb-2">Sold Out</p>
                            <p class="text-sm mb-4" style="color:var(--color-brand-muted)">All tickets have been sold. Join the waitlist to be notified if tickets become available.</p>
                            <button class="btn-ghost w-full justify-center text-sm">Join Waitlist</button>
                        </div>
                    @endif

                </div>

                {{-- Share --}}
                <div class="mt-4 p-4 rounded-xl text-center" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
                    <p class="text-xs mb-3 font-semibold" style="color:var(--color-brand-muted)">Share this event</p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="https://wa.me/?text={{ urlencode($event->title . ' - ' . route('events.show', $event)) }}"
                           target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold transition-colors"
                           style="background:rgba(37,211,102,0.15); color:#25D366; border:1px solid rgba(37,211,102,0.3)"
                           title="Share on WhatsApp">
                            W
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($event->title) }}&url={{ urlencode(route('events.show', $event)) }}"
                           target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold transition-colors"
                           style="background:rgba(29,155,240,0.15); color:#1D9BF0; border:1px solid rgba(29,155,240,0.3)"
                           title="Share on X">
                            𝕏
                        </a>
                        <button onclick="navigator.clipboard.writeText('{{ route('events.show', $event) }}'); this.innerHTML='✓ Copied'"
                                class="w-9 h-9 rounded-xl flex items-center justify-center text-xs transition-colors"
                                style="background:var(--color-brand-elevated); color:var(--color-brand-muted); border:1px solid var(--color-brand-border)"
                                title="Copy link">
                            🔗
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Related Events --}}
    @if ($relatedEvents->isNotEmpty())
        <div class="mt-16">
            <h2 class="text-2xl font-bold mb-6">More {{ $event->category->name ?? 'Events' }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($relatedEvents as $related)
                    @include('partials.event-card', ['event' => $related])
                @endforeach
            </div>
        </div>
    @endif

</div>

@endsection
