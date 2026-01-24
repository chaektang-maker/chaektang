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
        Schema::create('accuracy_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->onDelete('cascade');
            $table->string('type'); // two_digit, three_digit, running
            $table->integer('total_draws')->default(0); // จำนวนงวดที่วัดผล
            $table->integer('correct_count')->default(0); // จำนวนครั้งที่เข้า
            $table->decimal('accuracy_percentage', 5, 2)->default(0); // % เข้า
            $table->integer('consecutive_correct')->default(0); // เข้าติดกันกี่งวด
            $table->string('last_calculated_draw_date')->nullable(); // งวดล่าสุดที่คำนวณ
            $table->timestamps();
            
            $table->unique(['source_id', 'type']);
            $table->index('accuracy_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accuracy_scores');
    }
};
