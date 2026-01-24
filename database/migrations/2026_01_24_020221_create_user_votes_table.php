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
        Schema::create('user_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('source_id')->constrained('sources')->onDelete('cascade');
            $table->string('draw_date'); // งวดที่โหวต
            $table->string('ip_address', 45)->nullable(); // สำหรับผู้ใช้ไม่ล็อกอิน
            $table->timestamps();
            
            $table->unique(['user_id', 'source_id', 'draw_date'], 'unique_user_vote');
            $table->index(['source_id', 'draw_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_votes');
    }
};
