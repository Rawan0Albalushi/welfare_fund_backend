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
        Schema::table('campaigns', function (Blueprint $table) {
            // حذف الحقول القديمة
            $table->dropColumn(['title', 'description', 'impact_description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // إعادة الحقول القديمة في حالة التراجع
            $table->string('title')->after('category_id');
            $table->text('description')->after('title');
            $table->text('impact_description')->nullable()->after('target_donors');
        });
    }
};
