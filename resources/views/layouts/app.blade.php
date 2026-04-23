<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#080811">

    {{-- ═══════ Core SEO ═══════ --}}
    <title>@yield('title', 'Ticko-Plug') — Plug Into The Vibe</title>
    <meta name="description" content="@yield('description', 'Buy and sell event tickets in Kenya. Concerts, comedy, sports, festivals and more on Ticko-Plug.')">
    <meta name="keywords" content="@yield('keywords', 'events Kenya, Nairobi tickets, concerts, comedy shows, festival tickets, sports tickets, Ticko-Plug')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- ═══════ Open Graph ═══════ --}}
    <meta property="og:site_name" content="Ticko-Plug">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'Ticko-Plug') — Plug Into The Vibe">
    <meta property="og:description" content="@yield('description', 'Buy and sell event tickets in Kenya.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="en_KE">

    {{-- ═══════ Twitter / X Cards ═══════ --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@tickoplug">
    <meta name="twitter:title" content="@yield('title', 'Ticko-Plug') — Plug Into The Vibe">
    <meta name="twitter:description" content="@yield('description', 'Buy and sell event tickets in Kenya.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">

    {{-- ═══════ JSON-LD — default WebSite schema (always present) ═══════ --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "Ticko-Plug",
        "url": "{{ url('/') }}",
        "description": "Kenya's freshest events ticketing platform.",
        "potentialAction": {
            "@type": "SearchAction",
            "target": { "@type": "EntryPoint", "urlTemplate": "{{ url('/search') }}?q={search_term_string}" },
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    {{-- Page-specific JSON-LD (Event schema etc.) pushed via @push('schema') in child views --}}
    @stack('schema')

    {{-- ═══════ Fonts: DM Sans ═══════ --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet">

    {{-- ═══════ Favicon ═══════ --}}
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

    {{-- ═══════ Styles ═══════ --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire --}}
    @livewireStyles

    {{-- Page specific styles --}}
    @stack('styles')
</head>
<body class="antialiased min-h-screen flex flex-col">

    {{-- Navigation --}}
    @include('layouts.partials.navbar')

    {{-- Flash Messages --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0"
             class="fixed top-20 right-4 z-50 flex items-center gap-2 px-4 py-3 rounded-xl shadow-xl"
             style="background:rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.4); color:#4ADE80;">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
            <button @click="show = false" class="ml-2 opacity-50 hover:opacity-100">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0"
             class="fixed top-20 right-4 z-50 flex items-center gap-2 px-4 py-3 rounded-xl shadow-xl"
             style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#F87171;">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
            <button @click="show = false" class="ml-2 opacity-50 hover:opacity-100">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('layouts.partials.footer')

    {{-- Livewire --}}
    @livewireScripts

    {{-- Page specific scripts --}}
    @stack('scripts')

</body>
</html>
