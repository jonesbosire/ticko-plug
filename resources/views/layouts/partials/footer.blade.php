<footer class="border-t mt-20" style="border-color:var(--color-brand-border); background:var(--color-brand-surface)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

            {{-- Brand --}}
            <div class="col-span-1 md:col-span-2">
                <img src="{{ asset('images/logo.svg') }}" alt="Ticko-Plug" class="h-8 w-auto mb-4">
                <p class="text-sm leading-relaxed mb-4" style="color:var(--color-brand-muted)">
                    Kenya's freshest events ticketing platform. Plug into the vibe — concerts, comedy, sports, festivals and more.
                </p>
                <div class="flex gap-3">
                    <a href="#" aria-label="Instagram"
                       class="w-9 h-9 rounded-full flex items-center justify-center border transition-colors hover:border-purple-500 hover:text-purple-400"
                       style="border-color:var(--color-brand-border); color:var(--color-brand-muted)">
                        <span class="text-xs font-bold">IG</span>
                    </a>
                    <a href="#" aria-label="X / Twitter"
                       class="w-9 h-9 rounded-full flex items-center justify-center border transition-colors hover:border-purple-500 hover:text-purple-400"
                       style="border-color:var(--color-brand-border); color:var(--color-brand-muted)">
                        <span class="text-xs font-bold">TW</span>
                    </a>
                    <a href="#" aria-label="TikTok"
                       class="w-9 h-9 rounded-full flex items-center justify-center border transition-colors hover:border-purple-500 hover:text-purple-400"
                       style="border-color:var(--color-brand-border); color:var(--color-brand-muted)">
                        <span class="text-xs font-bold">TT</span>
                    </a>
                </div>
            </div>

            {{-- Discover --}}
            <div>
                <h4 class="font-semibold text-sm mb-4">Discover</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('events.index') }}" class="text-sm nav-link">Browse Events</a></li>
                    <li><a href="{{ route('events.index', ['category' => 'music']) }}" class="text-sm nav-link">Music & Concerts</a></li>
                    <li><a href="{{ route('events.index', ['category' => 'comedy']) }}" class="text-sm nav-link">Comedy</a></li>
                    <li><a href="{{ route('events.index', ['category' => 'sports']) }}" class="text-sm nav-link">Sports</a></li>
                    <li><a href="{{ route('events.index', ['category' => 'festivals']) }}" class="text-sm nav-link">Festivals</a></li>
                </ul>
            </div>

            {{-- Help --}}
            <div>
                <h4 class="font-semibold text-sm mb-4">Help</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-sm nav-link">How It Works</a></li>
                    <li><a href="#" class="text-sm nav-link">FAQs</a></li>
                    <li><a href="#" class="text-sm nav-link">Contact Support</a></li>
                    <li><a href="{{ route('login') }}" class="text-sm nav-link">Sign In</a></li>
                    <li><a href="{{ route('register') }}" class="text-sm nav-link">Create Account</a></li>
                </ul>
            </div>
        </div>

        <div class="divider mt-8 mb-6"></div>

        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-xs" style="color:var(--color-brand-subtle)">
                &copy; {{ date('Y') }} Ticko-Plug. All rights reserved.
            </p>
            <div class="flex gap-4">
                <a href="#" class="text-xs nav-link">Privacy Policy</a>
                <a href="#" class="text-xs nav-link">Terms of Service</a>
                <a href="#" class="text-xs nav-link">Support</a>
            </div>
        </div>
    </div>
</footer>
