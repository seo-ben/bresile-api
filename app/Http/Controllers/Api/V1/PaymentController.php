<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\RecordPaymentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\RecordPaymentRequest;
use App\Http\Resources\ApiResponse;
use App\Models\BankAccount;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PaymentService $paymentService,
        protected \App\Services\StripePaymentService $stripeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $payments = Payment::with(['installment'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return $this->successResponse(
            data: $payments,
            message: 'Historique des paiements récupéré.'
        );
    }

    public function getBankAccounts(Request $request): JsonResponse
    {
        $translator = app(\App\Services\AutoTranslationService::class);
        $lang = $translator->resolveLocale($request);

        $accounts = BankAccount::where('is_active', true)->get();

        if ($lang !== 'fr') {
            $accounts->each(function ($acc) use ($translator, $lang) {
                $acc->label        = $translator->translate($acc->label, $lang);
                $acc->instructions = $translator->translate($acc->instructions, $lang);
            });
        }

        return $this->successResponse(
            data: $accounts,
            message: 'Coordonnées bancaires de l\'école pour virement récupérées.'
        );
    }

    public function store(RecordPaymentRequest $request): JsonResponse
    {
        $dto = new RecordPaymentDTO(
            userId: (int) $request->user()->id,
            enrollmentId: (int) $request->input('enrollment_id'),
            installmentId: $request->input('installment_id') ? (int) $request->input('installment_id') : null,
            amount: (float) $request->input('amount'),
            currency: $request->input('currency'),
            paymentMethod: $request->input('payment_method'),
            gatewayReference: $request->input('gateway_reference'),
            proofFileId: $request->input('proof_file_id') ? (int) $request->input('proof_file_id') : null,
            notes: $request->input('notes')
        );

        $payment = $this->paymentService->recordPayment($dto);

        return $this->successResponse(
            data: $payment,
            message: 'Paiement enregistré avec succès. ' . ($payment->status === 'completed' ? 'Votre inscription est active.' : 'L\'école vérifie votre virement sous 72h.'),
            code: Response::HTTP_CREATED
        );
    }

    /**
     * Initialise une session de paiement Stripe Checkout.
     */
    public function createStripeCheckout(Request $request): JsonResponse
    {
        $request->validate([
            'enrollment_id'  => 'required|exists:enrollments,id',
            'amount'         => 'required|numeric|min:1',
            'currency'       => 'nullable|string|max:3',
            'installment_id' => 'nullable|integer',
            'description'    => 'nullable|string|max:255',
        ]);

        $enrollment = \App\Models\Enrollment::findOrFail($request->input('enrollment_id'));

        $sessionData = $this->stripeService->createCheckoutSession(
            user: $request->user(),
            enrollment: $enrollment,
            amount: (float) $request->input('amount'),
            currency: $request->input('currency', 'EUR'),
            installmentId: $request->input('installment_id') ? (int) $request->input('installment_id') : null,
            description: $request->input('description', 'Scolarité Niveau A1')
        );

        return $this->successResponse(
            data: $sessionData,
            message: 'Session de paiement Stripe générée avec succès.'
        );
    }

    /**
     * Valide le paiement Stripe au retour de l'étudiant.
     */
    public function verifyStripeSession(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $result = $this->stripeService->handleSessionSuccess($request->input('session_id'));

        return $this->successResponse(
            data: $result,
            message: $result['message'] ?? 'Statut Stripe vérifié.'
        );
    }
}
