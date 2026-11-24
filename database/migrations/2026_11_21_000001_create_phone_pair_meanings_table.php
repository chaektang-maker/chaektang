<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('phone_pair_meanings')) {
            Schema::create('phone_pair_meanings', function (Blueprint $table) {
                $table->id();
                $table->string('pair', 2)->unique(); // '00' ถึง '99'
                $table->string('level', 20)->default('neutral'); // excellent, good, neutral, bad
                $table->unsignedTinyInteger('score')->default(72); // 1 - 100
                $table->string('title', 255);
                $table->text('meaning');
                $table->unsignedTinyInteger('wealth_score')->default(72);
                $table->unsignedTinyInteger('career_score')->default(75);
                $table->unsignedTinyInteger('charm_score')->default(70);
                $table->unsignedTinyInteger('health_score')->default(72);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_pair_meanings');
    }
};
