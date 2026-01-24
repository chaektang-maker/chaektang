<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LottoData extends Model
{
    protected $table = 'lotto_data';

    protected $fillable = [
        'lotto_id',
        'url',
        'date_text',
        'draw_date',
        'is_fetched',
    ];

    protected $casts = [
        'is_fetched' => 'boolean',
        'draw_date' => 'date',
    ];

    /**
     * ความสัมพันธ์กับ lotto_details
     */
    public function detail(): HasOne
    {
        return $this->hasOne(LottoDetail::class, 'lotto_id', 'lotto_id');
    }

    /**
     * ความสัมพันธ์กับ lotto_prizes
     */
    public function prizes(): HasMany
    {
        return $this->hasMany(LottoPrize::class, 'lotto_id', 'lotto_id');
    }

    /**
     * ความสัมพันธ์กับ lotto_running_numbers
     */
    public function runningNumbers(): HasMany
    {
        return $this->hasMany(LottoRunningNumber::class, 'lotto_id', 'lotto_id');
    }

    /**
     * ดึงรางวัลที่ 1
     */
    public function getFirstPrizeAttribute()
    {
        $prize = $this->prizes()->where('prize_id', 'prizeFirst')->first();
        return $prize ? ($prize->numbers[0] ?? null) : null;
    }

    /**
     * ดึงเลขท้าย 2 ตัว
     */
    public function getLastTwoDigitAttribute()
    {
        $running = $this->runningNumbers()->where('running_id', 'runningNumberBackTwo')->first();
        return $running ? ($running->numbers[0] ?? null) : null;
    }

    /**
     * ดึงเลขท้าย 3 ตัว
     */
    public function getLastThreeDigitAttribute()
    {
        $running = $this->runningNumbers()->where('running_id', 'runningNumberBackThree')->first();
        return $running ? ($running->numbers[0] ?? null) : null;
    }
}
