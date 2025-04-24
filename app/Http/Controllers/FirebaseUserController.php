<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use App\Jobs\ImportFirebaseUsers;
use Kreait\Firebase\Contract\Auth;

class FirebaseUserController extends Controller
{

    protected $auth;

    public function __construct(Auth $auth)
    {
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));

        if (!file_exists($credentialsPath)) {
            throw new \Exception('Firebase credentials file not found');
        }

        $this->auth = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->createAuth();
    }

    public function index(Request $request)
    {
        $perPage = 30;
        $page = $request->input('page', 1);
        $search = $request->input('search');

        // Get all users (we need to fetch all for counting and filtering)
        $allUsers = $this->auth->listUsers();

        // Transform users data with proper date handling
        $users = [];
        $totalUsers = 0;

        foreach ($allUsers as $user) {
            // Apply search filter if provided
            if ($search && !str_contains(strtolower($user->email), strtolower($search))) {
                continue;
            }

            $totalUsers++;

            // Skip users not on the current page
            if ($totalUsers <= ($page - 1) * $perPage || $totalUsers > $page * $perPage) {
                continue;
            }

            $users[] = [
                'uid' => $user->uid,
                'email' => $user->email,
                'emailVerified' => $user->emailVerified,
                'disabled' => $user->disabled,
                'metadata' => [
                    'createdAt' => $user->metadata->createdAt ? $user->metadata->createdAt->format('Y-m-d H:i:s') : null,
                    'lastLoginAt' => $user->metadata->lastLoginAt ? $user->metadata->lastLoginAt->format('Y-m-d H:i:s') : null,
                ],
                'providerData' => array_map(function ($provider) {
                    return [
                        'providerId' => $provider->providerId,
                        'email' => $provider->email ?? null,
                    ];
                }, $user->providerData),
            ];
        }

        return Inertia::render('Users', [
            'users' => $users,
            'pagination' => [
                'total' => $totalUsers,
                'perPage' => $perPage,
                'currentPage' => $page,
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    // public function index()
    // {
    //     $users = [];
    //     $allUsers = $this->auth->listUsers();

    //     foreach ($allUsers as $user) {
    //         $users[] = [
    //             'uid' => $user->uid,
    //             'email' => $user->email,
    //             'emailVerified' => $user->emailVerified,
    //             'disabled' => $user->disabled,
    //             'metadata' => [
    //                 'createdAt' => $user->metadata->createdAt ? $user->metadata->createdAt->format('Y-m-d H:i:s') : null,
    //                 'lastLoginAt' => $user->metadata->lastLoginAt ? $user->metadata->lastLoginAt->format('Y-m-d H:i:s') : null,
    //             ],
    //         ];
    //     }

    //     return Inertia::render('Users', [
    //         'users' => $users,
    //     ]);
    // }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'sendEmailVerification' => 'boolean',
        ]);

        try {
            $userProperties = [
                'email' => $request->email,
                'password' => $request->password,
                'emailVerified' => !$request->sendEmailVerification,
            ];

            $user = $this->auth->createUser($userProperties);

            if ($request->sendEmailVerification) {
                $this->auth->sendEmailVerificationLink($request->email);
            }

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'User created successfully!',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('toast', [
                'type' => 'error',
                'message' => 'Failed to create user: ' . $e->getMessage(),
            ]);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*.email' => 'required|email',
            'users.*.password' => 'required|min:6',
        ]);

        $chunks = array_chunk($request->input('users'), 500);
        foreach ($chunks as $chunk) {
            ImportFirebaseUsers::dispatch($chunk, $request->input('sendEmailVerification', false));
        }

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Import started! You will be notified when it is done.',
        ]);
    }
}
