<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LottoPrize extends Model
{
    protected $table = 'lotto_prizes';

    protected $fillable = [
        'lotto_id',
        'prize_id',
        'prize_name',
        'reward',
        'amount',
        'numbers',
    ];

    protected $casts = [
        'numbers' => 'array',
        'amount' => 'integer',
    ];

    /**
     * ความสัมพันธ์กับ lotto_data
     */
    public function lottoData(): BelongsTo
    {
        return $this->belongsTo(LottoData::class, 'lotto_id', 'lotto_id');
    }
}
