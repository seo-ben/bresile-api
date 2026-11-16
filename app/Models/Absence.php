<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absence extends Model
{
    public const UPDATED_AT = null;
    use HasFactory;

    protected $table = 'absences';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'absence_date' => 'date',
            'is_justified' => 'boolean',
            'created_at'   => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Absence $absence) {
            if (! $absence->recorded_by) {
                $absence->recorded_by = auth()->id() ?: 1;
            }
        });
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
