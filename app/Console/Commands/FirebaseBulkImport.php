<?php

namespace App\Console\Commands;

use Kreait\Firebase\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FirebaseBulkImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:import
                            {file : Path to CSV file}
                            {credentials : Path to Firebase credentials}
                            {--project= : Firebase project ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk import users using Firebase CLI';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $file = $this->argument('file');
        $credentialsPath = $this->argument('credentials');
        $projectId = $this->option('project');
        // dd($file, $credentialsPath, $projectId);
        // Validate file exists
        // if (!Storage::exists($file)) {
        //     $this->error("File not found: {$file}");
        //     return 1;
        // }

        // if (!file_exists($credentialsPath)) {
        //     $this->error("Credentials file not found: {$credentialsPath}");
        //     return 1;
        // }

        // Initialize Firebase
        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri("https://{$projectId}.firebaseio.com");

        $auth = $factory->createAuth();
        // Get full path
        // dd($file);


        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Skip header row

        $successCount = 0;
        $errorCount = 0;

        $this->info("Starting import process...");

        while (($data = fgetcsv($handle)) !== false) {
            try {
                $userData = [
                    'email' => $data[0],
                    'password' => $data[1] ?? $this->generateRandomPassword(),
                    'emailVerified' => false,
                    'disabled' => false,
                ];

                // Add displayName if column exists
                if (isset($header[2]) && isset($data[2])) {
                    $userData['displayName'] = $data[2];
                }

                $auth->createUser($userData);
                $successCount++;
                $this->output->write('.');
            } catch (\Throwable $e) {
                // Handle/log errors per user, avoid stopping the loop
                logger()->error("Failed to create user" . $e->getMessage());
            }
        }

        fclose($handle);

        $this->newLine();
        $this->info("Import complete!");
        $this->info("Successfully imported: {$successCount} users");
        $this->error("Failed to import: {$errorCount} users");

        return 0;

        // Build command
        // $command = "firebase auth:import {$filePath} --project={$project} --hash-algo=BCRYPT";

        // Execute
        // $this->info("Starting import...");
        // system($command, $returnCode);

        // if ($returnCode === 0) {
        //     $this->info("Import completed successfully!");
        //     return 0;
        // }

        // $this->error("Import failed with code: {$returnCode}");
        // return 1;
    }

    private function generateRandomPassword(): string
    {
        return bin2hex(random_bytes(8));
    }
}
