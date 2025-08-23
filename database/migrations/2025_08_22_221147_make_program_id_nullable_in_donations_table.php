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
        Schema::table('donations', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['program_id']);
            
            // Make program_id nullable
            $table->foreignId('program_id')->nullable()->change();
            
            // Re-add the foreign key constraint
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['program_id']);
            
            // Make program_id NOT NULL again
            $table->foreignId('program_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
        });
    }
};
