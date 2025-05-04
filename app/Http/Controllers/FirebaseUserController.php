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
            $project = FirebaseProject::where('project_id', $projectId)->first();
            if ($project) {
                $path = storage_path("app/private/{$project->credentials_path}");
                return (new Factory)->withServiceAccount($path)->createAuth();
            }
        } else {
            return null;
            // $defaultPath = base_path(env('FIREBASE_CREDENTIALS'));
            // if (!file_exists($defaultPath)) {
            //     throw new \Exception('Firebase credentials file not found');
            // }

            // return (new Factory)->withServiceAccount($defaultPath)->createAuth();
        }
    }

    public function index(Request $request)
    {
        $perPage = 50;
        $page = $request->input('page', 1);
        $projectId = $request->input('project');

        $projects = auth()->user()->googleAccounts
            ->load('firebaseProjects')
            ->pluck('firebaseProjects')
            ->flatten();
        if (!$projectId && $projects->isNotEmpty()) {
            $projectId = $projects->first()->project_id;
        }

        $this->auth = $this->loadAuth($projectId);
        if ($this->auth == null) {
            return Inertia::render('Users', [
                'users' => [],
                'pagination' => [
                    'total' => 0,
                    'perPage' => 0,
                    'currentPage' => 1,
                    'lastPage' => 1,
                ],
                'filters' => $request->only(['search']),
                'firebaseProjects' => $projects,
                'selectedProjectId' => $projectId,
            ]);
        }
        $users = [];
        $start = ($page - 1) * $perPage;
        $end = $start + $perPage;


        $allUsers = iterator_to_array($this->auth->listUsers(10000));
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

    public function deleteAll($project)
    {
        if (! $project) {
            return redirect()->back()->with('toast', [
                'type'    => 'error',
                'message' => 'No project is selected',
            ]);
        }
        $auth = $this->loadAuth($project);
        $allUsers = iterator_to_array($auth->listUsers(10000));
        $uids = array_map(fn($user) => $user->uid, $allUsers);


        $errors = [];
        foreach (array_chunk($uids, 1000) as $batch) {
            // true = force deletion even if accounts aren’t disabled
            $deleteResult = $auth->deleteUsers($batch, true);
        }
        // dd($deleteResult);

        return redirect()->back()->with('toast', [
            'type'    => 'success',
            'message' => 'All users deleted successfully!',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $projectId = $request->input('project');

        $url = "https://identitytoolkit.googleapis.com/admin/v2/projects/{$projectId}/config";

        $query = [
            'updateMask' => implode(',', [
                'notification.sendEmail.dnsInfo.useCustomDomain',
                'notification.sendEmail.resetPasswordTemplate'
            ]),
        ];
        $payload = [
            'notification' => [
                'sendEmail' => [
                    'dnsInfo' => [
                        'useCustomDomain' => true,
                        "customDomain" => 'amazon.com',
                    ],
                    'resetPasswordTemplate' => [
                        // 'senderLocalPart' => 'ibrahim.b@intelligentb2b.com',
                        'replyTo' => 'amazon',
                        'senderDisplayName' => 'amazon.com',
                        'subject'     => "testing",
                        'body'        => "
                        Hello, Follow this link to reset your %APP_NAME% password for your %EMAIL% account.
                        https://test2-9f700.firebaseapp.com/__/auth/action?mode=action&oobCode=code
                        If you didn't ask to reset your password, you can ignore this email.Thanks,Your %APP_NAME% team
                        ",
                    ],
                ],
            ],
        ];

        $http = null;
        if ($projectId) {
            $project = FirebaseProject::where('project_id', $projectId)->first();
            if ($project) {
                $path = storage_path("app/private/{$project->credentials_path}");
                $http = (new Factory)->withServiceAccount($path)->createApiClient();
            }
        }

        try {
            $response = $http->request('PATCH', $url, [
                'query' => $query,
                'json'  => $payload,
            ]);
        } catch (\Throwable $th) {
            dd($th);
            throw $th;
        }

        if ($response->getStatusCode() === 200) {
            // dd('gg');
            $auth = $this->loadAuth($projectId);
            $auth->sendPasswordResetLink($request->email);
            return back()->with('toast', [
                'type'    => 'success',
                'message' => 'Firebase reset‑password template updated!',
            ]);
        }

        // 7. On failure, surface the error body
        $error = $response->getBody()->getContents();
        dd($error);
        return back()->with('toast', [
            'type'    => 'error',
            'message' => 'Failed to update template: ' . $error,
        ]);
    }

    public function resetPasswordAdmin(Request $request)
    {
        $data = $request->validate([
            'uid'                       => 'required|string',
            'password'                  => 'required|string|min:6|confirmed',
            'project'                   => 'required|string',
        ]);
        $auth = $this->loadAuth($data['project']);
        try {
            $auth->changeUserPassword($data['uid'], $data['password']);
            return back()->with('toast', [
                'type'    => 'success',
                'message' => "Password for {$data['uid']} reset successfully.",
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'Failed to reset password: ' . $e->getMessage(),
            ]);
        }
    }
    public function importUsers(Request $request)
    {
        $request->validate([
            'target_project_id' => 'required|string',
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        try {
            $file = $request->file('csv_file');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $relativePath = $file->storeAs(
                'temp-imports',
                $filename,
                'local'
            );
            $csvPath = storage_path("app/private/{$relativePath}");
            // firebase auth:import testCsv.csv  --project test4-77hgj
            $command = sprintf(
                'firebase auth:import %s --project %s ',
                $csvPath,
                $request->target_project_id,
            );
            exec($command . ' 2>&1', $output, $returnVar);

            // 5. Handle result
            if ($returnVar === 0) {
                return back()->with('success', 'Users imported successfully!');
            } else {
                return back()->with('erros', $output);
            }

            // return back()->with('error', $this->parseFirebaseError($output));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        } finally {
            // Clean up
            if (isset($csvPath) && file_exists($csvPath)) {
                unlink($csvPath);
            }
        }
    }
}
