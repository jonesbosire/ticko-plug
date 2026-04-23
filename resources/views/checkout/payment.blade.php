@extends('layouts.app')
@section('title', 'Payment — ' . $event->title)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    @include('checkout.partials.steps', ['current' => 3])

    <div class="flex flex-col lg:flex-row gap-8 mt-8">

        {{-- ── LEFT: Payment Options ── --}}
        <div class="flex-1">
            <h1 class="text-2xl font-bold mb-2">Payment</h1>
            <p class="text-sm mb-6" style="color:var(--color-brand-muted)">
                Paying as <strong class="text-white">{{ $buyer['buyer_name'] }}</strong> · {{ $buyer['buyer_email'] }}
            </p>

            @if ($total == 0)
                {{-- Free ticket --}}
                <div class="p-6 rounded-2xl mb-6 text-center" style="background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3)">
                    <div class="text-4xl mb-3">🎟️</div>
                    <h3 class="font-bold text-lg mb-1">Free Ticket!</h3>
                    <p class="text-sm" style="color:var(--color-brand-muted)">No payment required for this event.</p>
                </div>
                <form action="{{ route('checkout.payment.mpesa', $event) }}" method="POST">
                    @csrf
                    <input type="hidden" name="payment_method" value="free">
                    <button type="submit" class="btn-primary w-full justify-center text-base">
                        🎟️ Claim Free Ticket
                    </button>
                </form>

            @else
                {{-- Payment Method Tabs --}}
                <div x-data="{ method: 'mpesa' }">

                    {{-- Tab buttons --}}
                    <div class="flex gap-3 mb-6">
                        <button type="button" @click="method = 'mpesa'"
                                :class="method === 'mpesa' ? 'border-purple-500 bg-purple-500/10' : ''"
                                class="flex items-center gap-2.5 px-5 py-3 rounded-xl border text-sm font-semibold transition-all"
                                style="border-color:var(--color-brand-border)">
                            <span class="text-lg">📱</span> M-Pesa
                        </button>
                        <button type="button" @click="method = 'card'"
                                :class="method === 'card' ? 'border-purple-500 bg-purple-500/10' : ''"
                                class="flex items-center gap-2.5 px-5 py-3 rounded-xl border text-sm font-semibold transition-all"
                                style="border-color:var(--color-brand-border)">
                            <span class="text-lg">💳</span> Card
                        </button>
                    </div>

                    {{-- M-Pesa Form --}}
                    <div x-show="method === 'mpesa'" x-cloak>
                        <div class="p-6 rounded-2xl mb-6" style="background:var(--color-brand-surface); border:1.5px solid rgba(0,159,68,0.4)">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl"
                                     style="background:rgba(0,159,68,0.15)">📱</div>
                                <div>
                                    <p class="font-bold">Pay with M-Pesa</p>
                                    <p class="text-xs" style="color:var(--color-brand-muted)">You'll receive a payment prompt on your phone</p>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl mb-5" style="background:var(--color-brand-elevated); border:1px solid var(--color-brand-border)">
                                <p class="text-xs mb-1" style="color:var(--color-brand-muted)">Payment will be sent to:</p>
                                <p class="font-bold text-lg">{{ $displayPhone }}</p>
                                <p class="text-xs mt-1" style="color:var(--color-brand-subtle)">Make sure this number has enough M-Pesa balance</p>
                            </div>

                            <div class="flex items-start gap-3 p-3 rounded-xl mb-5" style="background:rgba(240,196,39,0.08); border:1px solid rgba(240,196,39,0.2)">
                                <span class="text-sm shrink-0">💡</span>
                                <p class="text-xs leading-relaxed" style="color:var(--color-brand-muted)">
                                    After clicking "Pay Now", check your phone for an M-Pesa STK Push prompt. Enter your M-Pesa PIN to complete payment. Do <strong class="text-white">not</strong> close this page.
                                </p>
                            </div>

                            <form action="{{ route('checkout.payment.mpesa', $event) }}" method="POST" id="mpesa-form">
                                @csrf
                                <input type="hidden" name="payment_method" value="mpesa">

                                <button type="submit" class="btn-primary w-full justify-center text-base"
                                        x-on:click="$el.innerHTML = '📱 Sending prompt…'; $el.disabled = true; document.getElementById('mpesa-form').submit()">
                                    Pay KES {{ number_format($total) }} via M-Pesa
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Card Form --}}
                    <div x-show="method === 'card'" x-cloak>
                        <div class="p-6 rounded-2xl mb-6" style="background:var(--color-brand-surface); border:1.5px solid rgba(99,102,241,0.4)">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl"
                                     style="background:rgba(99,102,241,0.15)">💳</div>
                                <div>
                                    <p class="font-bold">Pay with Card</p>
                                    <p class="text-xs" style="color:var(--color-brand-muted)">Visa, Mastercard — secured by Flutterwave</p>
                                </div>
                            </div>

                            <form action="{{ route('checkout.payment.card', $event) }}" method="POST">
                                @csrf
                                <input type="hidden" name="payment_method" value="card">
                                <button type="submit" class="btn-primary w-full justify-center text-base">
                                    Pay KES {{ number_format($total) }} by Card →
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            @endif

            {{-- Security note --}}
            <div class="flex items-center gap-2 mt-4 justify-center text-xs" style="color:var(--color-brand-subtle)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Secure &amp; encrypted checkout · Powered by Ticko-Plug
            </div>
        </div>

        {{-- ── RIGHT: Order summary ── --}}
        <div class="w-full lg:w-80 shrink-0 space-y-4">

            @include('checkout.partials.event-summary', ['event' => $event])

            <div class="p-5 rounded-2xl" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">
                <h3 class="font-semibold text-sm mb-3" style="color:var(--color-brand-muted)">Order Summary</h3>
                <div class="space-y-1.5 text-sm">
                    @foreach ($lineItems as $item)
                        <div class="flex justify-between">
                            <span style="color:var(--color-brand-muted)">{{ $item['type']->name }} × {{ $item['qty'] }}</span>
                            <span>{{ $item['type']->price == 0 ? 'Free' : 'KES ' . number_format($item['subtotal']) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="border-t mt-3 pt-3 space-y-1.5 text-sm" style="border-color:var(--color-brand-border)">
                    @if ($discount > 0)
                        <div class="flex justify-between" style="color:var(--color-brand-success)">
                            <span>Discount ({{ $promo->code }})</span>
                            <span>− KES {{ number_format($discount) }}</span>
                        </div>
                    @endif
                    @if ($platformFee > 0)
                        <div class="flex justify-between" style="color:var(--color-brand-muted)">
                            <span>Platform fee</span>
                            <span>KES {{ number_format($platformFee) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-bold text-base pt-1">
                        <span>Total</span>
                        <span class="price-tag">KES {{ number_format($total) }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
