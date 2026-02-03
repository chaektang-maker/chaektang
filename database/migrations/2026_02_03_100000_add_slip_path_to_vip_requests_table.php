<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_requests', function (Blueprint $table) {
            $table->string('slip_path')->nullable()->after('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('vip_requests', function (Blueprint $table) {
            $table->dropColumn('slip_path');
        });
    }
};
