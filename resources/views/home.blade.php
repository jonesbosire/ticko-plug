@extends('layouts.app')

@section('title', 'Ticko-Plug')
@section('description', 'Kenya\'s freshest events ticketing platform. Plug into concerts, comedy, sports, festivals and more.')

@section('content')

{{-- ═══════════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════════ --}}
<section class="hero-bg relative overflow-hidden py-24 md:py-36">
    {{-- Decorative blobs --}}
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute top-0 left-1/4 w-96 h-96 rounded-full opacity-20 blur-3xl"
             style="background: radial-gradient(circle, #7C3AED, transparent)"></div>
        <div class="absolute bottom-0 right-1/4 w-72 h-72 rounded-full opacity-10 blur-3xl"
             style="background: radial-gradient(circle, #F0C427, transparent)"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-widest mb-6"
                 style="background:rgba(124,58,237,0.15); border:1px solid rgba(124,58,237,0.4); color:#9D5EF0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Plug Into The Vibe
            </div>

            <h1 class="section-heading text-5xl md:text-7xl mb-6">
                Your Ticket To<br>
                <span class="gradient-text">Every Experience</span>
            </h1>

            <p class="text-lg md:text-xl mb-10 max-w-2xl mx-auto" style="color:var(--color-brand-muted)">
                Kenya's freshest events platform. Concerts, comedy, sports, festivals — buy your ticket in seconds, pay with M-Pesa.
            </p>

            {{-- Search Bar --}}
            <form action="{{ route('search') }}" method="GET" class="max-w-2xl mx-auto">
                <div class="flex gap-2 p-2 rounded-2xl" style="background:var(--color-brand-elevated); border:1.5px solid var(--color-brand-border)">
                    <div class="flex items-center gap-3 flex-1 px-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--color-brand-muted)">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="q" placeholder="Search events, artists, venues…"
                               class="bg-transparent flex-1 outline-none text-base"
                               style="color:var(--color-brand-text); font-family:var(--font-sans)"
                               autocomplete="off">
                    </div>
                    <button type="submit" class="btn-primary shrink-0 py-3 px-6">Search</button>
                </div>
            </form>

            {{-- Quick stats --}}
            <div class="flex items-center justify-center gap-8 mt-10 text-sm" style="color:var(--color-brand-muted)">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    <strong class="text-white">10K+</strong> tickets sold
                </span>
                <span class="w-px h-4" style="background:var(--color-brand-border)"></span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <strong class="text-white">500+</strong> events listed
                </span>
                <span class="w-px h-4" style="background:var(--color-brand-border)"></span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <strong class="text-white">20+</strong> cities
                </span>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CATEGORIES
═══════════════════════════════════════════════ --}}
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold">Browse by Category</h2>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-6 gap-3">
            @foreach ($categories as $cat)
                <a href="{{ route('events.category', $cat) }}"
                   class="group flex flex-col items-center gap-2 p-4 rounded-2xl text-center transition-all duration-200"
                   style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)"
                   onmouseover="this.style.borderColor='{{ $cat->color ?? '#7C3AED' }}44'; this.style.background='{{ $cat->color ?? '#7C3AED' }}11'"
                   onmouseout="this.style.borderColor='var(--color-brand-border)'; this.style.background='var(--color-brand-surface)'">

                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background:{{ $cat->color ?? '#7C3AED' }}22; color:{{ $cat->color ?? '#7C3AED' }}">
                        <x-dynamic-component :component="$cat->icon ?? 'heroicon-o-star'" class="w-5 h-5"/>
                    </div>
                    <span class="text-xs font-medium leading-tight" style="color:var(--color-brand-muted)">{{ $cat->name }}</span>
                    @if($cat->events_count > 0)
                        <span class="text-xs" style="color:var(--color-brand-subtle)">{{ $cat->events_count }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     FEATURED EVENTS
═══════════════════════════════════════════════ --}}
@if ($featuredEvents->isNotEmpty())
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full animate-pulse" style="background:#F0C427"></span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#F0C427">Featured</span>
                </div>
                <h2 class="text-2xl font-bold">Don't Miss These</h2>
            </div>
            <a href="{{ route('events.index') }}" class="btn-ghost text-sm py-2 px-4">View all</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($featuredEvents as $event)
                @include('partials.event-card', ['event' => $event, 'featured' => true])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     THIS WEEKEND
═══════════════════════════════════════════════ --}}
@if ($thisWeekendEvents->isNotEmpty())
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--color-brand-accent-alt)">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--color-brand-accent-alt)">Weekend Vibes</span>
                </div>
                <h2 class="text-2xl font-bold">Happening This Weekend</h2>
            </div>
            <a href="{{ route('events.index', ['when' => 'this_weekend']) }}" class="btn-ghost text-sm py-2 px-4">View all</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($thisWeekendEvents as $event)
                @include('partials.event-card', ['event' => $event])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     UPCOMING EVENTS
═══════════════════════════════════════════════ --}}
@if ($upcomingEvents->isNotEmpty())
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold">Upcoming Events</h2>
            <a href="{{ route('events.index') }}" class="btn-ghost text-sm py-2 px-4">See all events</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($upcomingEvents as $event)
                @include('partials.event-card', ['event' => $event])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════════════ --}}
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="section-heading text-4xl mb-4">How It Works</h2>
            <p style="color:var(--color-brand-muted)">From browse to venue in under 60 seconds</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            {{-- Connector line --}}
            <div class="hidden md:block absolute top-12 left-1/3 right-1/3 h-px"
                 style="background:linear-gradient(90deg, transparent, var(--color-brand-primary), transparent)"></div>

            @foreach([
                [
                    'step' => '01',
                    'title' => 'Find Your Event',
                    'desc'  => 'Browse hundreds of events by category, date, or location. Filter by price, vibe, and more.',
                    'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
                ],
                [
                    'step' => '02',
                    'title' => 'Pick Your Tickets',
                    'desc'  => 'Select ticket type and quantity. Reserve your spot with a 15-minute hold while you pay.',
                    'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>',
                ],
                [
                    'step' => '03',
                    'title' => 'Pay with M-Pesa',
                    'desc'  => 'One tap M-Pesa STK push. Confirm on your phone and your QR ticket lands in seconds.',
                    'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
                ],
            ] as $step)
            <div class="relative text-center p-8 rounded-2xl"
                 style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
                     style="background:rgba(124,58,237,0.12); color:#9D5EF0">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $step['svg'] !!}
                    </svg>
                </div>
                <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full flex items-center justify-center text-xs font-black"
                     style="background:var(--color-brand-primary); color:white">{{ $step['step'] }}</div>
                <h3 class="text-lg font-bold mb-2">{{ $step['title'] }}</h3>
                <p class="text-sm leading-relaxed" style="color:var(--color-brand-muted)">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     HOST AN EVENT CTA
═══════════════════════════════════════════════ --}}
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl p-10 md:p-16 text-center"
             style="background:linear-gradient(135deg, rgba(124,58,237,0.2) 0%, rgba(240,196,39,0.1) 100%); border:1px solid rgba(124,58,237,0.3)">

            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10 blur-3xl"
                     style="background:radial-gradient(circle, #F0C427, transparent)"></div>
            </div>

            <div class="relative z-10">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6"
                     style="background:rgba(124,58,237,0.2); color:#9D5EF0; border:1px solid rgba(124,58,237,0.4)">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h2 class="section-heading text-4xl md:text-5xl mb-4">
                    Got an Event?<br><span class="gradient-text">Plug In.</span>
                </h2>
                <p class="text-lg mb-8 max-w-xl mx-auto" style="color:var(--color-brand-muted)">
                    Create your event in minutes. Sell tickets, track attendance, manage payouts — all in one dashboard. Zero setup fee.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}"
                       class="btn-primary text-base inline-flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        Get Started Free
                    </a>
                    <a href="{{ route('events.index') }}"
                       class="btn-ghost text-base inline-flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Browse Events
                    </a>
                </div>
                <p class="text-xs mt-6" style="color:var(--color-brand-subtle)">
                    Only {{ config('tickoplug.platform_fee_percentage', 5) }}% platform fee per ticket sold. No monthly fees.
                </p>
            </div>
        </div>
    </div>
</section>

@endsection
