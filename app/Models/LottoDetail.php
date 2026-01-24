<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LottoDetail extends Model
{
    protected $table = 'lotto_details';

    protected $fillable = [
        'lotto_id',
        'date',
        'endpoint',
    ];

    /**
     * ความสัมพันธ์กับ lotto_data
     */
    public function lottoData(): BelongsTo
    {
        return $this->belongsTo(LottoData::class, 'lotto_id', 'lotto_id');
    }
}
