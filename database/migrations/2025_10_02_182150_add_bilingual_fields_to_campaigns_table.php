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
        Schema::table('campaigns', function (Blueprint $table) {
            // إضافة حقول العنوان بالعربي والإنجليزي
            $table->string('title_ar')->nullable()->after('title');
            $table->string('title_en')->nullable()->after('title_ar');
            
            // إضافة حقول الوصف بالعربي والإنجليزي
            $table->text('description_ar')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_ar');
            
            // إضافة حقول وصف التأثير بالعربي والإنجليزي
            $table->text('impact_description_ar')->nullable()->after('impact_description');
            $table->text('impact_description_en')->nullable()->after('impact_description_ar');
        });

        // نقل البيانات الحالية من الحقول القديمة إلى الحقول العربية
        DB::statement("UPDATE campaigns SET title_ar = title WHERE title IS NOT NULL");
        DB::statement("UPDATE campaigns SET description_ar = description WHERE description IS NOT NULL");
        DB::statement("UPDATE campaigns SET impact_description_ar = impact_description WHERE impact_description IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'title_ar',
                'title_en',
                'description_ar',
                'description_en',
                'impact_description_ar',
                'impact_description_en'
            ]);
        });
    }
};
