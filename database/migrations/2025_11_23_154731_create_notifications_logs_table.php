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
        Schema::create('notifications_logs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('fcm_token')->nullable(); // Store the token used for this notification
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error_message')->nullable(); // Store error if sending failed
            $table->json('data')->nullable(); // Additional data sent with notification
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_logs');
    }
};
