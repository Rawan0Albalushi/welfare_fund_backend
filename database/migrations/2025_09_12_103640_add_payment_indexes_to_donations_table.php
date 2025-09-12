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
            // إضافة فهارس لتحسين الأداء
            $table->index('payment_session_id');
            $table->index(['status', 'created_at']);
            $table->index(['status', 'payment_session_id']);
            
            // التأكد من أن الحقول المالية من نوع DECIMAL
            $table->decimal('amount', 10, 2)->change();
            $table->decimal('paid_amount', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            // حذف الفهارس
            $table->dropIndex(['payment_session_id']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['status', 'payment_session_id']);
        });
    }
};
