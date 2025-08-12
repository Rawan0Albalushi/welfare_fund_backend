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
        // Simply rename the table - Laravel will handle foreign key constraints automatically
        Schema::rename('student_applications', 'student_registrations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Simply rename the table back - Laravel will handle foreign key constraints automatically
        Schema::rename('student_registrations', 'student_applications');
    }
};
