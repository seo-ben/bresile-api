<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'enrolled_at'  => 'datetime',
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(LevelOffer::class, 'offer_id');
    }

    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(OfferPaymentPlan::class, 'payment_plan_id');
    }

    public function visaApplication(): HasOne
    {
        return $this->hasOne(VisaApplication::class, 'enrollment_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(BrazilCity::class, 'brazil_city_id');
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(EnrollmentInstallment::class, 'enrollment_id')->orderBy('installment_no');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'enrollment_id');
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class, 'enrollment_id');
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class, 'enrollment_id');
    }

    public function levelResult(): HasOne
    {
        return $this->hasOne(LevelResult::class, 'enrollment_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(EnrollmentStatusHistory::class, 'enrollment_id');
    }
}
