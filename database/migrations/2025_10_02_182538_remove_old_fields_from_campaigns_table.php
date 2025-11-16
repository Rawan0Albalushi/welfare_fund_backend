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
		// SQLite لا يدعم dropColumn بسهولة خصوصًا مع الفهارس؛ نتخطى العملية في بيئة SQLite (اختبارات)
		$driver = Schema::getConnection()->getDriverName();
		if ($driver === 'sqlite') {
			return;
		}
		Schema::table('campaigns', function (Blueprint $table) {
			// حذف الحقول القديمة إن وجدت
			if (Schema::hasColumn('campaigns', 'title')) {
				$table->dropColumn('title');
			}
			if (Schema::hasColumn('campaigns', 'description')) {
				$table->dropColumn('description');
			}
			if (Schema::hasColumn('campaigns', 'impact_description')) {
				$table->dropColumn('impact_description');
			}
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
