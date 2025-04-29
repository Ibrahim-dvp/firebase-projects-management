<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firebase_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_google_account_id')->constrained('user_google_accounts')->onDelete('cascade');
            $table->string('project_id')->unique();
            $table->string('name');
            $table->string('credentials_path');  // Encrypted storage path
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firebase_projects');
    }
};
