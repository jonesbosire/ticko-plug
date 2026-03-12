<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#080811">

    <title>@yield('title', 'Ticko-Plug') — Plug Into The Vibe</title>
    <meta name="description" content="@yield('description', 'Buy and sell event tickets in Kenya. Concerts, comedy, sports, festivals and more on Ticko-Plug.')">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('title', 'Ticko-Plug') — Plug Into The Vibe">
    <meta property="og:description" content="@yield('description', 'Buy and sell event tickets in Kenya.')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">

    {{-- Fonts: DM Sans from Google --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    {{-- Styles --}}
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
             class="fixed top-20 right-4 z-50 badge badge-success px-4 py-3 rounded-xl shadow-lg" style="background:rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.4);">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed top-20 right-4 z-50 px-4 py-3 rounded-xl shadow-lg" style="background:rgba(239,68,68,0.15); color:#EF4444; border:1px solid rgba(239,68,68,0.4);">
            ❌ {{ session('error') }}
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
