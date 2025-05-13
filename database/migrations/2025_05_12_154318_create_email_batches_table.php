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
        Schema::create('email_batches', function (Blueprint $table) {
            $table->id();
            $table->string('project_id');
            $table->integer('total_emails');
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->string('status')->default('pending'); // pending/processing/completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_batches');
    }
};
