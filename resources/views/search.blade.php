@extends('layouts.app')

@section('title', $query ? 'Search: ' . $query : 'Search Events')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Search bar --}}
    <div class="max-w-2xl mb-10">
        <h1 class="text-3xl font-bold mb-6">Search Events</h1>
        <form action="{{ route('search') }}" method="GET">
            <div class="flex gap-2 p-2 rounded-2xl" style="background:var(--color-brand-elevated); border:1.5px solid var(--color-brand-border)">
                <div class="flex items-center gap-3 flex-1 px-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--color-brand-muted)">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" value="{{ $query }}" placeholder="Search events, artists, venues…"
                           class="bg-transparent flex-1 outline-none text-base"
                           style="color:var(--color-brand-text); font-family:var(--font-sans)"
                           autofocus autocomplete="off">
                </div>
                <button type="submit" class="btn-primary shrink-0 py-3 px-6">Search</button>
            </div>
        </form>
    </div>

    {{-- Results --}}
    @if (strlen($query) < 2)
        <div class="text-center py-16">
            <div class="text-6xl mb-4">🔍</div>
            <p style="color:var(--color-brand-muted)">Type at least 2 characters to search</p>
        </div>
    @elseif ($events instanceof \Illuminate\Support\Collection ? $events->isEmpty() : $events->isEmpty())
        <div class="text-center py-16">
            <div class="text-6xl mb-4">🤷</div>
            <h3 class="text-xl font-bold mb-2">No results for "{{ $query }}"</h3>
            <p class="mb-6" style="color:var(--color-brand-muted)">Try different keywords or browse all events.</p>
            <a href="{{ route('events.index') }}" class="btn-primary">Browse All Events</a>
        </div>
    @else
        @php $total = $events instanceof \Illuminate\Pagination\LengthAwarePaginator ? $events->total() : $events->count(); @endphp
        <p class="text-sm mb-6" style="color:var(--color-brand-muted)">
            {{ number_format($total) }} {{ Str::plural('result', $total) }} for "<strong class="text-white">{{ $query }}</strong>"
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($events as $event)
                @include('partials.event-card', ['event' => $event])
            @endforeach
        </div>

        @if ($events instanceof \Illuminate\Pagination\LengthAwarePaginator && $events->hasPages())
            <div class="mt-10">
                {{ $events->links('partials.pagination') }}
            </div>
        @endif
    @endif

</div>

@endsection
