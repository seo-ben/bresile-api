<?php

namespace App\Services;

use App\DTOs\DeclareVisaOutcomeDTO;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\RefundPolicy;
use App\Models\RefundRequest;
use App\Models\User;
use App\Models\VisaApplication;
use App\Models\VisaDocument;
use Illuminate\Support\Facades\DB;

class VisaAndRefundService
{
    public function __construct(
        protected EnrollmentService $enrollmentService,
        protected AuditService $auditService,
        protected EmailNotificationService $emailService
    ) {}

    /**
     * Start a visa assistance file for an enrolled student.
     */
    public function initiateVisaApplication(
        int $studentId,
        int $enrollmentId
    ): VisaApplication {
        $enrollment = Enrollment::findOrFail($enrollmentId);

        return VisaApplication::create([
            'student_id'    => $studentId,
            'enrollment_id' => $enrollment->id,
            'status'        => 'draft',
            'visa_outcome'  => 'pending',
        ]);
    }

    /**
     * Issue an official visa document (e.g., school enrollment certificate, acceptance letter).
     */
    public function issueOfficialDocument(
        int $visaApplicationId,
        string $title,
        int $fileId,
        int $issuedByAdminId
    ): VisaDocument {
        $visa = VisaApplication::with('student.profile')->findOrFail($visaApplicationId);

        return DB::transaction(function () use ($visa, $title, $fileId, $issuedByAdminId) {
            $document = VisaDocument::create([
                'visa_application_id' => $visa->id,
                'title'               => $title,
                'file_id'             => $fileId,
                'issued_by'           => $issuedByAdminId,
                'issued_at'           => now(),
            ]);

            $visa->update(['status' => 'documents_issued']);

            // Notification email informant l'étudiant que ses documents sont disponibles
            if ($visa->student) {
                $this->emailService->sendTemplateEmail(
                    templateCode: 'visa_documents_issued',
                    recipient: $visa->student,
                    variables: ['document_title' => $title],
                    locale: $visa->student->preferred_locale ?? 'fr'
                );
            }

            $this->auditService->log(
                eventType: 'visa_document_issued',
                entity: $document,
                newValues: ['title' => $title],
                actorId: $issuedByAdminId
            );

            return $document;
        });
    }

    /**
     * Declare the official outcome of a visa application.
     * In case of refusal, automatically triggers the 85% refund process based on school policy.
     */
    public function declareOutcome(DeclareVisaOutcomeDTO $dto): array
    {
        $visa = VisaApplication::with(['enrollment', 'student.profile'])->findOrFail($dto->visaApplicationId);
        $enrollment = $visa->enrollment;
        $student = $visa->student;

        return DB::transaction(function () use ($dto, $visa, $enrollment, $student) {
            if ($dto->outcome === 'granted') {
                $visa->update([
                    'visa_outcome'        => 'granted',
                    'outcome_reported_at' => now(),
                    'status'              => 'closed',
                ]);

                $this->auditService->log(
                    eventType: 'visa_granted',
                    entity: $visa,
                    actorId: $dto->userId
                );

                return [
                    'visa'   => $visa,
                    'refund' => null,
                ];
            }

            // Cas du refus de visa : déclenchement de la procédure éthique de remboursement
            $visa->update([
                'visa_outcome'        => 'refused',
                'outcome_reported_at' => now(),
                'status'              => 'closed',
            ]);

            // Récupération de la politique de remboursement configurée par l'administrateur
            $policy = RefundPolicy::where('code', 'visa_refusal')->first();
            $refundPercent = $policy ? (float) $policy->refund_percent : 85.00;

            // Calcul de la somme totale versée par l'étudiant
            $totalPaid = (float) Payment::where('enrollment_id', $enrollment->id)
                ->where('status', 'confirmed')
                ->sum('amount');

            $refundAmount = round(($totalPaid * $refundPercent) / 100, 2);

            $refundRequest = RefundRequest::create([
                'student_id'          => $dto->userId,
                'visa_application_id' => $visa->id,
                'enrollment_id'       => $enrollment->id,
                'refund_policy_id'    => $policy?->id ?? 1,
                'reason'              => 'visa_refusal',
                'status'              => 'submitted',
                'total_paid_amount'   => $totalPaid,
                'refund_percent'      => $refundPercent,
                'refund_amount'       => $refundAmount,
                'currency'            => $enrollment->currency ?? 'EUR',
                'notes'               => $dto->studentBankDetails,
            ]);

            $this->enrollmentService->transitionStatus(
                enrollment: $enrollment,
                newStatus: 'withdrawn',
                reason: "Procédure de remboursement initiée suite à un refus de visa (remboursement garanti à {$refundPercent}%)",
                actorId: $dto->userId
            );

            // Notification email automatique pour rassurer l'étudiant sur la prise en charge des 85%
            if ($student) {
                $this->emailService->sendTemplateEmail(
                    templateCode: 'visa_refusal_refund_initiated',
                    recipient: $student,
                    variables: [
                        'refund_amount'  => number_format($refundAmount, 2, ',', ' '),
                        'refund_percent' => $refundPercent,
                        'currency'       => $enrollment->currency ?? 'EUR',
                    ],
                    locale: $student->preferred_locale ?? 'fr'
                );
            }

            $this->auditService->log(
                eventType: 'visa_refusal_refund_initiated',
                entity: $refundRequest,
                newValues: [
                    'total_paid'     => $totalPaid,
                    'refund_percent' => $refundPercent,
                    'refund_amount'  => $refundAmount,
                ],
                actorId: $dto->userId
            );

            return [
                'visa'   => $visa,
                'refund' => $refundRequest,
            ];
        });
    }
}
