<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') — Ticko-Plug</title>
    <meta name="theme-color" content="#080811">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,600;9..40,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen flex items-center justify-center" style="background:#080811; font-family:'DM Sans', sans-serif;">

    {{-- Background glows --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full opacity-10 blur-3xl"
             style="background:radial-gradient(circle, #7C3AED, transparent)"></div>
        <div class="absolute bottom-1/4 right-1/4 w-64 h-64 rounded-full opacity-8 blur-3xl"
             style="background:radial-gradient(circle, #F0C427, transparent)"></div>
    </div>

    <div class="relative z-10 text-center px-6 max-w-lg mx-auto">

        {{-- Code badge --}}
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-8"
             style="background:rgba(124,58,237,0.15); border:2px solid rgba(124,58,237,0.3);">
            <span class="text-4xl font-black" style="color:#9D5EF0;">@yield('code')</span>
        </div>

        <h1 class="text-4xl md:text-5xl font-black mb-4" style="color:#F1F0FF;">
            @yield('headline')
        </h1>

        <p class="text-lg mb-8" style="color:#6B7280;">
            @yield('message')
        </p>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="btn-primary px-6 py-3 rounded-xl font-semibold">
                Back to Home
            </a>
            <a href="{{ url('/events') }}" class="btn-ghost px-6 py-3 rounded-xl font-semibold">
                Browse Events
            </a>
        </div>

        {{-- Brand --}}
        <p class="mt-10 text-xs" style="color:#374151;">
            Ticko-Plug &mdash; Plug Into The Vibe
        </p>

    </div>

</body>
</html>
