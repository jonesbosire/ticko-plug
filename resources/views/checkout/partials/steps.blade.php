@php $steps = [1 => 'Select', 2 => 'Details', 3 => 'Payment', 4 => 'Confirm']; @endphp
<div class="flex items-center justify-center gap-0">
    @foreach ($steps as $num => $label)
        @php $done = $num < $current; $active = $num === $current; @endphp
        <div class="flex items-center">
            <div class="flex flex-col items-center gap-1">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors"
                     style="{{ $done ? 'background:var(--color-brand-success); color:white' : ($active ? 'background:var(--color-brand-primary); color:white' : 'background:var(--color-brand-elevated); color:var(--color-brand-subtle); border:1px solid var(--color-brand-border)') }}">
                    @if ($done) ✓ @else {{ $num }} @endif
                </div>
                <span class="text-xs font-medium hidden sm:block"
                      style="color:{{ $active ? 'var(--color-brand-text)' : ($done ? 'var(--color-brand-success)' : 'var(--color-brand-subtle)') }}">
                    {{ $label }}
                </span>
            </div>
            @unless ($num === count($steps))
                <div class="w-16 sm:w-24 h-px mx-2 mb-5"
                     style="background:{{ $done ? 'var(--color-brand-success)' : 'var(--color-brand-border)' }}"></div>
            @endunless
        </div>
    @endforeach
</div>
