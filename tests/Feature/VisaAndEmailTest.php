<?php

namespace Tests\Feature;

use App\DTOs\DeclareVisaOutcomeDTO;
use App\DTOs\RecordPaymentDTO;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\VisaApplication;
use App\Services\EmailNotificationService;
use App\Services\PaymentService;
use App\Services\VisaAndRefundService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VisaAndEmailTest extends TestCase
{
    public function test_email_notification_service_records_in_outbox(): void
    {
        Mail::fake();

        $student = User::firstOrCreate(
            ['email' => 'test-student@example.com'],
            ['password_hash' => bcrypt('password'), 'role' => 'student']
        );

        $service = app(EmailNotificationService::class);
        $outbox = $service->sendTemplateEmail(
            templateCode: 'payment_confirmed',
            recipient: $student,
            variables: ['amount' => '350,00', 'currency' => 'EUR']
        );

        $this->assertNotNull($outbox);
        $this->assertEquals($student->email, $outbox->to_email);
        $this->assertStringContainsString('350,00', $outbox->body_html);
    }

    public function test_visa_refusal_triggers_85_percent_refund_and_email(): void
    {
        Mail::fake();

        $student = User::firstOrCreate(
            ['email' => 'refusal-test@example.com'],
            ['password_hash' => bcrypt('password'), 'role' => 'student']
        );

        UserProfile::firstOrCreate(
            ['user_id' => $student->id],
            ['first_name' => 'Amadou', 'last_name' => 'Diallo']
        );

        $level = Level::first();

        $enrollment = Enrollment::create([
            'student_id'   => $student->id,
            'level_id'     => $level->id,
            'offer_id'     => 2,
            'rhythm'       => 'day',
            'status'       => 'active',
        ]);

        Payment::create([
            'student_id'    => $student->id,
            'enrollment_id' => $enrollment->id,
            'purpose'       => 'level_fee',
            'method'        => 'bank_transfer',
            'amount'        => 500.00,
            'currency'      => 'EUR',
            'status'        => 'confirmed',
            'confirmed_at'  => now(),
        ]);

        $visaService = app(VisaAndRefundService::class);
        $visa = $visaService->initiateVisaApplication($student->id, $enrollment->id);

        $dto = new DeclareVisaOutcomeDTO(
            visaApplicationId: $visa->id,
            userId: $student->id,
            outcome: 'refused',
            refusalReason: 'Motif consulaire officiel',
            refusalProofFileId: null,
            studentBankDetails: 'IBAN: SN00 1234 5678'
        );

        $result = $visaService->declareOutcome($dto);

        // Vérifications mathématiques et d'état
        $this->assertNotNull($result['refund']);
        $this->assertEquals(425.00, (float) $result['refund']->refund_amount); // 85% de 500€
        $this->assertEquals(85.00, (float) $result['refund']->refund_percent);
        $this->assertEquals('withdrawn', $enrollment->fresh()->status);
    }
}
