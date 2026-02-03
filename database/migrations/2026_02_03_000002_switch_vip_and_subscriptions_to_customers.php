<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * เปลี่ยน vip_requests และ subscriptions ให้อ้างถึง customers แทน users
     */
    public function up(): void
    {
        // vip_requests: user_id (ลูกค้าที่ขอ) -> customer_id
        Schema::table('vip_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('vip_requests', function (Blueprint $table) {
            $table->foreignId('customer_id')->after('id')->constrained('customers')->cascadeOnDelete();
        });

        // subscriptions: user_id -> customer_id (ลูกค้า VIP)
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'status']);
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('customer_id')->after('id')->constrained('customers')->cascadeOnDelete();
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vip_requests', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
        Schema::table('vip_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'status']);
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
            $table->index(['user_id', 'status']);
        });
    }
};
