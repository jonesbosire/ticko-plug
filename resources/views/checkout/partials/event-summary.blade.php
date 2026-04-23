<div class="rounded-2xl overflow-hidden sticky top-24" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">

    {{-- Banner --}}
    @php $banner = $event->getFirstMediaUrl('banner') ?: $event->getFirstMediaUrl('images'); @endphp
    <div class="aspect-video overflow-hidden">
        @if ($banner)
            <img src="{{ $banner }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center text-5xl"
                 style="background:linear-gradient(135deg, {{ $event->category->color ?? '#7C3AED' }}33, #12121E)">🎪</div>
        @endif
    </div>

    <div class="p-5">
        <h3 class="font-bold text-base mb-3 leading-snug">{{ $event->title }}</h3>

        <div class="space-y-2.5 text-sm" style="color:var(--color-brand-muted)">
            <div class="flex items-center gap-2">
                <span>📅</span>
                <span>{{ $event->start_datetime->format('D, M j · g:i A') }}</span>
            </div>
            @if ($event->venue)
            <div class="flex items-center gap-2">
                <span>📍</span>
                <span>{{ $event->venue->name }}, {{ $event->venue->city }}</span>
            </div>
            @elseif ($event->is_online)
            <div class="flex items-center gap-2">
                <span>🌐</span><span>Online Event</span>
            </div>
            @endif
        </div>
    </div>
</div>
