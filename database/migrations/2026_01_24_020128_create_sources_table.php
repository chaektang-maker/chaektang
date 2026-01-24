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
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ชื่อสำนัก
            $table->text('description')->nullable(); // คำอธิบาย
            $table->string('status')->default('active'); // active, suspended
            $table->integer('popularity_score')->default(0); // คะแนนความนิยม
            $table->boolean('is_promoted')->default(false); // โปรโมทหรือไม่
            $table->timestamp('promoted_until')->nullable(); // โปรโมทจนถึงเมื่อไหร่
            $table->integer('view_count')->default(0); // จำนวนครั้งที่ดู
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
