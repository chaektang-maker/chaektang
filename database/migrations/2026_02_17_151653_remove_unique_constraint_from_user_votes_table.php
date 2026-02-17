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
        Schema::table('user_votes', function (Blueprint $table) {
            // Drop foreign keys ก่อน (เพราะ MySQL ใช้ index เดียวกัน)
            $table->dropForeign(['user_id']);
            $table->dropForeign(['source_id']);
        });

        Schema::table('user_votes', function (Blueprint $table) {
            // ลบ unique constraint เพราะมีปัญหาเมื่อ user_id เป็น NULL
            // จะใช้การตรวจสอบใน application level แทน
            $table->dropUnique('unique_user_vote');
            
            // เพิ่ม index สำหรับการค้นหา
            $table->index(['user_id', 'source_id', 'draw_date'], 'idx_user_vote');
            $table->index(['ip_address', 'source_id', 'draw_date'], 'idx_ip_vote');
        });

        Schema::table('user_votes', function (Blueprint $table) {
            // สร้าง foreign keys ใหม่
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('source_id')
                ->references('id')
                ->on('sources')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_votes', function (Blueprint $table) {
            // Drop foreign keys ก่อน
            $table->dropForeign(['user_id']);
            $table->dropForeign(['source_id']);
        });

        Schema::table('user_votes', function (Blueprint $table) {
            // คืนค่า unique constraint
            $table->dropIndex('idx_user_vote');
            $table->dropIndex('idx_ip_vote');
            $table->unique(['user_id', 'source_id', 'draw_date'], 'unique_user_vote');
        });

        Schema::table('user_votes', function (Blueprint $table) {
            // สร้าง foreign keys ใหม่
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('source_id')
                ->references('id')
                ->on('sources')
                ->onDelete('cascade');
        });
    }
};
