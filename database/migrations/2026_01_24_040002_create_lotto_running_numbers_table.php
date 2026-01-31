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
        if (!Schema::hasTable('lotto_running_numbers')) {
            Schema::create('lotto_running_numbers', function (Blueprint $table) {
                $table->id();
                $table->string('lotto_id');
                $table->string('running_id');
                $table->string('running_name')->nullable();
                $table->string('reward')->nullable();
                $table->integer('amount')->default(0);
                $table->json('numbers')->nullable();
                $table->timestamps();
                
                $table->index('lotto_id');
                $table->index('running_id');
                $table->foreign('lotto_id')->references('lotto_id')->on('lotto_data')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotto_running_numbers');
    }
};
