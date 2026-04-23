@extends('layouts.app')
@section('title', 'Your Details — ' . $event->title)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    @include('checkout.partials.steps', ['current' => 2])

    <div class="flex flex-col lg:flex-row gap-8 mt-8">

        {{-- ── LEFT: Buyer form ── --}}
        <div class="flex-1">
            <h1 class="text-2xl font-bold mb-6">Your Details</h1>

            @if ($errors->any())
                <div class="p-4 rounded-xl mb-6" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3)">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm" style="color:#EF4444">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('checkout.details.store', $event) }}" method="POST">
                @csrf

                <div class="space-y-5 p-6 rounded-2xl mb-6" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
                    <div>
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="buyer_name" class="form-input"
                               value="{{ old('buyer_name', auth()->user()?->name) }}"
                               placeholder="John Kamau" required autocomplete="name">
                    </div>

                    <div>
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="buyer_email" class="form-input"
                               value="{{ old('buyer_email', auth()->user()?->email) }}"
                               placeholder="john@example.com" required autocomplete="email">
                        <p class="text-xs mt-1.5" style="color:var(--color-brand-subtle)">Your ticket will be sent to this email</p>
                    </div>

                    <div>
                        <label class="form-label">M-Pesa Phone Number *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium" style="color:var(--color-brand-muted)">🇰🇪</span>
                            <input type="tel" name="buyer_phone" class="form-input pl-10"
                                   value="{{ old('buyer_phone', auth()->user()?->phone) }}"
                                   placeholder="0712 345 678" required autocomplete="tel">
                        </div>
                        <p class="text-xs mt-1.5" style="color:var(--color-brand-subtle)">You'll receive an M-Pesa payment prompt on this number</p>
                    </div>

                    {{-- Promo Code --}}
                    <div x-data="{ open: false }">
                        <button type="button" @click="open = !open"
                                class="text-sm font-medium flex items-center gap-1.5" style="color:var(--color-brand-primary)">
                            <span x-text="open ? '−' : '+'"></span>
                            <span>Have a promo code?</span>
                        </button>
                        <div x-show="open" x-cloak class="mt-3">
                            <div class="flex gap-2">
                                <input type="text" name="promo_code" class="form-input uppercase tracking-widest"
                                       value="{{ old('promo_code') }}"
                                       placeholder="ENTER CODE" maxlength="50">
                            </div>
                            @error('promo_code')
                                <p class="text-xs mt-1.5" style="color:#EF4444">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="p-5 rounded-2xl mb-6" style="background:var(--color-brand-elevated); border:1px solid var(--color-brand-border)">
                    <h3 class="font-semibold text-sm mb-4" style="color:var(--color-brand-muted)">Order Summary</h3>
                    <div class="space-y-2">
                        @foreach ($lineItems as $item)
                            <div class="flex items-center justify-between text-sm">
                                <span>{{ $item['type']->name }} × {{ $item['qty'] }}</span>
                                <span class="font-medium">
                                    {{ $item['type']->price == 0 ? 'Free' : 'KES ' . number_format($item['subtotal']) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t mt-3 pt-3 space-y-1.5" style="border-color:var(--color-brand-border)">
                        <div class="flex justify-between text-sm" style="color:var(--color-brand-muted)">
                            <span>Subtotal</span>
                            <span>KES {{ number_format($subtotal) }}</span>
                        </div>
                        @if ($platformFee > 0)
                            <div class="flex justify-between text-sm" style="color:var(--color-brand-muted)">
                                <span>Platform fee ({{ config('tickoplug.platform_fee_percentage', 5) }}%)</span>
                                <span>KES {{ number_format($platformFee) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-bold text-base pt-1">
                            <span>Total</span>
                            <span class="price-tag">KES {{ number_format($total) }}</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full justify-center text-base">
                    Continue → Payment
                </button>
            </form>
        </div>

        {{-- ── RIGHT: Summary ── --}}
        <div class="w-full lg:w-80 shrink-0">
            @include('checkout.partials.event-summary', ['event' => $event])
        </div>

    </div>
</div>
@endsection
