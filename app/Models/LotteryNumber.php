<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotteryNumber extends Model
{
    protected $fillable = [
        'source_id',
        'draw_date',
        'two_digit',
        'three_digit',
        'running_numbers',
    ];

    protected $casts = [
        'running_numbers' => 'array',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
