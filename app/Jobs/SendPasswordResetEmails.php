<?php

namespace App\Jobs;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Factory;
use Illuminate\Bus\Batchable;
use App\Models\FirebaseProject;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Kreait\Firebase\Exception\AuthException;

class SendPasswordResetEmails implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    protected $emails;
    protected $projectId;

    public function __construct(array $emails, string $projectId)
    {
        $this->emails = $emails;
        $this->projectId = $projectId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $project = FirebaseProject::where('project_id', $this->projectId)->first();
        if (!$project) {
            \Log::error("Firebase project {$this->projectId} not found.");
            return;
        }

        $path = storage_path("app/private/{$project->credentials_path}");
        $auth = (new Factory)->withServiceAccount($path)->createAuth();

        foreach ($this->emails as $email) {
            try {
                $auth->sendPasswordResetLink($email);
                usleep(200000);
            } catch (\Throwable $e) {
                \Log::error("Failed to send reset email to {$email}: " . $e->getMessage());
            }
        }
    }
}
