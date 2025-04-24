<?php

namespace App\Http\Controllers;

use App\Models\UserGoogleAccount;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\FirebaseProject;
use Google\Client;

class FirebaseProjectController extends Controller
{

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $user = $request->user();
        $accountId = $request->input('accountId');
        if ($accountId) {
            $selectedAccountEmail = UserGoogleAccount::where('id', $accountId)->value('email');
        }
        $googleAccounts = $user->googleAccounts;
        $validatedAccounts = [];
        foreach ($googleAccounts as $googleAccount) {
            $validAccount = $this->getValidGoogleAccount($user, $googleAccount->id);
            if ($validAccount) {
                $validatedAccounts[] = [
                    'id' => $googleAccount->id,
                    'access_token' => $validAccount->access_token,
                    'name' => $googleAccount->name,
                    'email' => $googleAccount->email,
                ];
            }
        }

        return Inertia::render('Projects', [
            'googleAccounts' => $validatedAccounts, // Pass validated accounts to the frontend
            'selectedEmail' => $selectedAccountEmail ?? 'all', // Pass account ID to frontend
        ]);
    }


    protected function getValidGoogleAccount($user, $accountId = null)
    {
        // Get the specified account or first available
        $account = $accountId
            ? $user->googleAccounts()->find($accountId)
            : $user->googleAccounts()->first();

        if (!$account) {
            return null;
        }

        return $this->refreshTokenIfNeeded($account);
    }

    protected function refreshTokenIfNeeded($googleAccount)
    {
        if ($this->tokenNeedsRefresh($googleAccount)) {
            $client = $this->createGoogleClient();

            try {
                $client->refreshToken($googleAccount->refresh_token);
                $newToken = $client->getAccessToken();

                $googleAccount->update([
                    'access_token' => $newToken['access_token'],
                    'expires_at' => now()->addSeconds($newToken['expires_in']),
                ]);

                return $googleAccount->fresh();
            } catch (\Exception $e) {
                report($e); // Log the error
                return null;
            }
        }

        return $googleAccount;
    }

    protected function tokenNeedsRefresh($googleAccount)
    {
        return !$googleAccount->expires_at ||
            now()->addMinutes(5)->gte($googleAccount->expires_at);
    }

    protected function createGoogleClient()
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        return $client;
    }
}
