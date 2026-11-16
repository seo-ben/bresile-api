<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelResult extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $table = 'level_results';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
        'average_score' => 'decimal:2',
        'passing_average_snapshot' => 'decimal:2',
        'result' => 'date',
        'decided_at' => 'datetime',
        ];
    }

}