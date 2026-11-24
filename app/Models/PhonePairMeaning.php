<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PhonePairMeaning extends Model
{
    protected $fillable = [
        'pair',
        'level',
        'score',
        'title',
        'meaning',
        'wealth_score',
        'career_score',
        'charm_score',
        'health_score',
    ];

    protected $casts = [
        'score' => 'integer',
        'wealth_score' => 'integer',
        'career_score' => 'integer',
        'charm_score' => 'integer',
        'health_score' => 'integer',
    ];

    /**
     * ตรวจสอบและสร้างตารางอัตโนมัติหากยังไม่ได้รัน migrate
     */
    public static function ensureTableExists(): bool
    {
        try {
            if (!Schema::hasTable('phone_pair_meanings')) {
                Schema::create('phone_pair_meanings', function (Blueprint $table) {
                    $table->id();
                    $table->string('pair', 2)->unique();
                    $table->string('level', 20)->default('neutral');
                    $table->unsignedTinyInteger('score')->default(72);
                    $table->string('title', 255);
                    $table->text('meaning');
                    $table->unsignedTinyInteger('wealth_score')->default(72);
                    $table->unsignedTinyInteger('career_score')->default(75);
                    $table->unsignedTinyInteger('charm_score')->default(70);
                    $table->unsignedTinyInteger('health_score')->default(72);
                    $table->timestamps();
                });
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
