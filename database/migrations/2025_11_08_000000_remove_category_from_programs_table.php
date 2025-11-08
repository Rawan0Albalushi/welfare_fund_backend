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
        if (!Schema::hasColumn('programs', 'category_id')) {
            return;
        }

        Schema::table('programs', function (Blueprint $table) {
            try {
                $table->dropForeign(['category_id']);
            } catch (\Throwable $e) {
                // Ignore if the foreign key was already removed.
            }

            try {
                $table->dropIndex(['status', 'category_id']);
            } catch (\Throwable $e) {
                // Ignore if the composite index does not exist.
            }

            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('programs', 'category_id')) {
            return;
        }

        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        Schema::table('programs', function (Blueprint $table) {
            try {
                $table->index(['status', 'category_id'], 'programs_status_category_id_index');
            } catch (\Throwable $e) {
                // Ignore if the index already exists.
            }
        });
    }
};

