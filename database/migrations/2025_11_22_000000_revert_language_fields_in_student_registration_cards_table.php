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
        Schema::table('student_registration_cards', function (Blueprint $table) {
            $table->dropColumn(['language', 'headline', 'subtitle']);
            $table->string('headline_ar')->nullable()->after('background_image');
            $table->string('headline_en')->nullable()->after('headline_ar');
            $table->text('subtitle_ar')->nullable()->after('headline_en');
            $table->text('subtitle_en')->nullable()->after('subtitle_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_registration_cards', function (Blueprint $table) {
            $table->dropColumn(['headline_ar', 'headline_en', 'subtitle_ar', 'subtitle_en']);
            $table->enum('language', ['ar', 'en'])->default('ar')->after('status');
            $table->string('headline')->after('language');
            $table->text('subtitle')->nullable()->after('headline');
        });
    }
};

