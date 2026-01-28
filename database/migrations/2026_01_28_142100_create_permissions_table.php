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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ชื่อ permission เช่น "จัดการสำนัก"
            $table->string('slug')->unique(); // slug เช่น "manage-sources"
            $table->string('route_name')->nullable(); // route name เช่น "backoffice.sources.*"
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // สำหรับเรียงลำดับในเมนู
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
