<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    use HasFactory;

    protected $table = 'exam_results';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'score'       => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ExamResult $result) {
            if (! $result->recorded_by) {
                $result->recorded_by = auth()->id() ?: 1;
            }
            if (! $result->recorded_at) {
                $result->recorded_at = now();
            }
        });
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
}
