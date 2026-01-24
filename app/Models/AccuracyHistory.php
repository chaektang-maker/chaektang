<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccuracyHistory extends Model
{
    protected $fillable = [
        'source_id',
        'draw_date',
        'type',
        'is_correct',
        'predicted_number',
        'actual_number',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
