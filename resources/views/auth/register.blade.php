@extends('layouts.app')
@section('title', 'Create Account')
@section('robots', 'noindex, nofollow')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md">

        <div class="rounded-2xl p-8" style="background:var(--color-brand-surface); border:1px solid var(--color-brand-border)">

            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold mb-3"
                     style="background:rgba(124,58,237,0.15); border:1px solid rgba(124,58,237,0.4); color:#9D5EF0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    For Ticket Buyers
                </div>
                <h1 class="text-2xl font-bold mb-1">Create Your Account</h1>
                <p class="text-sm" style="color:var(--color-brand-muted)">Buy tickets, track your orders, and get QR codes — all in one place</p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="p-4 rounded-xl mb-6" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3)">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm" style="color:#F87171">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Google OAuth --}}
            <a href="{{ route('auth.google') }}"
               class="flex items-center justify-center gap-3 w-full py-3 px-4 rounded-xl font-medium text-sm transition-colors mb-6"
               style="background:var(--color-brand-elevated); border:1px solid var(--color-brand-border); color:var(--color-brand-text)">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Sign up with Google
            </a>

            <div class="flex items-center gap-3 mb-6">
                <div class="flex-1 h-px" style="background:var(--color-brand-border)"></div>
                <span class="text-xs" style="color:var(--color-brand-subtle)">or register with email</span>
                <div class="flex-1 h-px" style="background:var(--color-brand-border)"></div>
            </div>

            <form method="POST" action="{{ route('register.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1.5">Full name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                           class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-colors"
                           style="background:var(--color-brand-elevated); border:1px solid {{ $errors->has('name') ? 'rgba(239,68,68,0.6)' : 'var(--color-brand-border)' }}; color:var(--color-brand-text)"
                           placeholder="Jane Doe">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1.5">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-colors"
                           style="background:var(--color-brand-elevated); border:1px solid {{ $errors->has('email') ? 'rgba(239,68,68,0.6)' : 'var(--color-brand-border)' }}; color:var(--color-brand-text)"
                           placeholder="you@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1.5">
                        Phone number
                        <span class="font-normal ml-1" style="color:var(--color-brand-subtle)">(optional)</span>
                    </label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-colors"
                           style="background:var(--color-brand-elevated); border:1px solid {{ $errors->has('phone') ? 'rgba(239,68,68,0.6)' : 'var(--color-brand-border)' }}; color:var(--color-brand-text)"
                           placeholder="0712 345 678">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-colors"
                           style="background:var(--color-brand-elevated); border:1px solid {{ $errors->has('password') ? 'rgba(239,68,68,0.6)' : 'var(--color-brand-border)' }}; color:var(--color-brand-text)"
                           placeholder="At least 8 characters">
                    <p class="text-xs mt-1.5" style="color:var(--color-brand-subtle)">Min 8 characters, mixed case + numbers</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1.5">Confirm password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-colors"
                           style="background:var(--color-brand-elevated); border:1px solid var(--color-brand-border); color:var(--color-brand-text)"
                           placeholder="••••••••">
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-3">
                    Create Account
                </button>

                <p class="text-xs text-center" style="color:var(--color-brand-subtle)">
                    By registering you agree to our
                    <a href="#" class="hover:underline" style="color:var(--color-brand-primary)">Terms</a>
                    and
                    <a href="#" class="hover:underline" style="color:var(--color-brand-primary)">Privacy Policy</a>.
                </p>
            </form>

            <p class="text-center text-sm mt-6" style="color:var(--color-brand-muted)">
                Already have an account?
                <a href="{{ route('login') }}" style="color:var(--color-brand-primary)" class="font-medium hover:underline">Sign in</a>
            </p>

            <div class="mt-6 pt-6 border-t text-center" style="border-color:var(--color-brand-border)">
                <p class="text-xs" style="color:var(--color-brand-subtle)">
                    Want to sell tickets or host events?
                    <a href="{{ route('filament.organizer.auth.login') }}"
                       style="color:var(--color-brand-primary)" class="hover:underline">
                        Apply as an Organizer
                    </a>
                </p>
            </div>
        </div>

    </div>
</div>
@endsection
