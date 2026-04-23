<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        // Find by google_id first, then fall back to email match
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->fill([
                'google_id' => $googleUser->getId(),
                'avatar'    => $user->avatar ?: $googleUser->getAvatar(),
            ])->save();

            if ($user->status !== UserStatus::Active) {
                return redirect()->route('home')
                    ->withErrors(['auth' => 'Your account has been suspended.']);
            }
        } else {
            $user = User::create([
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
                'password'  => bcrypt(Str::random(32)),
                'status'    => UserStatus::Active,
            ]);

            $user->assignRole('attendee');
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('home'));
    }
}
