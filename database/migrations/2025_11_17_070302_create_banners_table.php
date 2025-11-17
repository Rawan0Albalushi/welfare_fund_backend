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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar');
            $table->string('title_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('image')->nullable();
            $table->string('link')->nullable(); // رابط البانر (اختياري)
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('order')->default(0); // ترتيب البانر
            $table->boolean('is_featured')->default(false); // بانر مميز
            $table->date('start_date')->nullable(); // تاريخ البدء
            $table->date('end_date')->nullable(); // تاريخ الانتهاء
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'is_featured']);
            $table->index('order');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
