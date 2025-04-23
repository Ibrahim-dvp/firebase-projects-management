<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseUserController extends Controller
{
    public function index(Request $request)
    {
        // Get the selected project ID from session or request
        $projectId = $request->session()->get('current_firebase_project')
            ?? $request->user()->firebaseProjects()->first()?->project_id;

        // Initialize Firebase Auth
        $auth = $this->getFirebaseAuth($projectId);

        // Fetch users
        $users = [];
        try {
            $firebaseUsers = $auth->listUsers();
            foreach ($firebaseUsers as $user) {
                $users[] = [
                    'uid' => $user->uid,
                    'email' => $user->email,
                    'emailVerified' => $user->emailVerified,
                    'disabled' => $user->disabled,
                    'metadata' => [
                        'createdAt' => $user->metadata->createdAt?->format('Y-m-d H:i:s'),
                        'lastLoginAt' => $user->metadata->lastLoginAt?->format('Y-m-d H:i:s'),
                    ],
                ];
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch users: ' . $e->getMessage());
        }

        return Inertia::render('Users', [
            'users' => $users,
            'currentProjectId' => $projectId,
            'firebaseProjects' => $request->user()->firebaseProjects()->get(),
        ]);
    }

    protected function getFirebaseAuth($projectId)
    {
        // Get the service account for this project
        $serviceAccount = ServiceAccount::fromArray([
            'project_id' => $projectId,
            // Add other service account details from your database
            // or from the Google account associated with this project
        ]);

        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();

        return $firebase->getAuth();
    }
}
