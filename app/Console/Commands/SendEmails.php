<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendPasswordResetEmails;
use Exception;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-emails {project} {users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Emails With Queue';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $emails = [];
        $batchSize = 1000;
        $maxUsers = 10000;
        $count = 0;
        $projectId = $this->argument('project');
        try {
            $users = $this->argument('users');
            foreach ($users as $user) {
                if ($user->email) {
                    $emails[] = $user->email;
                    $count++;
                }
                if (count($emails) === $batchSize) {
                    SendPasswordResetEmails::dispatch($emails, $projectId)
                        ->onQueue("project_{$projectId}");
                    $emails = [];
                }

                if ($count >= $maxUsers) {
                    break;
                }
            }

            if (!empty($emails)) {
                SendPasswordResetEmails::dispatch($emails, $projectId)
                    ->onQueue("project_{$projectId}");
            };
            return back()->with('toast', [
                'type'    => 'success',
                'message' => "Password reset emails are being sent to {$count} users.",
            ]);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
