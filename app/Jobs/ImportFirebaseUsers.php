<?php

namespace App\Jobs;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportFirebaseUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */


    public $users;
    public $sendEmailVerification;

    public function __construct(array $users, bool $sendEmailVerification = false)
    {
        $this->users = $users;
        $this->sendEmailVerification = $sendEmailVerification;
    }
    public function handle()
    {

        $auth = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
            ->createAuth();

        foreach ($this->users as $userData) {
            try {
                $user = $auth->createUser([
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                    'emailVerified' => !$this->sendEmailVerification,
                ]);

                if ($this->sendEmailVerification) {
                    $auth->sendEmailVerificationLink($userData['email']);
                }

                // Optional: Log success or push to DB table

                // 🧘‍♂️ Delay between requests to avoid rate-limiting
                usleep(200000); // 200ms = 5 requests/second

            } catch (\Throwable $e) {
                // Handle/log errors per user, avoid stopping the loop
                logger()->error("Failed to create user {$userData['email']}: " . $e->getMessage());
            }
        }
    }
}
