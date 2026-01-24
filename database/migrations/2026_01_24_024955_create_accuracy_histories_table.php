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
        Schema::create('accuracy_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->onDelete('cascade');
            $table->string('draw_date'); // วันที่งวด
            $table->string('type'); // two_digit, three_digit, running
            $table->boolean('is_correct')->default(false); // เข้าหรือไม่
            $table->string('predicted_number')->nullable(); // เลขที่สำนักให้
            $table->string('actual_number')->nullable(); // เลขที่ออกจริง
            $table->timestamps();
            
            $table->index(['source_id', 'draw_date', 'type']);
            $table->index('draw_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accuracy_histories');
    }
};
