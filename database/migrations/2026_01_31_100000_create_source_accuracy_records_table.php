<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Track Record: เก็บสถิติต่องวด ว่าสำนักเข้าตรง / ตัวกลับ / เฉียด กี่ครั้ง
     */
    public function up(): void
    {
        Schema::create('source_accuracy_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->onDelete('cascade');
            $table->string('draw_date'); // วันที่งวด (YYYY-MM-DD)

            // จำนวนครั้งที่เข้า (ต่องวด)
            $table->unsignedTinyInteger('hit_direct_count')->default(0);   // เข้าตรง (2 ตัวตรง, 3 ตัวตรง, เลขวิ่งตรง)
            $table->unsignedTinyInteger('hit_reverse_count')->default(0); // เข้าตัวกลับ
            $table->unsignedTinyInteger('hit_near_count')->default(0);     // เฉียด (ตรงอย่างน้อย 1 หลัก แต่ไม่ตรง/ไม่กลับ)
            $table->unsignedTinyInteger('total_predictions')->default(0);  // จำนวนเลขที่สำนักให้ในงวดนี้

            // งวดนี้มีอย่างน้อย 1 ครั้งหรือไม่ (ใช้คำนวณ "ใน 10 งวด เข้าตรงกี่งวด")
            $table->boolean('has_direct_hit')->default(false);
            $table->boolean('has_reverse_hit')->default(false);
            $table->boolean('has_near_hit')->default(false);

            $table->timestamps();

            $table->unique(['source_id', 'draw_date']);
            $table->index('draw_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_accuracy_records');
    }
};
