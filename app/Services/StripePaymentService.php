<?php

namespace App\Services;

use App\DTOs\RecordPaymentDTO;
use App\Models\Enrollment;
use App\Models\EnrollmentInstallment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Create a Stripe Checkout Session for an enrollment or installment.
     */
    public function createCheckoutSession(
        User $user,
        Enrollment $enrollment,
        float $amount,
        string $currency = 'EUR',
        ?int $installmentId = null,
        string $description = 'Paiement scolarité Brasil·É'
    ): array {
        $stripeSecret = config('services.stripe.secret');

        // Normalisation de la devise pour Stripe (minuscules, ex: eur, xof)
        $stripeCurrency = strtolower($currency);
        // Stripe attend les montants en centimes pour l'EUR (170 EUR = 17000)
        // Pour les devises zéro-décimale comme XOF, pas de multiplication par 100
        $isZeroDecimal = in_array($stripeCurrency, ['xof', 'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xpf']);
        $unitAmount = $isZeroDecimal ? (int) round($amount) : (int) round($amount * 100);

        $frontendUrl = env('FRONTEND_URL', 'http://127.0.0.1:5173');
        $successUrl = "{$frontendUrl}/portail?payment=success&session_id={CHECKOUT_SESSION_ID}";
        $cancelUrl = "{$frontendUrl}/portail?payment=cancelled";

        if ($stripeSecret) {
            Stripe::setApiKey($stripeSecret);

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'customer_email'       => $user->email,
                'line_items'           => [
                    [
                        'price_data' => [
                            'currency'     => $stripeCurrency,
                            'unit_amount'  => $unitAmount,
                            'product_data' => [
                                'name'        => "Brasil·É — {$description}",
                                'description' => "Candidat : {$user->email} • Réf : BE-2026-" . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                            ],
                        ],
                        'quantity'   => 1,
                    ],
                ],
                'mode'                 => 'payment',
                'success_url'          => $successUrl,
                'cancel_url'           => $cancelUrl,
                'metadata'             => [
                    'user_id'        => (string) $user->id,
                    'enrollment_id'  => (string) $enrollment->id,
                    'installment_id' => $installmentId ? (string) $installmentId : '',
                    'amount'         => (string) $amount,
                    'currency'       => $currency,
                ],
            ]);

            return [
                'mode'         => 'live',
                'checkout_url' => $session->url,
                'session_id'   => $session->id,
            ];
        }

        // Mode simulation / bac à sable si les clés Stripe ne sont pas encore renseignées dans le .env
        return [
            'mode'         => 'simulation',
            'checkout_url' => "{$frontendUrl}/portail?payment=simulation&amount={$amount}&currency={$currency}&installment_id={$installmentId}",
            'session_id'   => 'sim_' . bin2hex(random_bytes(8)),
            'message'      => 'Prêt pour Stripe. Renseignez STRIPE_SECRET dans votre fichier .env pour activer la passerelle en direct.',
        ];
    }

    /**
     * Confirms payment from a completed Stripe session.
     */
    public function handleSessionSuccess(string $sessionId): array
    {
        $stripeSecret = config('services.stripe.secret');

        if ($stripeSecret && !str_starts_with($sessionId, 'sim_')) {
            Stripe::setApiKey($stripeSecret);
            $session = StripeSession::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $meta = $session->metadata;
                $userId = (int) ($meta->user_id ?? 0);
                $enrollmentId = (int) ($meta->enrollment_id ?? 0);
                $installmentId = !empty($meta->installment_id) ? (int) $meta->installment_id : null;
                $amount = (float) ($meta->amount ?? 0);
                $currency = $meta->currency ?? 'EUR';

                $payment = $this->paymentService->recordPayment(new RecordPaymentDTO(
                    userId: $userId,
                    enrollmentId: $enrollmentId,
                    installmentId: $installmentId,
                    amount: $amount,
                    currency: $currency,
                    paymentMethod: 'stripe',
                    gatewayReference: $session->payment_intent ?? $session->id,
                    notes: "Paiement en ligne validé par Stripe Checkout (Session {$session->id})"
                ));

                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => 'Paiement Stripe confirmé avec succès.',
                ];
            }

            return [
                'success' => false,
                'message' => 'La session Stripe n\'a pas encore le statut payé.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Simulation de paiement Stripe enregistrée.',
        ];
    }
}
