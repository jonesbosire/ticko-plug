@if ($paginator->hasPages())
<nav class="flex items-center justify-between gap-4 flex-wrap">

    {{-- Info --}}
    <p class="text-sm" style="color:var(--color-brand-muted)">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ number_format($paginator->total()) }}
    </p>

    {{-- Page links --}}
    <div class="flex items-center gap-1">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="w-9 h-9 rounded-xl flex items-center justify-center text-sm opacity-30 cursor-not-allowed"
                  style="background:var(--color-brand-elevated); color:var(--color-brand-muted)">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="w-9 h-9 rounded-xl flex items-center justify-center text-sm transition-colors"
               style="background:var(--color-brand-elevated); color:var(--color-brand-muted); border:1px solid var(--color-brand-border)"
               onmouseover="this.style.borderColor='var(--color-brand-primary)'; this.style.color='var(--color-brand-text)'"
               onmouseout="this.style.borderColor='var(--color-brand-border)'; this.style.color='var(--color-brand-muted)'">←</a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="w-9 h-9 flex items-center justify-center text-sm" style="color:var(--color-brand-subtle)">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold"
                              style="background:var(--color-brand-primary); color:white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           class="w-9 h-9 rounded-xl flex items-center justify-center text-sm transition-colors"
                           style="background:var(--color-brand-elevated); color:var(--color-brand-muted); border:1px solid var(--color-brand-border)"
                           onmouseover="this.style.borderColor='var(--color-brand-primary)'; this.style.color='var(--color-brand-text)'"
                           onmouseout="this.style.borderColor='var(--color-brand-border)'; this.style.color='var(--color-brand-muted)'">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="w-9 h-9 rounded-xl flex items-center justify-center text-sm transition-colors"
               style="background:var(--color-brand-elevated); color:var(--color-brand-muted); border:1px solid var(--color-brand-border)"
               onmouseover="this.style.borderColor='var(--color-brand-primary)'; this.style.color='var(--color-brand-text)'"
               onmouseout="this.style.borderColor='var(--color-brand-border)'; this.style.color='var(--color-brand-muted)'">→</a>
        @else
            <span class="w-9 h-9 rounded-xl flex items-center justify-center text-sm opacity-30 cursor-not-allowed"
                  style="background:var(--color-brand-elevated); color:var(--color-brand-muted)">→</span>
        @endif
    </div>

</nav>
@endif
