<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * เพิ่ม customer_id ในตาราง user_votes เพื่อให้ลูกค้าที่ล็อกอินผ่าน guard customer โหวตสำนักได้
     */
    public function up(): void
    {
        Schema::table('user_votes', function (Blueprint $table) {
            if (!Schema::hasColumn('user_votes', 'customer_id')) {
                $table->foreignId('customer_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('customers')
                    ->cascadeOnDelete();

                $table->index(['customer_id', 'source_id', 'draw_date'], 'idx_customer_vote');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_votes', function (Blueprint $table) {
            if (Schema::hasColumn('user_votes', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->dropIndex('idx_customer_vote');
                $table->dropColumn('customer_id');
            }
        });
    }
};

