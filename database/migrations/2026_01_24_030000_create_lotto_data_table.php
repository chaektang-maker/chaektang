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
        Schema::create('lotto_data', function (Blueprint $table) {
            $table->string('lotto_id')->primary();
            $table->string('url');
            $table->string('date_text');
            $table->boolean('is_fetched')->default(false);
            $table->timestamps();
            
            $table->index('date_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotto_data');
    }
};
