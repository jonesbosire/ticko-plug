@extends('layouts.app')

@section('title', isset($category) ? $category->name . ' Events in Kenya' : 'All Events in Kenya')
@section('description', isset($category)
    ? 'Buy tickets for ' . $category->name . ' events in Kenya. Find upcoming shows on Ticko-Plug — Africa\'s freshest ticketing platform.'
    : 'Browse all upcoming events in Kenya — concerts, comedy, sports, festivals and more. Buy tickets securely on Ticko-Plug.')
@section('keywords', isset($category)
    ? $category->name . ' tickets Kenya, ' . $category->name . ' events Nairobi, Ticko-Plug'
    : 'events Kenya, Nairobi tickets, buy tickets online, concerts Kenya, Ticko-Plug')
@section('canonical', isset($category) ? route('events.category', $category) : route('events.index'))

@section('content')

{{-- Page Header --}}
<div class="py-12" style="background:var(--color-brand-surface); border-bottom:1px solid var(--color-brand-border)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (isset($category))
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background:{{ $category->color ?? '#7C3AED' }}22; color:{{ $category->color ?? '#7C3AED' }}">
                    <x-dynamic-component :component="$category->icon ?? 'heroicon-o-star'" class="w-5 h-5"/>
                </div>
                <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
            </div>
            <p style="color:var(--color-brand-muted)">{{ $category->description }}</p>
        @else
            <h1 class="text-3xl font-bold mb-1">All Events</h1>
            <p style="color:var(--color-brand-muted)">Discover upcoming experiences across Kenya</p>
        @endif
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ── SIDEBAR FILTERS ── --}}
        <aside class="w-full lg:w-64 shrink-0">
            <form id="filter-form" method="GET" action="{{ isset($category) ? route('events.category', $category) : route('events.index') }}">

                {{-- Active filters count --}}
                @php $activeFilters = collect(request()->only(['when','category','city','price','sort']))->filter()->count(); @endphp
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-sm uppercase tracking-widest" style="color:var(--color-brand-muted)">Filters</h3>
                    @if ($activeFilters > 0)
                        <a href="{{ request()->url() }}" class="text-xs" style="color:var(--color-brand-primary)">
                            Clear all ({{ $activeFilters }})
                        </a>
                    @endif
                </div>

                {{-- When --}}
                <div class="mb-6">
                    <label class="form-label">Date</label>
                    <div class="flex flex-col gap-1.5">
                        @foreach([
                            '' => 'Any time',
                            'today' => 'Today',
                            'tomorrow' => 'Tomorrow',
                            'this_weekend' => 'This Weekend',
                            'this_week' => 'This Week',
                            'this_month' => 'This Month',
                        ] as $val => $label)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="when" value="{{ $val }}"
                                       {{ request('when', '') === $val ? 'checked' : '' }}
                                       class="accent-purple-600"
                                       onchange="document.getElementById('filter-form').submit()">
                                <span class="text-sm group-hover:text-white transition-colors"
                                      style="color:{{ request('when', '') === $val ? 'var(--color-brand-text)' : 'var(--color-brand-muted)' }}">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Price --}}
                <div class="mb-6">
                    <label class="form-label">Price</label>
                    <div class="flex flex-col gap-1.5">
                        @foreach(['' => 'Any price', 'free' => 'Free', 'paid' => 'Paid'] as $val => $label)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="price" value="{{ $val }}"
                                       {{ request('price', '') === $val ? 'checked' : '' }}
                                       class="accent-purple-600"
                                       onchange="document.getElementById('filter-form').submit()">
                                <span class="text-sm group-hover:text-white transition-colors"
                                      style="color:{{ request('price', '') === $val ? 'var(--color-brand-text)' : 'var(--color-brand-muted)' }}">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Category (only show if not already on category page) --}}
                @unless (isset($category))
                <div class="mb-6">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-input text-sm py-2"
                            onchange="document.getElementById('filter-form').submit()">
                        <option value="">All categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endunless

                {{-- Sort --}}
                <div class="mb-6">
                    <label class="form-label">Sort by</label>
                    <select name="sort" class="form-input text-sm py-2"
                            onchange="document.getElementById('filter-form').submit()">
                        <option value="date" {{ request('sort', 'date') === 'date' ? 'selected' : '' }}>Date (soonest)</option>
                        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                        <option value="featured" {{ request('sort') === 'featured' ? 'selected' : '' }}>Featured First</option>
                    </select>
                </div>

            </form>
        </aside>

        {{-- ── EVENTS GRID ── --}}
        <div class="flex-1 min-w-0">

            {{-- Results count --}}
            <div class="flex items-center justify-between mb-6">
                <p class="text-sm" style="color:var(--color-brand-muted)">
                    @if ($events instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        {{ number_format($events->total()) }} {{ Str::plural('event', $events->total()) }} found
                    @else
                        {{ $events->count() }} {{ Str::plural('event', $events->count()) }} found
                    @endif
                </p>
            </div>

            @if ($events->isEmpty())
                <div class="text-center py-20">
                    <div class="text-6xl mb-4">🎪</div>
                    <h3 class="text-xl font-bold mb-2">No events found</h3>
                    <p class="mb-6" style="color:var(--color-brand-muted)">Try adjusting your filters or check back soon.</p>
                    <a href="{{ route('events.index') }}" class="btn-ghost text-sm">Clear filters</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach ($events as $event)
                        @include('partials.event-card', ['event' => $event])
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($events instanceof \Illuminate\Pagination\LengthAwarePaginator && $events->hasPages())
                    <div class="mt-10">
                        {{ $events->links('partials.pagination') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@endsection
