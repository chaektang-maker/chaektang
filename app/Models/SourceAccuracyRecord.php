<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourceAccuracyRecord extends Model
{
    protected $fillable = [
        'source_id',
        'draw_date',
        'hit_direct_count',
        'hit_reverse_count',
        'hit_near_count',
        'total_predictions',
        'has_direct_hit',
        'has_reverse_hit',
        'has_near_hit',
    ];

    protected $casts = [
        'has_direct_hit' => 'boolean',
        'has_reverse_hit' => 'boolean',
        'has_near_hit' => 'boolean',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
