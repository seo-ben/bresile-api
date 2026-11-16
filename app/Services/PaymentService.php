<?php

namespace App\Services;

use App\DTOs\RecordPaymentDTO;
use App\Models\Enrollment;
use App\Models\EnrollmentInstallment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected EnrollmentService $enrollmentService,
        protected AuditService $auditService,
        protected EmailNotificationService $emailService
    ) {}

    /**
     * Submit or declare a payment (e.g. proof of wire transfer or gateway callback).
     */
    public function recordPayment(RecordPaymentDTO $dto): Payment
    {
        $enrollment = Enrollment::findOrFail($dto->enrollmentId);

        return DB::transaction(function () use ($dto, $enrollment) {
            $isDirect = ($dto->paymentMethod === 'stripe' || $dto->paymentMethod === 'card');
            $status = $isDirect ? 'confirmed' : 'awaiting_confirmation';
            $method = ($dto->paymentMethod === 'bank_transfer') ? 'bank_transfer' : 'card_visa';

            $payment = Payment::create([
                'student_id'        => $dto->userId,
                'enrollment_id'     => $enrollment->id,
                'purpose'           => 'level_fee',
                'method'            => $method,
                'amount'            => $dto->amount,
                'currency'          => $dto->currency,
                'status'            => $status,
                'gateway_name'      => $isDirect ? 'stripe' : null,
                'gateway_reference' => $dto->gatewayReference,
                'confirmed_at'      => $isDirect ? now() : null,
            ]);

            if ($status === 'confirmed') {
                $this->applyPaymentToEnrollment($payment, $enrollment, $dto->installmentId);
            } else {
                // Envoi automatique de l'email avec les coordonnées bancaires
                $student = User::with('profile')->find($dto->userId);
                if ($student) {
                    $this->emailService->sendBankTransferInstructions(
                        student: $student,
                        amount: $dto->amount,
                        currency: $dto->currency
                    );
                }
            }

            $this->auditService->log(
                eventType: 'payment_recorded',
                entity: $payment,
                newValues: [
                    'amount' => $payment->amount,
                    'method' => $payment->method,
                    'status' => $payment->status,
                ],
                actorId: $dto->userId
            );

            return $payment;
        });
    }

    /**
     * Confirm a pending payment (typically done by Admin for bank transfers).
     */
    public function confirmPayment(Payment $payment, int $adminId): Payment
    {
        if ($payment->status === 'confirmed') {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $adminId) {
            $oldStatus = $payment->status;
            $payment->update([
                'status'       => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => $adminId,
            ]);

            $enrollment = $payment->enrollment;
            $this->applyPaymentToEnrollment($payment, $enrollment);

            // Notification email de confirmation de paiement à l'étudiant
            $student = User::with('profile')->find($payment->student_id);
            if ($student) {
                $this->emailService->sendTemplateEmail(
                    templateCode: 'payment_confirmed',
                    recipient: $student,
                    variables: [
                        'amount'   => number_format((float) $payment->amount, 2, ',', ' '),
                        'currency' => $payment->currency,
                    ],
                    locale: $student->preferred_locale ?? 'fr'
                );
            }

            $this->auditService->log(
                eventType: 'payment_confirmed_by_admin',
                entity: $payment,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => 'confirmed'],
                actorId: $adminId
            );

            return $payment;
        });
    }

    protected function applyPaymentToEnrollment(Payment $payment, Enrollment $enrollment, ?int $installmentId = null): void
    {
        // Marquer la tranche correspondante comme payée
        $installmentQuery = EnrollmentInstallment::where('enrollment_id', $enrollment->id)
            ->where('status', 'pending');

        if ($installmentId) {
            $installmentQuery->where('id', $installmentId);
        }

        $installment = $installmentQuery->orderBy('installment_no')->first();
        if ($installment) {
            $installment->update([
                'payment_id' => $payment->id,
                'status'     => 'paid',
                'paid_at'    => now(),
            ]);
        }

        // Si l'inscription était en attente de paiement, l'activer
        if ($enrollment->status === 'pending_payment') {
            $this->enrollmentService->transitionStatus(
                enrollment: $enrollment,
                newStatus: 'active',
                reason: 'Paiement validé avec succès',
                actorId: $payment->student_id
            );
        }
    }
}
