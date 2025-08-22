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
        Schema::table('programs', function (Blueprint $table) {
            // إزالة حقول التبرع من برامج الدعم الطلابي (الأعمدة الموجودة فقط)
            $table->dropColumn([
                'goal_amount',
                'raised_amount',
                'start_date',
                'end_date'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // إعادة إضافة حقول التبرع إذا تم التراجع
            $table->decimal('goal_amount', 10, 2)->nullable();
            $table->decimal('raised_amount', 10, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });
    }
};
