<?php

namespace App\Http\Controllers;

use Google\Client;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\FirebaseProject;
use App\Models\UserGoogleAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FirebaseProjectController extends Controller
{

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
            $client = $this->createGoogleClient($googleAccount);
            try {

                // $client->fetchAccessTokenWithRefreshToken($googleAccount->refresh_token);
                // $newToken = $client->getAccessToken();

                // $googleAccount->update([
                //     'access_token' => $newToken['access_token'],
                //     'expires_at' => now()->addSeconds($newToken['expires_in']),
                // ]);
                $newToken = $client->fetchAccessTokenWithRefreshToken();
                $googleAccount->update([
                    'access_token'  => $newToken['access_token'],
                    'expires_at'    => now()->addSeconds($newToken['expires_in']),
                    'refresh_token' => $newToken['refresh_token'] ?? $googleAccount->refresh_token,
                ]);

                return $googleAccount->fresh();
            } catch (\Exception $e) {
                throw new \Exception($e); // Log the error
                // return null;
            }
        }

        return $googleAccount;
    }

    protected function tokenNeedsRefresh($googleAccount)
    {
        return !$googleAccount->expires_at ||
            now()->addMinutes(5)->gte($googleAccount->expires_at);
    }

    protected function createGoogleClient($googleAccount): Client
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        // $client->setPrompt('consent');
        // $client->setAccessType('offline'); // Required to get refresh tokens
        // $client->setApprovalPrompt('force'); // Sometimes needed for first authorization
        $client->addScope('https://www.googleapis.com/auth/firebase');
        $client->addScope('https://www.googleapis.com/auth/cloud-platform');

        $client->setAccessToken([
            'access_token'  => $googleAccount->access_token,
            'refresh_token' => $googleAccount->refresh_token,
            'expires_in'    => $googleAccount->expires_at->diffInSeconds(now()),
        ]);
        return $client;
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $googleAccounts = $user->googleAccounts;
        $projects = auth()->user()->googleAccounts
            ->load('firebaseProjects')
            ->pluck('firebaseProjects')
            ->flatten();
        if ($googleAccounts->isEmpty()) {
            return Inertia::render('Uploads', [
                'googleAccounts' => [],
                'firebaseProjects' => $projects ?? [],
            ]);
        }
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

        return Inertia::render('Uploads', [
            'googleAccounts' => $validatedAccounts,
            'firebaseProjects' => $projects ?? [],
        ]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'email' => 'required|email|string',
            'display_name' => 'required|string|max:255',
            'project_id' => 'required|string|max:255|unique:firebase_projects,project_id,NULL,id',
            'credentials_file' => 'required|file|mimetypes:application/json',
        ]);

        $project = FirebaseProject::where('project_id', $request->project_id)->first();

        if ($project) {
            return back()->with('toast', [
                'type' => 'default',
                'message' => 'already exists',
            ]);
        }

        if (!$validated['project_id']) {
            return back()->withErrors([
                'project_id' => 'already exists',
            ]);
        }

        // Verify JSON contains required fields
        $json = json_decode(file_get_contents($validated['credentials_file']), true);
        if (!isset($json['project_id']) || $json['project_id'] !== $validated['project_id']) {
            return back()->withErrors([
                'credentials_file' => 'JSON does not match selected project',
            ]);
        }

        // // Store in secure storage
        $path = Storage::putFileAs(
            'firebase-credentials',
            $validated['credentials_file'],
            $validated['project_id'] . '.json'
        );


        // Save to database
        $user_id = Auth::user()->id;
        $accountId = UserGoogleAccount::where('user_id', '=', $user_id)->first()->id;
        FirebaseProject::updateOrCreate([
            'user_google_account_id' => $accountId,
            'name' => $request->display_name ?? $validated['project_id'],
            'project_id' => $validated['project_id'],
            'credentials_path' => $path,
        ]);

        return redirect()->route('uploads.index')->with('toast', [
            'type' => 'success',
            'message' => 'Credentials added successfully',
        ]);
    }
}
