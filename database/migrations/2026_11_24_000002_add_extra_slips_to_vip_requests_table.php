<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vip_requests')) {
            Schema::table('vip_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('vip_requests', 'extra_slips')) {
                    $table->json('extra_slips')->nullable()->after('slip_path');
                }
                if (!Schema::hasColumn('vip_requests', 'slip_hash')) {
                    $table->string('slip_hash', 64)->nullable()->index()->after('extra_slips');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vip_requests')) {
            Schema::table('vip_requests', function (Blueprint $table) {
                if (Schema::hasColumn('vip_requests', 'extra_slips')) {
                    $table->dropColumn('extra_slips');
                }
                if (Schema::hasColumn('vip_requests', 'slip_hash')) {
                    $table->dropColumn('slip_hash');
                }
            });
        }
    }
};
