<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // إضافة حقول الأسماء بالعربي والإنجليزي
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
        });

        // نقل البيانات الحالية من name إلى name_ar (إذا كانت عربية)
        DB::statement("UPDATE categories SET name_ar = name WHERE name IS NOT NULL");
        
        // يمكن إزالة الحقل القديم name إذا أردت
        // Schema::table('categories', function (Blueprint $table) {
        //     $table->dropColumn('name');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en']);
        });
    }
};
