<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelRule extends Model
{
    use HasFactory;

    protected $table = 'level_rules';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
        'count_justified_absences' => 'boolean',
        'absence_failure_refund_pct' => 'decimal:2',
        'grade_scale_max' => 'decimal:2',
        'passing_average' => 'decimal:2',
        'updated_at' => 'datetime',
        ];
    }

}