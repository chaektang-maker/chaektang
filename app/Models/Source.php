<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'popularity_score',
        'is_promoted',
        'promoted_until',
        'view_count',
    ];

    protected $casts = [
        'is_promoted' => 'boolean',
        'promoted_until' => 'datetime',
        'popularity_score' => 'integer',
        'view_count' => 'integer',
    ];

    /**
     * ป้องกันการแปลงวันที่เป็น UTC ตอนส่ง JSON ไปยัง Inertia
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return \Carbon\Carbon::instance($date)->timezone(config('app.timezone', 'Asia/Bangkok'))->format('Y-m-d');
    }

    public function lotteryNumbers(): HasMany
    {
        return $this->hasMany(LotteryNumber::class);
    }

    public function accuracyScores(): HasMany
    {
        return $this->hasMany(AccuracyScore::class);
    }

    public function accuracyHistories(): HasMany
    {
        return $this->hasMany(AccuracyHistory::class);
    }

    public function sourceAccuracyRecords(): HasMany
    {
        return $this->hasMany(SourceAccuracyRecord::class);
    }

    public function userVotes(): HasMany
    {
        return $this->hasMany(UserVote::class);
    }

    public function userFollows(): HasMany
    {
        return $this->hasMany(UserFollow::class);
    }

    /**
     * ตรวจสอบว่าสำนักนี้กำลังอยู่ในช่วงโปรโมทหรือไม่
     */
    public function isCurrentlyPromoted(): bool
    {
        if (!$this->is_promoted) {
            return false;
        }

        if ($this->promoted_until === null) {
            return true;
        }

        return $this->promoted_until->copy()->endOfDay()->isFuture();
    }
}
