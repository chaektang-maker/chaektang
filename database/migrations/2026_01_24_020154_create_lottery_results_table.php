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
        Schema::create('lottery_results', function (Blueprint $table) {
            $table->id();
            $table->string('draw_date')->unique(); // วันที่งวด (YYYY-MM-DD)
            $table->string('first_prize'); // รางวัลที่ 1 (6 หลัก)
            $table->string('last_two_digit'); // เลขท้าย 2 ตัว
            $table->string('last_three_digit'); // เลขท้าย 3 ตัว
            $table->json('running_numbers')->nullable(); // เลขวิ่ง
            $table->boolean('is_calculated')->default(false); // คำนวณคะแนนแล้วหรือยัง
            $table->timestamps();
            
            $table->index('draw_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lottery_results');
    }
};
