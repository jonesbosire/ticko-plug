@php
    $banner = $event->getFirstMediaUrl('banner') ?: $event->getFirstMediaUrl('images');
    $minPrice = $event->ticketTypes->where('is_visible', true)->min('price');
    $isFree = $minPrice === null || $minPrice == 0;
    $isSoldOut = $event->ticketTypes->where('is_visible', true)
        ->every(fn ($t) => $t->quantity_total > 0 && ($t->quantity_sold + $t->quantity_reserved) >= $t->quantity_total);
@endphp

<a href="{{ route('events.show', $event) }}" class="event-card block group">

    {{-- Banner --}}
    <div class="event-card-banner">
        @if ($banner)
            <img src="{{ $banner }}" alt="{{ $event->title }}" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center"
                 style="background:linear-gradient(135deg, {{ $event->category->color ?? '#7C3AED' }}33, {{ $event->category->color ?? '#1C1C2E' }}66)">
                <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
        @endif

        <div class="event-card-banner-overlay"></div>

        {{-- Category badge --}}
        <div class="absolute top-3 left-3">
            <span class="ticket-badge" style="background:{{ $event->category->color ?? '#7C3AED' }}22; border-color:{{ $event->category->color ?? '#7C3AED' }}55; color:{{ $event->category->color ?? '#F0C427' }}">
                {{ $event->category->name ?? 'Event' }}
            </span>
        </div>

        {{-- Featured badge --}}
        @if (isset($featured) && $featured && $event->featured_at)
            <div class="absolute top-3 right-3">
                <span class="ticket-badge inline-flex items-center gap-1" style="background:rgba(240,196,39,0.2); border-color:rgba(240,196,39,0.5); color:#F0C427">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Featured
                </span>
            </div>
        @endif

        {{-- Sold out overlay --}}
        @if ($isSoldOut)
            <div class="absolute inset-0 flex items-center justify-center"
                 style="background:rgba(8,8,17,0.7)">
                <span class="badge badge-danger text-sm px-4 py-2">Sold Out</span>
            </div>
        @endif
    </div>

    {{-- Details --}}
    <div class="p-4">

        {{-- Date + time --}}
        <div class="flex items-center gap-1.5 text-xs font-medium mb-2" style="color:var(--color-brand-primary-glow)">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ $event->start_datetime->format('D, M j') }} · {{ $event->start_datetime->format('g:i A') }}
        </div>

        {{-- Title --}}
        <h3 class="font-bold text-base leading-snug mb-2 group-hover:text-purple-300 transition-colors line-clamp-2">
            {{ $event->title }}
        </h3>

        {{-- Venue --}}
        @if ($event->venue)
            <div class="flex items-center gap-1.5 text-xs mb-3" style="color:var(--color-brand-muted)">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="truncate">{{ $event->venue->name }}, {{ $event->venue->city }}</span>
            </div>
        @elseif ($event->is_online)
            <div class="flex items-center gap-1.5 text-xs mb-3" style="color:var(--color-brand-muted)">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
                </svg>
                <span>Online Event</span>
            </div>
        @endif

        {{-- Price --}}
        <div class="flex items-center justify-between">
            <div>
                @if ($isSoldOut)
                    <span class="text-sm font-bold" style="color:var(--color-brand-danger)">Sold Out</span>
                @elseif ($isFree)
                    <span class="price-tag free text-sm">FREE</span>
                @else
                    <span class="price-tag text-sm">
                        KES {{ number_format($minPrice) }}
                        @if ($event->ticketTypes->where('is_visible', true)->count() > 1)
                            <span class="text-xs font-normal" style="color:var(--color-brand-muted)">+</span>
                        @endif
                    </span>
                @endif
            </div>

            <div class="w-8 h-8 rounded-xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                 style="background:var(--color-brand-primary); color:white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </div>
    </div>
</a>
