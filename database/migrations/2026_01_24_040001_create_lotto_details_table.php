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
        Schema::create('lotto_details', function (Blueprint $table) {
            $table->id();
            $table->string('lotto_id');
            $table->string('date')->nullable();
            $table->string('endpoint')->nullable();
            $table->timestamps();
            
            $table->index('lotto_id');
            $table->foreign('lotto_id')->references('lotto_id')->on('lotto_data')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotto_details');
    }
};
