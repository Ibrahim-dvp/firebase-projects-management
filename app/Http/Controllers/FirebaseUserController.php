<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use App\Models\FirebaseProject;
use App\Jobs\ImportFirebaseUsers;
use App\Models\UserGoogleAccount;
use Kreait\Firebase\Contract\Auth;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class FirebaseUserController extends Controller
{

    protected $auth;

    private function loadAuth($projectId = null)
    {
        if ($projectId) {
            // dump('first');
            $project = FirebaseProject::where('project_id', $projectId)->first();
            if ($project) {
                $path = storage_path("app/private/{$project->credentials_path}");
                return (new Factory)->withServiceAccount($path)->createAuth();
            }
        } else {
            // dump('second');
            $defaultPath = base_path(env('FIREBASE_CREDENTIALS'));
            if (!file_exists($defaultPath)) {
                throw new \Exception('Firebase credentials file not found');
            }

            return (new Factory)->withServiceAccount($defaultPath)->createAuth();
        }
    }


    public function index(Request $request)
    {
        // dump('thirs');
        $perPage = 50;
        $page = $request->input('page', 1);
        $projectId = $request->input('project');

        $projects = FirebaseProject::all();

        if (!$projectId && $projects->isNotEmpty()) {
            $projectId = $projects->first()->project_id;
        }

        $this->auth = $this->loadAuth($projectId);

        $users = [];
        $start = ($page - 1) * $perPage;
        $end = $start + $perPage;

        $allUsers = iterator_to_array($this->auth->listUsers());
        $totalCount = count($allUsers);
        $pagedUsers = array_slice($allUsers, $start, $perPage);

        foreach ($pagedUsers as $user) {
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

        return Inertia::render('Users', [
            'users' => $users,
            'pagination' => [
                'total' => $totalCount,
                'perPage' => $perPage,
                'currentPage' => (int)$page,
                'lastPage' => max(1, ceil($totalCount / $perPage)),
            ],
            'filters' => $request->only(['search']),
            'firebaseProjects' => $projects,
            'selectedProjectId' => $projectId,
        ]);
    }

    public function store(Request $request)
    {
        $projectId = $request->project;
        $auth = $this->loadAuth($projectId);

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
            $user = $auth->createUser($userProperties);

            if ($request->sendEmailVerification) {
                $auth->sendEmailVerificationLink($request->email);
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

    public function destroy(string $uidWithProject)
    {
        $parts = explode('|', $uidWithProject);
        $uid = $parts[0];
        $projectId = $parts[1] ?? null;
        $auth = $this->loadAuth($projectId);

        try {
            $auth->deleteUser($uid);
            return redirect()->back()->with('toast', [
                'type'    => 'success',
                'message' => "User {$uid} deleted.",
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('toast', [
                'type'    => 'error',
                'message' => 'Failed to delete user: ' . $e->getMessage(),
            ]);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $projectId = $request->input('project');
        $auth = $this->loadAuth($projectId);
        try {
            $auth->sendPasswordResetLink($request->email);
            return redirect()->back()->with('toast', [
                'type'    => 'success',
                'message' => 'Password reset email sent.',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('toast', [
                'type'    => 'error',
                'message' => 'Failed to send reset email: ' . $e->getMessage(),
            ]);
        }
    }

    public function import(Request $request)
    {
        // dd($request->input('users'));
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

    public function importUsers(Request $request)
    {
        $request->validate([
            'target_project_id' => 'required|string',
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        try {
            // 1. Process CSV and prepare data

            // 2. Create temp directory if needed
            $csvPath = $request->file('csv_file')->store('temp-imports');
            $absoluteCsvPath = storage_path('app/' . $csvPath);

            // 4. Build and execute command
            $command = sprintf(
                'firebase auth:import %s --project=%s --hash-algo=SCRYPT --rounds=8 --mem-cost=14 --hash-key=%s --salt-separator=%s',
                $absoluteCsvPath,
                $request->target_project_id,
                'YcIQUaHlZbBdcb08BeO3WzTu47WO/sOJFDZwNfv6tqGG1NCH3IDw6irsWm1QpCHTNvVG/NbXy4HakBAmy8vbnQ==',
                'Bw=='
            );


            exec($command . ' 2>&1', $output, $returnVar);

            // 5. Handle result
            if ($returnVar === 0) {
                dd('gg');
                return back()->with('success', 'Users imported successfully!');
            } else {
                dd($output);
            }

            // return back()->with('error', $this->parseFirebaseError($output));
        } catch (\Exception $e) {
            dd($e);

            return back()->with('error', $e->getMessage());
        } finally {
            // Clean up
            if (isset($jsonPath) && file_exists($jsonPath)) {
                // unlink($jsonPath);
            }
        }
    }
}
