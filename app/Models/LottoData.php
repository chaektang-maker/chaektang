<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // แปลง lotto_id เป็น draw_date อัตโนมัติเมื่อบันทึกข้อมูล
        static::saving(function ($model) {
            try {
                if ($model->lotto_id && !$model->draw_date) {
                    $drawDate = $model->convertLottoIdToDate($model->lotto_id);
                    if ($drawDate) {
                        $model->draw_date = $drawDate;
                    }
                }
            } catch (\Exception $e) {
                // Log error แต่ไม่ throw exception เพื่อไม่ให้การบันทึกข้อมูลล้มเหลว
                \Log::warning('Error converting lotto_id to draw_date', [
                    'lotto_id' => $model->lotto_id ?? null,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    /**
     * แปลง lotto_id (DDMMYYYY พ.ศ.) เป็น draw_date (YYYY-MM-DD ค.ศ.)
     * 
     * @param string $lottoId รูปแบบ: DDMMYYYY (พ.ศ.)
     * @return \Carbon\Carbon|null
     */
    protected function convertLottoIdToDate($lottoId)
    {
        // ตรวจสอบว่า lotto_id เป็นตัวเลข 8 หลักหรือไม่
        if (!preg_match('/^[0-9]{8}$/', $lottoId)) {
            return null;
        }

        try {
            // ตัดข้อความตาม SQL: SUBSTRING(lotto_id, 1, 2) = วัน, SUBSTRING(lotto_id, 3, 2) = เดือน, SUBSTRING(lotto_id, 5, 4) = ปี (พ.ศ.)
            $day = substr($lottoId, 0, 2);
            $month = substr($lottoId, 2, 2);
            $yearBE = (int)substr($lottoId, 4, 4);
            
            // แปลงจาก พ.ศ. เป็น ค.ศ. (ลบ 543)
            $yearAD = $yearBE - 543;
            
            // สร้างวันที่
            return Carbon::createFromDate($yearAD, (int)$month, (int)$day);
        } catch (\Exception $e) {
            return null;
        }
    }

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
