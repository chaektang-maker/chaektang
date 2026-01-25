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
        Schema::table('lotto_data', function (Blueprint $table) {
            if (!Schema::hasColumn('lotto_data', 'draw_date')) {
                $table->date('draw_date')->nullable()->after('date_text');
                $table->index('draw_date');
            }
        });

        // อัปเดตข้อมูลที่มีอยู่แล้ว: แปลงจาก lotto_id (DDMMYYYY พ.ศ.) เป็น draw_date
        // รูปแบบ: DDMMYYYY (พ.ศ.) -> YYYY-MM-DD (ค.ศ.)
        \DB::statement("
            UPDATE lotto_data
            SET draw_date = STR_TO_DATE(
                CONCAT(
                    SUBSTRING(lotto_id, 5, 4) - 543, '-',
                    SUBSTRING(lotto_id, 3, 2), '-',
                    SUBSTRING(lotto_id, 1, 2)
                ),
                '%Y-%m-%d'
            )
            WHERE lotto_id REGEXP '^[0-9]{8}$'
            AND LENGTH(lotto_id) = 8
            AND draw_date IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lotto_data', function (Blueprint $table) {
            if (Schema::hasColumn('lotto_data', 'draw_date')) {
                $table->dropIndex(['draw_date']);
                $table->dropColumn('draw_date');
            }
        });
    }
};
