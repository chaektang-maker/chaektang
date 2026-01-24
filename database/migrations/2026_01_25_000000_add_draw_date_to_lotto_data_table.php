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
