<?php

namespace App\Http\Controllers;

use App\Models\UserGoogleAccount;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\FirebaseProject;
use App\Http\Requests\StoreFirebaseProjectRequest;
use App\Http\Requests\UpdateFirebaseProjectRequest;
use Google\Client;

class FirebaseProjectController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     $user = $request->user();
    //     // Get the first Google account for the user
    //     $googleAccount = $user->googleAccounts()->first();
    //     $token = $googleAccount->access_token ?? null;
    //     return Inertia::render('Projects', [
    //         'token' => $token
    //     ]);
    // }

    public function index(Request $request)
    {
        $accountId = $request->query('accountId');
        return $this->getProjects($request, $accountId);
    }

    public function getProjects(Request $request, $accountId)
    {
        $user = $request->user();

        // Get the account with token refresh if needed
        $googleAccount = $this->getValidGoogleAccount($user, $accountId);

        if (!$googleAccount) {
            return Inertia::render('Projects', [
                'token' => null,
            ]);
        }

        return Inertia::render('Projects', [
            'token' => $googleAccount->access_token,
            'accountId' => $googleAccount->id // Pass account ID to frontend
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


    public function addFirebase(Request $request, $accountId)
    {
        $request->validate([
            'project_id' => 'required|string',
        ]);

        $user = $request->user();
        $googleAccount = $user->googleAccounts()->findOrFail($accountId);

        // Create a new FirebaseProject instance
        $firebaseProject = new FirebaseProject();
        $firebaseProject->project_id = $request->project_id;
        $firebaseProject->google_account_id = $googleAccount->id;

        // Save the project to the database
        if ($firebaseProject->save()) {
            return response()->json(['success' => true, 'message' => 'Firebase project added successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to add Firebase project.'], 500);
        }
    }
}
