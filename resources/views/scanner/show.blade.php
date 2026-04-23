<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#080811">
    <title>Check-In Scanner — {{ $event->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #080811; color: #F8F8FF; overflow-x: hidden; }
        #scanner-video { width: 100%; height: 100%; object-fit: cover; }
        .scanner-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; }
        .scan-frame { width: 240px; height: 240px; position: relative; }
        .scan-frame::before, .scan-frame::after,
        .scan-frame span::before, .scan-frame span::after {
            content: ''; position: absolute; width: 28px; height: 28px; border-color: #7C3AED; border-style: solid;
        }
        .scan-frame::before  { top: 0;    left: 0;   border-width: 3px 0 0 3px; }
        .scan-frame::after   { top: 0;    right: 0;  border-width: 3px 3px 0 0; }
        .scan-frame span::before { bottom: 0; left: 0;   border-width: 0 0 3px 3px; }
        .scan-frame span::after  { bottom: 0; right: 0;  border-width: 0 3px 3px 0; }
        .scan-line {
            position: absolute; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, #7C3AED, transparent);
            animation: scanLine 2s ease-in-out infinite;
        }
        @keyframes scanLine { 0%, 100% { top: 5px; } 50% { top: 225px; } }
    </style>
</head>
<body x-data="scanner({{ $event->id }}, '{{ route('api.checkin.scan') }}')" x-init="init()">

{{-- ═══ TOP BAR ═══ --}}
<div class="flex items-center justify-between px-4 py-3 border-b" style="background:rgba(8,8,17,0.95); border-color:var(--color-brand-border)">
    <div>
        <p class="font-bold text-sm leading-tight">{{ $event->title }}</p>
        <p class="text-xs" style="color:var(--color-brand-muted)">📅 {{ $event->start_datetime->format('M j · g:i A') }}</p>
    </div>
    <div class="text-right">
        <p class="text-xs font-bold" style="color:var(--color-brand-muted)">Checked In</p>
        <p class="font-black text-lg" style="color:var(--color-brand-success)">
            <span x-text="stats.checked_in">{{ $stats['checked_in'] }}</span>/<span x-text="stats.total_tickets">{{ $stats['total_tickets'] }}</span>
        </p>
    </div>
</div>

{{-- ═══ SCANNER VIEWPORT ═══ --}}
<div class="relative w-full" style="height: calc(100vh - 180px); background:#000; overflow:hidden">

    {{-- Camera feed --}}
    <video id="scanner-video" autoplay playsinline muted></video>

    {{-- Scan frame overlay --}}
    <div class="scanner-overlay">
        <div class="scan-frame" :class="{ 'opacity-0': !cameraActive }">
            <span></span>
            <div class="scan-line"></div>
        </div>
    </div>

    {{-- Camera off / permission state --}}
    <div x-show="!cameraActive" x-cloak
         class="absolute inset-0 flex flex-col items-center justify-center gap-4 px-8 text-center">
        <div class="text-5xl">📷</div>
        <h3 class="font-bold text-lg">Camera Required</h3>
        <p class="text-sm" style="color:var(--color-brand-muted)">Allow camera access to scan QR codes</p>
        <button @click="startCamera()" class="btn-primary text-sm py-2.5 px-6">Enable Camera</button>
    </div>

    {{-- RESULT OVERLAY ═══ --}}
    <div x-show="result" x-cloak
         class="absolute inset-0 flex flex-col items-center justify-center px-6"
         :style="result?.success ? 'background:rgba(8,8,17,0.96)' : 'background:rgba(8,8,17,0.96)'"
         :class="result?.success ? 'scan-success' : 'scan-error'">

        <template x-if="result">
            <div class="text-center w-full max-w-sm">
                <div class="text-7xl mb-4" x-text="result.success ? '✅' : '❌'"></div>

                <div x-show="result.success" class="mb-4">
                    <h2 class="text-2xl font-black mb-1" style="color:var(--color-brand-success)">Check-In OK!</h2>
                    <p class="text-xl font-bold" x-text="result.ticket?.attendee_name"></p>
                    <p class="text-sm mt-1 px-3 py-1 rounded-full inline-block"
                       style="background:rgba(34,197,94,0.15); color:#22C55E; border:1px solid rgba(34,197,94,0.3)"
                       x-text="result.ticket?.ticket_type"></p>
                </div>

                <div x-show="!result.success" class="mb-4">
                    <h2 class="text-2xl font-black mb-2" style="color:var(--color-brand-danger)">Access Denied</h2>
                    <p class="text-base" x-text="result.message"></p>
                    <template x-if="result.ticket?.attendee_name">
                        <p class="text-sm mt-2" style="color:var(--color-brand-muted)">
                            Ticket holder: <strong x-text="result.ticket.attendee_name"></strong>
                        </p>
                    </template>
                </div>

                <div class="flex gap-3 mt-6 justify-center">
                    <button @click="resetScanner()" class="btn-primary px-8">
                        Scan Next
                    </button>
                    <button @click="manualEntry = true; result = null" class="btn-ghost px-5 text-sm">
                        Manual
                    </button>
                </div>
            </div>
        </template>
    </div>

</div>

{{-- ═══ BOTTOM BAR ═══ --}}
<div class="px-4 py-3 flex items-center justify-between border-t" style="background:rgba(8,8,17,0.95); border-color:var(--color-brand-border)">

    {{-- Progress bar --}}
    <div class="flex-1 mr-4">
        <div class="flex items-center justify-between text-xs mb-1" style="color:var(--color-brand-muted)">
            <span x-text="stats.percentage + '%'">{{ $stats['percentage'] }}%</span>
            <span x-text="stats.remaining + ' remaining'">{{ $stats['remaining'] }} remaining</span>
        </div>
        <div class="w-full rounded-full h-2" style="background:var(--color-brand-elevated)">
            <div class="h-2 rounded-full transition-all duration-500"
                 style="background:linear-gradient(90deg, #7C3AED, #22C55E); width: {{ $stats['percentage'] }}%"
                 :style="'width: ' + stats.percentage + '%'"></div>
        </div>
    </div>

    {{-- Manual entry button --}}
    <button @click="manualEntry = !manualEntry" class="btn-ghost text-sm py-2 px-4 shrink-0">
        ✏️ Manual
    </button>
</div>

{{-- ═══ MANUAL ENTRY MODAL ═══ --}}
<div x-show="manualEntry" x-cloak
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center px-4 pb-4"
     style="background:rgba(0,0,0,0.8)">
    <div class="w-full max-w-sm p-6 rounded-2xl" style="background:var(--color-brand-surface); border:1.5px solid var(--color-brand-border)">
        <h3 class="font-bold text-lg mb-4">Manual Ticket Lookup</h3>
        <input type="text" x-model="manualCode" @keyup.enter="scanManual()"
               placeholder="Enter ticket number (TK-XXXXXXXX)"
               class="form-input mb-4 uppercase tracking-widest text-sm"
               style="font-family:monospace">
        <div class="flex gap-3">
            <button @click="scanManual()" class="btn-primary flex-1 justify-center text-sm">Check In</button>
            <button @click="manualEntry = false; manualCode = ''" class="btn-ghost flex-1 justify-center text-sm">Cancel</button>
        </div>
    </div>
</div>

{{-- Loading state --}}
<div x-show="scanning" x-cloak
     class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-40 px-6 py-4 rounded-2xl"
     style="background:var(--color-brand-elevated); border:1px solid var(--color-brand-border)">
    <div class="flex items-center gap-3">
        <div class="w-4 h-4 rounded-full animate-spin" style="border:2px solid var(--color-brand-border); border-top-color:var(--color-brand-primary)"></div>
        <span class="text-sm font-medium">Verifying…</span>
    </div>
</div>

<script>
function scanner(eventId, apiUrl) {
    return {
        cameraActive: false,
        scanning: false,
        result: null,
        manualEntry: false,
        manualCode: '',
        lastScanned: null,
        stats: {
            checked_in: {{ $stats['checked_in'] }},
            total_tickets: {{ $stats['total_tickets'] }},
            remaining: {{ $stats['remaining'] }},
            percentage: {{ $stats['percentage'] }},
        },
        videoStream: null,
        animFrameId: null,

        async init() {
            await this.startCamera();
        },

        async startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
                });
                const video = document.getElementById('scanner-video');
                video.srcObject = stream;
                this.videoStream = stream;
                this.cameraActive = true;
                video.addEventListener('loadedmetadata', () => this.startScanning(video));
            } catch (err) {
                console.error('Camera error:', err);
                this.cameraActive = false;
            }
        },

        startScanning(video) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });

            const tick = () => {
                if (!this.cameraActive || this.scanning || this.result || this.manualEntry) {
                    this.animFrameId = requestAnimationFrame(tick);
                    return;
                }
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.height = video.videoHeight;
                    canvas.width  = video.videoWidth;
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

                    // Use BarcodeDetector API if available (fast, native)
                    if ('BarcodeDetector' in window) {
                        new BarcodeDetector({ formats: ['qr_code'] })
                            .detect(canvas)
                            .then(barcodes => {
                                if (barcodes.length > 0) {
                                    const code = barcodes[0].rawValue;
                                    if (code !== this.lastScanned) {
                                        this.lastScanned = code;
                                        this.processCode(code);
                                    }
                                }
                            });
                    } else {
                        // Fallback: jsQR (loaded below)
                        if (window.jsQR) {
                            const code = jsQR(imageData.data, canvas.width, canvas.height);
                            if (code && code.data !== this.lastScanned) {
                                this.lastScanned = code.data;
                                this.processCode(code.data);
                            }
                        }
                    }
                }
                this.animFrameId = requestAnimationFrame(tick);
            };
            tick();
        },

        async processCode(secret) {
            if (this.scanning || secret.length !== 64) return;
            this.scanning = true;
            navigator.vibrate?.(50);

            try {
                const res = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                        'X-Device-Token': this.getDeviceToken(),
                    },
                    body: JSON.stringify({ secret, event_id: eventId }),
                });

                const data = await res.json();
                this.result = data;

                if (data.success) {
                    this.stats.checked_in++;
                    this.stats.remaining = Math.max(0, this.stats.remaining - 1);
                    this.stats.percentage = this.stats.total_tickets > 0
                        ? parseFloat((this.stats.checked_in / this.stats.total_tickets * 100).toFixed(1))
                        : 0;
                    navigator.vibrate?.([100, 50, 100]);
                } else {
                    navigator.vibrate?.([300]);
                }

                // Auto-dismiss after 4 seconds
                setTimeout(() => this.resetScanner(), 4000);

            } catch (err) {
                console.error('Scan error:', err);
                this.result = { success: false, message: '⚠️ Network error. Check connection.' };
                setTimeout(() => this.resetScanner(), 3000);
            } finally {
                this.scanning = false;
            }
        },

        async scanManual() {
            const code = this.manualCode.trim().toUpperCase();
            if (!code) return;

            // For manual entry, look up by ticket number instead of secret
            this.manualEntry = false;
            this.scanning = true;

            try {
                const res = await fetch('/api/checkin/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                    },
                    body: JSON.stringify({ ticket_number: code, event_id: eventId }),
                });
                const data = await res.json();
                this.result = data;
                setTimeout(() => this.resetScanner(), 4000);
            } finally {
                this.scanning = false;
                this.manualCode = '';
            }
        },

        resetScanner() {
            this.result = null;
            this.lastScanned = null;
        },

        getDeviceToken() {
            let token = localStorage.getItem('scanner_device_token');
            if (!token) {
                token = Math.random().toString(36).substring(2) + Date.now().toString(36);
                localStorage.setItem('scanner_device_token', token);
            }
            return token;
        },
    };
}
</script>

{{-- jsQR fallback for browsers without BarcodeDetector --}}
<script>
if (!('BarcodeDetector' in window)) {
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js';
    document.head.appendChild(s);
}
</script>

{{-- Alpine.js --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>
</html>
