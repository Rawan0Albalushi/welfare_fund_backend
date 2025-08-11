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
        Schema::create('student_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('application_id')->unique(); // APP_xxx format
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->json('personal_json');
            $table->json('academic_json');
            $table->json('financial_json');
            $table->enum('status', ['under_review', 'accepted', 'rejected'])->default('under_review');
            $table->text('reject_reason')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['program_id', 'status']);
            $table->index('application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_applications');
    }
};
