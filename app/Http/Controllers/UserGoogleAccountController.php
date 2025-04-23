<?php

namespace App\Http\Controllers;

use App\Models\UserGoogleAccount;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class UserGoogleAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/cloud-platform',
                'https://www.googleapis.com/auth/firebase',
                'https://www.googleapis.com/auth/cloudplatformprojects',
                'https://www.googleapis.com/auth/cloudplatformprojects.readonly'
            ])
            // ->scopes(['https://www.googleapis.com/auth/cloud-platform', 'https://www.googleapis.com/auth/firebase'])
            ->with([
                'access_type' => 'offline', // This is crucial
                'prompt' => 'consent', // Force consent every time (for testing)
            ])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // dd($googleUser);

            if (UserGoogleAccount::where('google_id', $googleUser->id)->exists()) {
                return redirect()->route('dashboard',)->with('error', 'Google account already linked!');
            }
            // Save Google account info to the database
            UserGoogleAccount::updateOrCreate([
                'user_id' => Auth::id(),
                'google_id' => $googleUser->id,
                'access_token' => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken,
                'expires_in' => $googleUser->expiresIn,
                'expires_at' => now()->addSeconds($googleUser->expiresIn),
                'email' => $googleUser->email,
                'name' => $googleUser->name,
                'given_name' => $googleUser->user['given_name'],
                'family_name' => $googleUser->user['family_name'],
                'picture' => $googleUser->user['picture'],
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
        return redirect()->route('dashboard')->with('success', 'Google account linked successfully!');
    }

    public function destroy($id)
    {
        $googleAccount = UserGoogleAccount::findOrFail($id);
        $googleAccount->delete();
        return redirect()->route('dashboard')->with('success', 'Google account unlinked successfully!');
    }
}
