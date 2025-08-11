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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->uuid('donation_id')->unique(); // DN_xxx format
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('donor_name');
            $table->enum('type', ['quick', 'gift'])->default('quick');
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->json('payload')->nullable(); // Payment provider response
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('note')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'program_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('donation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
