<?php

namespace App\Jobs;

use Kreait\Firebase\Auth;
use App\Models\EmailBatch;
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
    protected $batch_id;
    public $tries = 3;


    public function __construct(array $emails, string $projectId, $batch_id)
    {
        $this->emails = $emails;
        $this->projectId = $projectId;
        $this->batch_id = $batch_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $batch = EmailBatch::find($this->batch_id);
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
                $batch->increment('sent_count');
                sleep(1);
            } catch (\Throwable $e) {
                $batch->increment('failed_count');
                \Log::error("Failed to send to {$email}: " . $e->getMessage());
            }
        }
        if ($batch->sent_count + $batch->failed_count >= $batch->total_emails) {
            $batch->update(['status' => 'completed']);
        }
    }
}
