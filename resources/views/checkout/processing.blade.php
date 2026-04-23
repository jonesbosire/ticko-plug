@extends('layouts.app')
@section('title', 'Processing Payment…')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-20"
     x-data="paymentPoller('{{ $order->order_number }}', '{{ route('orders.confirmation', $order) }}')"
     x-init="startPolling()">

    <div class="w-full max-w-md text-center">

        {{-- Animated icon --}}
        <div class="relative w-24 h-24 mx-auto mb-8">
            {{-- Outer ring --}}
            <div class="absolute inset-0 rounded-full border-4 mpesa-pulse"
                 style="border-color:rgba(0,159,68,0.3)"></div>
            {{-- Inner circle --}}
            <div class="absolute inset-2 rounded-full flex items-center justify-center text-4xl"
                 style="background:rgba(0,159,68,0.15)">
                <span x-show="status === 'pending' || status === 'processing'" class="mpesa-pulse">📱</span>
                <span x-show="status === 'paid'" x-cloak class="scan-success">✅</span>
                <span x-show="status === 'failed'" x-cloak>❌</span>
            </div>
        </div>

        {{-- Status text --}}
        <div x-show="status === 'pending' || status === 'processing'">
            <h1 class="text-2xl font-bold mb-3">Waiting for M-Pesa…</h1>
            <p class="mb-2" style="color:var(--color-brand-muted)">
                Check your phone for the M-Pesa payment prompt and enter your PIN.
            </p>
            <p class="text-sm" style="color:var(--color-brand-subtle)">
                Order: <strong class="text-white">{{ $order->order_number }}</strong>
            </p>
            <div class="flex items-center justify-center gap-1.5 mt-6" style="color:var(--color-brand-muted)">
                <span class="w-2 h-2 rounded-full animate-bounce" style="background:var(--color-brand-primary); animation-delay:0s"></span>
                <span class="w-2 h-2 rounded-full animate-bounce" style="background:var(--color-brand-primary); animation-delay:0.15s"></span>
                <span class="w-2 h-2 rounded-full animate-bounce" style="background:var(--color-brand-primary); animation-delay:0.3s"></span>
            </div>
        </div>

        <div x-show="status === 'paid'" x-cloak>
            <h1 class="text-2xl font-bold mb-3" style="color:var(--color-brand-success)">Payment Confirmed! 🎉</h1>
            <p style="color:var(--color-brand-muted)">Redirecting to your tickets…</p>
        </div>

        <div x-show="status === 'failed'" x-cloak>
            <h1 class="text-2xl font-bold mb-3" style="color:var(--color-brand-danger)">Payment Failed</h1>
            <p class="mb-6" style="color:var(--color-brand-muted)">Something went wrong. You have not been charged.</p>
            <a href="{{ url()->previous() }}" class="btn-primary">Try Again</a>
        </div>

        {{-- Timeout message --}}
        <div x-show="timedOut" x-cloak class="mt-6 p-4 rounded-xl"
             style="background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3)">
            <p class="text-sm font-semibold mb-1" style="color:#F59E0B">Taking longer than expected?</p>
            <p class="text-xs mb-3" style="color:var(--color-brand-muted)">
                If you completed the M-Pesa payment, your tickets will be delivered shortly. Check your email or
                <a href="{{ route('orders.index') }}" style="color:var(--color-brand-primary)">view your orders</a>.
            </p>
            <button @click="checkNow()" class="btn-ghost text-xs py-2 px-4">Check again</button>
        </div>

    </div>
</div>

@push('scripts')
<script>
function paymentPoller(orderNumber, confirmationUrl) {
    return {
        status: '{{ $order->status->value }}',
        timedOut: false,
        pollInterval: null,
        pollCount: 0,
        maxPolls: 40, // ~2 minutes at 3s intervals

        startPolling() {
            if (this.status === 'paid') {
                window.location.href = confirmationUrl;
                return;
            }
            this.pollInterval = setInterval(() => this.poll(), 3000);
        },

        async poll() {
            this.pollCount++;
            if (this.pollCount > this.maxPolls) {
                clearInterval(this.pollInterval);
                this.timedOut = true;
                return;
            }
            try {
                const res = await fetch(`/api/orders/${orderNumber}/status`);
                const data = await res.json();
                this.status = data.status;
                if (data.status === 'paid') {
                    clearInterval(this.pollInterval);
                    setTimeout(() => window.location.href = confirmationUrl, 1200);
                } else if (data.status === 'failed' || data.status === 'cancelled') {
                    clearInterval(this.pollInterval);
                }
            } catch (e) {
                // Network error — keep polling
            }
        },

        async checkNow() {
            this.timedOut = false;
            this.pollCount = 0;
            clearInterval(this.pollInterval);
            await this.poll();
            if (this.status !== 'paid' && this.status !== 'failed') {
                this.pollInterval = setInterval(() => this.poll(), 3000);
            }
        },
    };
}
</script>
@endpush

@endsection
