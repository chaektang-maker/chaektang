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

    public function userVotes(): HasMany
    {
        return $this->hasMany(UserVote::class);
    }

    public function userFollows(): HasMany
    {
        return $this->hasMany(UserFollow::class);
    }
}
