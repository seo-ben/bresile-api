<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    public const UPDATED_AT = null;
    use HasFactory;

    protected $table = 'exams';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'exam_date'  => 'date',
            'max_score'  => 'decimal:2',
            'weight'     => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::saving(function (Exam $exam) {
            if (! $exam->created_by) {
                $exam->created_by = auth()->id() ?: 1;
            }
        });
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class, 'exam_id');
    }
}
