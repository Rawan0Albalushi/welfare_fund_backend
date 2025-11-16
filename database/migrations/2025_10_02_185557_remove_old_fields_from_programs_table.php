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
		// تخطّي dropColumn على SQLite (غير مدعوم بسهولة خصوصًا مع الفهارس)
		$driver = Schema::getConnection()->getDriverName();
		if ($driver === 'sqlite') {
			return;
		}
		Schema::table('programs', function (Blueprint $table) {
			// حذف الحقول القديمة إن وجدت
			if (Schema::hasColumn('programs', 'title')) {
				$table->dropColumn('title');
			}
			if (Schema::hasColumn('programs', 'description')) {
				$table->dropColumn('description');
			}
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // إعادة الحقول القديمة في حالة التراجع
            $table->string('title')->after('category_id');
            $table->text('description')->after('title');
        });
    }
};
