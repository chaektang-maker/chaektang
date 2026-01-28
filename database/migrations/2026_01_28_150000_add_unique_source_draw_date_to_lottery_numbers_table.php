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
        Schema::table('lottery_numbers', function (Blueprint $table) {
            // 1 สำนัก ต่อ 1 งวด ได้เลขชุดเดียว
            $table->unique(['source_id', 'draw_date'], 'lottery_numbers_source_draw_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lottery_numbers', function (Blueprint $table) {
            $table->dropUnique('lottery_numbers_source_draw_date_unique');
        });
    }
};

