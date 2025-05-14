<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\EmailBatch;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use App\Models\FirebaseProject;
use App\Jobs\ImportFirebaseUsers;
use App\Models\UserGoogleAccount;
use Kreait\Firebase\Contract\Auth;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SendPasswordResetEmails;
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
        }
    }

    public function index(Request $request)
    {
        $perPage = 50;
        $page = $request->input('page', 1);
        $projectId = $request->input('project');
        $user = $request->user();
        $googleAccounts = $user->googleAccounts;
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
                'googleAccounts' => $googleAccounts,
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
            'googleAccounts' => $googleAccounts,
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
        $request->validate([
            'subject' => 'required|string',
            'body' => 'required|string',
            'senderEmail' => 'required|email',
            'senderName' => 'required|string',
            'replyTo' => 'nullable|email',
            'project' => 'required|string'
        ]);
        $projectId = $request->input('project');
        // $users = iterator_to_array($this->loadAuth($projectId)->listUsers(10000));
        // Artisan::call('app:send-emails', ['project' => $projectId, 'users' => $users]);

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
                        "customDomain" => 'zmachine.pro',
                        'useCustomDomain' => true,
                    ],
                    'resetPasswordTemplate' => [
                        'senderLocalPart' => explode('@', $request->senderEmail)[0],
                        'senderDisplayName' =>  $request->senderName,
                        'replyTo' => $request->replyTo,
                        'subject'     =>  $request->subject,
                        'body'        => $request->body,
                        // "
                        // Hello, Follow this link to reset your %APP_NAME% password for your %EMAIL% account.
                        // https://test2-9f700.firebaseapp.com/__/auth/action?mode=action&oobCode=code
                        // If you didn't ask to reset your password, you can ignore this email.Thanks,Your %APP_NAME% team
                        // "
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

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to update template: ' . $response->getBody()->getContents());
        }


        $emails = [];
        $batchSize = 1000;
        $maxUsers = 10000;
        $count = 0;
        $batch = EmailBatch::create([
            'project_id' => $projectId,
            'total_emails' => 0,
            'status' => 'processing'
        ]);
        try {
            $auth = $this->loadAuth($projectId);
            foreach ($auth->listUsers($maxUsers, $batchSize) as $user) {
                if ($user->email) {
                    $emails[] = $user->email;
                    $count++;
                }
                if (count($emails) === $batchSize) {
                    SendPasswordResetEmails::dispatch($emails, $projectId, $batch->id);
                    $emails = [];
                }
                if ($count >= $maxUsers) {
                    break;
                }
            }
            if (!empty($emails)) {
                SendPasswordResetEmails::dispatch($emails, $projectId, $batch->id);
            };
            $batch->update(['total_emails' => $count]);
            return back()->with('toast', [
                'type'    => 'success',
                'message' => "Password reset emails are being sent to {$count} users.",
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
