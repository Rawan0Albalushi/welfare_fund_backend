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
            $table->dropColumn(['button_text_ar', 'button_text_en', 'cta_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_registration_cards', function (Blueprint $table) {
            $table->string('button_text_ar')->nullable()->after('subtitle_en');
            $table->string('button_text_en')->nullable()->after('button_text_ar');
            $table->string('cta_url')->nullable()->after('button_text_en');
        });
    }
};

