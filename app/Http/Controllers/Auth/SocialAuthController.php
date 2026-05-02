<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // If user exists, just update their google_id if it's missing
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
                Auth::login($user);
            } else {
                // Create a new user
                $user = User::create([
                    'name' => $googleUser->getName() ?? 'Trader',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    // password is left null, handled by migration
                ]);
                Auth::login($user);
            }

            return redirect()->intended(route('dashboard', absolute: false));

        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Failed to login with Google. Please try again.']);
        }
    }
}
