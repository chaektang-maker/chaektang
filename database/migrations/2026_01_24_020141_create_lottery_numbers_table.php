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
        Schema::create('lottery_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->onDelete('cascade');
            $table->string('draw_date'); // วันที่งวด (YYYY-MM-DD)
            $table->string('two_digit')->nullable(); // เลข 2 ตัว
            $table->string('three_digit')->nullable(); // เลข 3 ตัว
            $table->json('running_numbers')->nullable(); // เลขวิ่ง (array)
            $table->timestamps(); // created_at, updated_at
            
            $table->index(['source_id', 'draw_date']);
            $table->index('draw_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lottery_numbers');
    }
};
