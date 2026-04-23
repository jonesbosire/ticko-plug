<nav class="sticky top-0 z-40 border-b" style="background:rgba(8,8,17,0.85); backdrop-filter:blur(16px); border-color:rgba(42,42,64,0.8);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('images/logo.svg') }}" alt="Ticko-Plug" class="h-8 w-auto">
            </a>

            {{-- Center Nav --}}
            <div class="hidden md:flex items-center gap-6">
                <a href="{{ route('events.index') }}" class="nav-link text-sm">Browse Events</a>
                <a href="{{ route('search') }}" class="nav-link text-sm">Search</a>
            </div>

            {{-- Right --}}
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('orders.index') }}" class="nav-link text-sm hidden md:block">My Tickets</a>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm" style="color:var(--color-brand-muted)">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt=""
                                     class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm"
                                     style="background:var(--color-brand-primary)">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </button>

                        <div x-show="open" @click.outside="open = false" x-cloak
                             class="absolute right-0 mt-2 w-52 card py-1 shadow-xl z-50">
                            <div class="px-4 py-2 border-b" style="border-color:var(--color-brand-border)">
                                <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs truncate" style="color:var(--color-brand-muted)">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('orders.index') }}"
                               class="block px-4 py-2 text-sm nav-link hover:bg-white/5">
                                My Tickets
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-sm hover:bg-white/5"
                                        style="color:var(--color-brand-danger)">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="nav-link text-sm">Sign In</a>
                    <a href="{{ route('register') }}" class="btn-primary text-sm py-2 px-4">Get Started</a>
                @endauth

                {{-- Mobile menu toggle --}}
                <button class="md:hidden p-2 rounded-lg" style="color:var(--color-brand-muted)"
                        x-data x-on:click="$dispatch('toggle-mobile-menu')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>
