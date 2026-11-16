<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\LevelResult;
use App\Models\LevelRule;
use Illuminate\Support\Facades\DB;

class AcademicTrackingService
{
    public function __construct(
        protected EnrollmentService $enrollmentService,
        protected AuditService $auditService
    ) {}

    /**
     * Record an absence and evaluate whether the configured threshold is violated.
     */
    public function recordAbsence(
        int $enrollmentId,
        string $absenceDate,
        int $minutesAbsent,
        bool $isJustified = false,
        ?string $reason = null,
        ?int $actorId = null
    ): Absence {
        $enrollment = Enrollment::with('level.rules')->findOrFail($enrollmentId);

        return DB::transaction(function () use ($enrollment, $absenceDate, $minutesAbsent, $isJustified, $reason, $actorId) {
            $absence = Absence::create([
                'enrollment_id'   => $enrollment->id,
                'absence_date'    => $absenceDate,
                'minutes_absent'  => $minutesAbsent,
                'is_justified'    => $isJustified,
                'reason'          => $reason,
                'recorded_by'     => $actorId,
            ]);

            // Vérification de la règle d'absence du niveau
            $rules = $enrollment->level?->rules;
            if ($rules && $rules->max_absence_minutes > 0) {
                $totalMinutes = Absence::where('enrollment_id', $enrollment->id)
                    ->when(!$rules->count_justified_absences, fn($q) => $q->where('is_justified', false))
                    ->sum('minutes_absent');

                if ($totalMinutes > $rules->max_absence_minutes && $enrollment->status === 'active') {
                    $this->enrollmentService->transitionStatus(
                        enrollment: $enrollment,
                        newStatus: 'failed_attendance',
                        reason: "Plafond d'absences dépassé ({$totalMinutes} min cumulées sur une limite autorisée de {$rules->max_absence_minutes} min)",
                        actorId: $actorId ?? 1
                    );
                }
            }

            $this->auditService->log(
                eventType: 'absence_recorded',
                entity: $absence,
                newValues: ['minutes' => $minutesAbsent, 'is_justified' => $isJustified],
                actorId: $actorId
            );

            return $absence;
        });
    }

    /**
     * Calculate final level outcome (Validation or retake) based on weighted exam scores.
     */
    public function evaluateLevelCompletion(int $enrollmentId, int $actorId): LevelResult
    {
        $enrollment = Enrollment::with('level.rules', 'examResults.exam')->findOrFail($enrollmentId);
        $rules = $enrollment->level->rules;

        return DB::transaction(function () use ($enrollment, $rules, $actorId) {
            $results = $enrollment->examResults;
            $totalWeightedScore = 0;
            $totalWeight = 0;

            foreach ($results as $res) {
                if ($res->score !== null && $res->exam) {
                    $weight = (float) $res->exam->weight;
                    $normalizedScore = ($res->score / $res->exam->max_score) * ($rules ? (float) $rules->grade_scale_max : 20.0);
                    $totalWeightedScore += ($normalizedScore * $weight);
                    $totalWeight += $weight;
                }
            }

            $average = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : 0.00;
            $passingGrade = $rules ? (float) $rules->passing_average : 12.00;
            $isPassed = $average >= $passingGrade;

            $decision = $isPassed ? 'passed' : 'failed';

            $levelResult = LevelResult::updateOrCreate(
                ['enrollment_id' => $enrollment->id],
                [
                    'average_score' => $average,
                    'decision'      => $decision,
                    'decided_by'    => $actorId,
                    'decided_at'    => now(),
                ]
            );

            $this->enrollmentService->transitionStatus(
                enrollment: $enrollment,
                newStatus: $isPassed ? 'completed' : 'failed_exams',
                reason: "Décision académique : Moyenne de {$average}/20 (Seuil de validation : {$passingGrade}/20)",
                actorId: $actorId
            );

            return $levelResult;
        });
    }
}
