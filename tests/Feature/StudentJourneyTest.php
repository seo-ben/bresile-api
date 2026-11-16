<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\Level;
use App\Models\Payment;
use App\Models\RefundPolicy;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentJourneyTest extends TestCase
{
    public function test_complete_student_journey_from_registration_to_visa_and_refund(): void
    {
        // 1. Enregistrement d'un nouvel étudiant
        $registerPayload = [
            'email'      => 'etudiant.test.' . time() . '@example.com',
            'password'   => 'SecretPassword123!',
            'first_name' => 'Moussa',
            'last_name'  => 'Diallo',
            'phone'      => '+221 77 123 45 67',
            'country_code' => 'SN',
        ];

        $regResponse = $this->postJson('/api/v1/auth/register', $registerPayload);
        $regResponse->assertStatus(201)
            ->assertJsonPath('success', true);

        $token = $regResponse->json('data.token');
        $headers = ['Authorization' => "Bearer {$token}"];

        // 2. Récupération du formulaire d'inscription dynamique
        $formResponse = $this->getJson('/api/v1/forms/registration');
        $formResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $formId = $formResponse->json('data.id');

        // 3. Soumission du formulaire dynamique
        $submitPayload = [
            'form_id' => $formId,
            'answers' => [
                'first_name'     => 'Moussa',
                'last_name'      => 'Diallo',
                'phone'          => '+221 77 123 45 67',
                'country'        => 'SN',
                'address'        => 'Dakar, Sénégal',
                'study_mode'     => 'in_person_brazil',
                'brazil_city'    => 'Brasília',
                'rhythm'         => 'day',
                'future_project' => 'Poursuivre un master en ingénierie à Brasília',
                'reason'         => 'Passion pour la culture lusophone',
            ],
        ];

        $subResponse = $this->withHeaders($headers)->postJson('/api/v1/forms/submit', $submitPayload);
        $subResponse->assertStatus(201)
            ->assertJsonPath('success', true);

        // 4. Création d'une inscription pour le niveau A1
        $level = Level::first();
        $enrollPayload = [
            'level_id'       => $level->id,
            'study_mode'     => 'in_person_brazil',
            'rhythm'         => 'day',
            'brazil_city_id' => 1,
        ];

        $enrollResponse = $this->withHeaders($headers)->postJson('/api/v1/enrollments', $enrollPayload);
        $enrollResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending_payment');

        $enrollmentId = $enrollResponse->json('data.id');
        $installmentId = $enrollResponse->json('data.installments.0.id');

        // 5. Enregistrement d'un premier paiement par carte/Stripe
        $paymentPayload = [
            'enrollment_id'     => $enrollmentId,
            'installment_id'    => $installmentId,
            'amount'            => 350.00,
            'currency'          => 'EUR',
            'payment_method'    => 'stripe',
            'gateway_reference' => 'ch_test_' . uniqid(),
        ];

        $payResponse = $this->withHeaders($headers)->postJson('/api/v1/payments', $paymentPayload);
        $payResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'confirmed');

        // Vérifier que l'inscription est désormais active
        $enrollCheck = $this->withHeaders($headers)->getJson("/api/v1/enrollments/{$enrollmentId}");
        $enrollCheck->assertStatus(200)
            ->assertJsonPath('data.status', 'active');

        // 6. Procédure de Visa : initiation
        $visaResponse = $this->withHeaders($headers)->postJson('/api/v1/visa/initiate', [
            'enrollment_id'     => $enrollmentId,
            'consulate_country' => 'Sénégal',
        ]);
        $visaResponse->assertStatus(201)
            ->assertJsonPath('success', true);

        $visaId = $visaResponse->json('data.id');

        // 7. Déclaration d'un refus de visa pour déclencher le remboursement garanti à 85%
        $outcomePayload = [
            'visa_application_id'  => $visaId,
            'outcome'              => 'refused',
            'refusal_reason'       => 'Motif administratif consulaire',
            'student_bank_details' => 'IBAN SN08 1234 5678 9012 3456',
        ];

        $outcomeResponse = $this->withHeaders($headers)->postJson('/api/v1/visa/declare-outcome', $outcomePayload);
        $outcomeResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.refund.refund_percent', '85.00')
            ->assertJsonPath('data.refund.refund_amount', '297.50'); // 85% de 350.00 = 297.50

        // Vérifier que l'inscription est passée en statut 'withdrawn'
        $finalEnrollCheck = $this->withHeaders($headers)->getJson("/api/v1/enrollments/{$enrollmentId}");
        $finalEnrollCheck->assertStatus(200)
            ->assertJsonPath('data.status', 'withdrawn');
    }
}
