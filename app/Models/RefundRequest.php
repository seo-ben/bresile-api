<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    use HasFactory;

    protected $table = 'refund_requests';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'total_paid_amount' => 'decimal:2',
            'refund_percent' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RefundRequest $refund) {
            if (in_array($refund->status, ['approved', 'paid', 'rejected'])) {
                if (! $refund->reviewed_by) {
                    $refund->reviewed_by = auth()->id() ?: 1;
                }
                if (! $refund->reviewed_at) {
                    $refund->reviewed_at = now();
                }
            }
            if ($refund->status === 'paid' && ! $refund->paid_at) {
                $refund->paid_at = now();
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function visaApplication(): BelongsTo
    {
        return $this->belongsTo(VisaApplication::class, 'visa_application_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function refundPolicy(): BelongsTo
    {
        return $this->belongsTo(RefundPolicy::class, 'refund_policy_id');
    }
}
