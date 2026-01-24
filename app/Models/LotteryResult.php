<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotteryResult extends Model
{
    protected $fillable = [
        'draw_date',
        'first_prize',
        'last_two_digit',
        'last_three_digit',
        'running_numbers',
        'is_calculated',
    ];

    protected $casts = [
        'running_numbers' => 'array',
        'is_calculated' => 'boolean',
    ];
}
