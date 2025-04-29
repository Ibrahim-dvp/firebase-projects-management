<?php

namespace App\Jobs;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Factory;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportFirebaseUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public string $filePath;
    public string $projectId;

    public function __construct($filePath, $projectId)
    {
        $this->filePath = $filePath;
        $this->projectId = $projectId;
        // dd($this->filePath, $this->projectId);
    }

    public function handle()
    {
        try {
            Artisan::call('firebase:import', [
                'file' => $this->filePath,
                '--project' => $this->projectId
            ]);

            Storage::delete($this->filePath);
        } catch (\Throwable $e) {
            // Handle/log errors per user, avoid stopping the loop
            logger()->error("Failed to create user" . $e->getMessage());
        }
    }

    // public $users;
    // public $sendEmailVerification;

    // public function __construct(array $users, bool $sendEmailVerification = false)
    // {
    //     $this->users = $users;
    //     $this->sendEmailVerification = $sendEmailVerification;
    // }
    // public function handle()
    // {

    //     $auth = (new Factory)
    //         ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
    //         ->createAuth();

    //     foreach ($this->users as $userData) {
    //         try {
    //             $user = $auth->createUser([
    //                 'email' => $userData['email'],
    //                 'password' => $userData['password'],
    //                 'emailVerified' => !$this->sendEmailVerification,
    //             ]);
    //             if ($this->sendEmailVerification) {
    //                 $auth->sendEmailVerificationLink($userData['email']);
    //             }

    //             // Optional: Log success or push to DB table

    //             // 🧘‍♂️ Delay between requests to avoid rate-limiting
    //             usleep(200000); // 200ms = 5 requests/second

    //         } catch (\Throwable $e) {
    //             // Handle/log errors per user, avoid stopping the loop
    //             logger()->error("Failed to create user {$userData['email']}: " . $e->getMessage());
    //         }
    //     }
    // }
}
