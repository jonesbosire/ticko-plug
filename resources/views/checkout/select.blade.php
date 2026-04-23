@extends('layouts.app')
@section('title', 'Get Tickets — ' . $event->title)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Steps --}}
    @include('checkout.partials.steps', ['current' => 1])

    <div class="flex flex-col lg:flex-row gap-8 mt-8">

        {{-- ── LEFT: Ticket selection ── --}}
        <div class="flex-1">
            <h1 class="text-2xl font-bold mb-6">Select Tickets</h1>

            @if ($errors->any())
                <div class="p-4 rounded-xl mb-6" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3)">
                    <p class="text-sm font-semibold mb-1" style="color:#EF4444">⚠️ Please fix the following:</p>
                    @foreach ($errors->all() as $error)
                        <p class="text-sm" style="color:var(--color-brand-muted)">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('checkout.reserve', $event) }}" method="POST" id="ticket-form">
                @csrf

                <div class="space-y-4" x-data="ticketSelector()">
                    @foreach ($event->ticketTypes as $index => $type)
                    @php
                        $available = $type->quantity_total > 0 ? max(0, $type->quantity_total - $type->quantity_sold - $type->quantity_reserved) : PHP_INT_MAX;
                        $isSoldOut = $type->quantity_total > 0 && $available === 0;
                        $saleEnded = $type->sale_ends_at && $type->sale_ends_at->isPast();
                        $saleNotStarted = $type->sale_starts_at && $type->sale_starts_at->isFuture();
                        $unavailable = $isSoldOut || $saleEnded || $saleNotStarted;
                        $maxQty = min($type->max_per_order ?? 10, $type->quantity_total > 0 ? $available : 10);
                    @endphp

                    <div class="p-5 rounded-2xl transition-all"
                         style="background:var(--color-brand-surface); border:1.5px solid {{ $unavailable ? 'var(--color-brand-border)' : 'rgba(124,58,237,0.3)' }}; opacity:{{ $unavailable ? '0.5' : '1' }}">

                        <input type="hidden" name="tickets[{{ $index }}][type_id]" value="{{ $type->id }}">
                        <input type="hidden" name="tickets[{{ $index }}][qty]" :value="quantities[{{ $index }}]">

                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-bold text-base">{{ $type->name }}</h3>
                                    @if ($isSoldOut)
                                        <span class="badge badge-danger text-xs">Sold Out</span>
                                    @elseif ($saleNotStarted)
                                        <span class="badge badge-warning text-xs">Coming Soon</span>
                                    @elseif ($saleEnded)
                                        <span class="badge badge-danger text-xs">Sale Ended</span>
                                    @elseif ($type->quantity_total > 0 && $available <= 20)
                                        <span class="badge badge-warning text-xs">{{ $available }} left</span>
                                    @endif
                                </div>

                                @if ($type->description)
                                    <p class="text-sm mt-1" style="color:var(--color-brand-muted)">{{ $type->description }}</p>
                                @endif

                                @if ($saleNotStarted)
                                    <p class="text-xs mt-2" style="color:var(--color-brand-warning)">
                                        Sales open {{ $type->sale_starts_at->format('M j, Y g:i A') }}
                                    </p>
                                @elseif ($type->sale_ends_at && !$saleEnded)
                                    <p class="text-xs mt-2" style="color:var(--color-brand-subtle)">
                                        Sale ends {{ $type->sale_ends_at->format('M j, Y') }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center gap-4 shrink-0">
                                <span class="font-bold text-lg {{ $type->price == 0 ? 'price-tag free' : 'price-tag' }}">
                                    {{ $type->price == 0 ? 'FREE' : 'KES ' . number_format($type->price) }}
                                </span>

                                @unless ($unavailable)
                                    {{-- Quantity stepper --}}
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                @click="decrease({{ $index }})"
                                                :disabled="quantities[{{ $index }}] <= 0"
                                                class="w-8 h-8 rounded-lg font-bold text-lg transition-colors flex items-center justify-center"
                                                :style="quantities[{{ $index }}] <= 0
                                                    ? 'background:var(--color-brand-border); color:var(--color-brand-subtle); cursor:not-allowed'
                                                    : 'background:var(--color-brand-primary); color:white; cursor:pointer'">
                                            −
                                        </button>
                                        <span class="w-8 text-center font-bold text-lg" x-text="quantities[{{ $index }}]">0</span>
                                        <button type="button"
                                                @click="increase({{ $index }}, {{ $maxQty }})"
                                                :disabled="quantities[{{ $index }}] >= {{ $maxQty }}"
                                                class="w-8 h-8 rounded-lg font-bold text-lg transition-colors flex items-center justify-center"
                                                :style="quantities[{{ $index }}] >= {{ $maxQty }}
                                                    ? 'background:var(--color-brand-border); color:var(--color-brand-subtle); cursor:not-allowed'
                                                    : 'background:var(--color-brand-primary); color:white; cursor:pointer'">
                                            +
                                        </button>
                                    </div>
                                @endunless
                            </div>
                        </div>

                    </div>
                    @endforeach

                    {{-- Order Total --}}
                    <div class="p-5 rounded-2xl mt-2" style="background:var(--color-brand-elevated); border:1px solid var(--color-brand-border)"
                         x-show="totalQty > 0" x-cloak>
                        <div class="flex items-center justify-between">
                            <span style="color:var(--color-brand-muted)">
                                <span x-text="totalQty"></span> ticket<span x-show="totalQty !== 1">s</span>
                            </span>
                            <span class="font-bold price-tag">
                                KES <span x-text="totalAmount.toLocaleString()"></span>
                            </span>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <button type="submit" x-show="totalQty > 0" x-cloak
                            class="btn-primary w-full justify-center text-base mt-2">
                        Continue → Enter Details
                    </button>
                    <p x-show="totalQty === 0" class="text-center text-sm pt-4" style="color:var(--color-brand-muted)">
                        Select at least one ticket to continue
                    </p>

                </div>
            </form>
        </div>

        {{-- ── RIGHT: Event Summary ── --}}
        <div class="w-full lg:w-80 shrink-0">
            @include('checkout.partials.event-summary', ['event' => $event])
        </div>

    </div>
</div>

@push('scripts')
<script>
function ticketSelector() {
    return {
        quantities: @json(array_fill(0, $event->ticketTypes->count(), 0)),
        prices: @json($event->ticketTypes->pluck('price')->values()),

        get totalQty() {
            return this.quantities.reduce((a, b) => a + b, 0);
        },
        get totalAmount() {
            return this.quantities.reduce((sum, qty, i) => sum + qty * this.prices[i], 0);
        },
        increase(i, max) {
            if (this.quantities[i] < max) this.quantities.splice(i, 1, this.quantities[i] + 1);
        },
        decrease(i) {
            if (this.quantities[i] > 0) this.quantities.splice(i, 1, this.quantities[i] - 1);
        },
    };
}
</script>
@endpush

@endsection
