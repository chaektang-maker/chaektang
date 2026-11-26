<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dream_interpretations')) {
            Schema::create('dream_interpretations', function (Blueprint $table) {
                $table->id();
                $table->string('keyword')->unique();
                $table->string('category_letter', 10)->index();
                $table->text('meaning');
                $table->string('two_digits', 500)->nullable();
                $table->string('three_digits', 500)->nullable();
                $table->string('no_number_note', 500)->nullable();
                $table->string('numbers_raw', 1000)->nullable();
                $table->unsignedInteger('search_count')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dream_interpretations');
    }
};
