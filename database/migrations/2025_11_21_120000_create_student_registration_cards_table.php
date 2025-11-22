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
        Schema::create('student_registration_cards', function (Blueprint $table) {
            $table->id();
            $table->string('headline_ar');
            $table->string('headline_en');
            $table->text('subtitle_ar')->nullable();
            $table->text('subtitle_en')->nullable();
            $table->string('button_text_ar')->nullable();
            $table->string('button_text_en')->nullable();
            $table->string('cta_url')->nullable();
            $table->json('background')->nullable();
            $table->string('background_image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_registration_cards');
    }
};

