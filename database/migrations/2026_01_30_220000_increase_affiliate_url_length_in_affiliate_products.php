<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ลิงก์ affiliate (เช่น Shopee + UTM) มักยาวกว่า 255 ตัวอักษร
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE affiliate_products MODIFY image_url VARCHAR(2048) NULL');
        DB::statement('ALTER TABLE affiliate_products MODIFY affiliate_url VARCHAR(2048) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE affiliate_products MODIFY image_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE affiliate_products MODIFY affiliate_url VARCHAR(255) NOT NULL');
    }
};
