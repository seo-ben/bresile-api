<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisaApplication extends Model
{
    use HasFactory;

    protected $table = 'visa_applications';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'outcome_reported_at' => 'datetime',
            'submitted_at'        => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected static function booted(): void
    {
        static::saving(function (VisaApplication $visa) {
            if (in_array($visa->visa_outcome, ['granted', 'refused']) || $visa->status === 'closed') {
                if (! $visa->reviewed_by) {
                    $visa->reviewed_by = auth()->id() ?: 1;
                }
                if (! $visa->outcome_reported_at) {
                    $visa->outcome_reported_at = now();
                }
            }
            if ($visa->status === 'submitted' && ! $visa->submitted_at) {
                $visa->submitted_at = now();
            }
        });
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VisaDocument::class, 'visa_application_id');
    }
}