<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LottoRunningNumber extends Model
{
    protected $table = 'lotto_running_numbers';

    protected $fillable = [
        'lotto_id',
        'running_id',
        'running_name',
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
