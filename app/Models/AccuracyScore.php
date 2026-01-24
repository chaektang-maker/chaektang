<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccuracyScore extends Model
{
    protected $fillable = [
        'source_id',
        'type',
        'total_draws',
        'correct_count',
        'accuracy_percentage',
        'consecutive_correct',
        'last_calculated_draw_date',
    ];

    protected $casts = [
        'total_draws' => 'integer',
        'correct_count' => 'integer',
        'accuracy_percentage' => 'decimal:2',
        'consecutive_correct' => 'integer',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
