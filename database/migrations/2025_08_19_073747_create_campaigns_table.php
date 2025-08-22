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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('image')->nullable();
            $table->decimal('goal_amount', 15, 2);
            $table->decimal('raised_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'active', 'paused', 'completed', 'archived'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('target_donors')->nullable(); // عدد المتبرعين المستهدف
            $table->text('impact_description')->nullable(); // وصف التأثير المتوقع
            $table->json('campaign_highlights')->nullable(); // نقاط بارزة للحملة
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'category_id']);
            $table->index('title');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
