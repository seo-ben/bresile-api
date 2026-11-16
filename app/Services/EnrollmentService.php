<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\EnrollmentInstallment;
use App\Models\EnrollmentStatusHistory;
use App\Models\Level;
use App\Models\LevelOffer;
use App\Models\OfferPaymentPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Create an enrollment for a student with installment schedule.
     */
    public function createEnrollment(
        int $studentId,
        int $levelId,
        string $studyMode,
        string $rhythm,
        ?int $brazilCityId = null,
        ?int $paymentPlanId = null
    ): Enrollment {
        $level = Level::findOrFail($levelId);

        // Trouver l'offre correspondante au mode d'étude
        $offer = LevelOffer::where('level_id', $level->id)
            ->where('study_mode', $studyMode)
            ->where('is_active', true)
            ->firstOrFail();

        // Détermination du plan de paiement ou plan par défaut
        $plan = $paymentPlanId
            ? OfferPaymentPlan::where('id', $paymentPlanId)->where('offer_id', $offer->id)->first()
            : OfferPaymentPlan::where('offer_id', $offer->id)->first();

        $totalAmount = $plan ? (float) $plan->total_amount : (float) $offer->base_price;
        $currency = $offer->currency ?? 'EUR';

        return DB::transaction(function () use ($studentId, $level, $offer, $plan, $studyMode, $rhythm, $brazilCityId, $totalAmount, $currency) {
            $enrollment = Enrollment::create([
                'student_id'      => $studentId,
                'level_id'        => $level->id,
                'offer_id'        => $offer->id,
                'payment_plan_id' => $plan?->id,
                'brazil_city_id'  => $brazilCityId,
                'rhythm'          => $rhythm,
                'status'          => 'pending_payment',
                'enrolled_at'     => now(),
            ]);

            // Génération de l'échéancier des tranches (installments)
            $installmentsCount = $plan ? $plan->installments_count : 1;
            $installmentAmount = round($totalAmount / $installmentsCount, 2);

            for ($i = 1; $i <= $installmentsCount; $i++) {
                EnrollmentInstallment::create([
                    'enrollment_id'  => $enrollment->id,
                    'installment_no' => $i,
                    'amount'         => ($i === $installmentsCount)
                        ? ($totalAmount - ($installmentAmount * ($installmentsCount - 1)))
                        : $installmentAmount,
                    'currency'       => $currency,
                    'due_date'       => now()->addMonths($i - 1)->toDateString(),
                    'status'         => 'pending',
                ]);
            }

            // Journaliser la première transition d'état
            EnrollmentStatusHistory::create([
                'enrollment_id' => $enrollment->id,
                'old_status'    => null,
                'new_status'    => 'pending_payment',
                'reason'        => 'Création du dossier d\'inscription',
                'changed_by'    => $studentId,
            ]);

            $this->auditService->log(
                eventType: 'enrollment_created',
                entity: $enrollment,
                newValues: [
                    'level_id'     => $level->id,
                    'total_amount' => $totalAmount,
                    'currency'     => $currency,
                ],
                actorId: $studentId
            );

            return $enrollment->load(['level', 'installments', 'city']);
        });
    }

    /**
     * Transition the state of an enrollment safely with validation and audit.
     */
    public function transitionStatus(
        Enrollment $enrollment,
        string $newStatus,
        string $reason,
        int $actorId
    ): Enrollment {
        $oldStatus = $enrollment->status;

        if ($oldStatus === $newStatus) {
            return $enrollment;
        }

        return DB::transaction(function () use ($enrollment, $oldStatus, $newStatus, $reason, $actorId) {
            $enrollment->update([
                'status'        => $newStatus,
                'status_reason' => $reason,
            ]);

            EnrollmentStatusHistory::create([
                'enrollment_id' => $enrollment->id,
                'old_status'    => $oldStatus,
                'new_status'    => $newStatus,
                'reason'        => $reason,
                'changed_by'    => $actorId,
            ]);

            $this->auditService->log(
                eventType: 'enrollment_status_changed',
                entity: $enrollment,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $newStatus, 'reason' => $reason],
                actorId: $actorId
            );

            return $enrollment;
        });
    }
}
