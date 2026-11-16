<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\DeclareVisaOutcomeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Visa\DeclareVisaOutcomeRequest;
use App\Http\Resources\ApiResponse;
use App\Models\VisaApplication;
use App\Services\VisaAndRefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VisaController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected VisaAndRefundService $visaService
    ) {}

    public function show(int $enrollmentId, Request $request): JsonResponse
    {
        $visa = VisaApplication::with(['documents.file', 'student.profile'])
            ->where('student_id', $request->user()->id)
            ->where('enrollment_id', $enrollmentId)
            ->first();

        return $this->successResponse(
            data: $visa,
            message: 'Dossier de visa récupéré.'
        );
    }

    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enrollment_id'     => ['required', 'integer', 'exists:enrollments,id'],
            'consulate_country' => ['nullable', 'string', 'max:100'],
        ]);

        $visa = $this->visaService->initiateVisaApplication(
            studentId: (int) $request->user()->id,
            enrollmentId: (int) $validated['enrollment_id']
        );

        return $this->successResponse(
            data: $visa,
            message: 'Dossier de demande de visa initié avec succès.',
            code: Response::HTTP_CREATED
        );
    }

    public function declareOutcome(DeclareVisaOutcomeRequest $request): JsonResponse
    {
        $dto = new DeclareVisaOutcomeDTO(
            visaApplicationId: (int) $request->input('visa_application_id'),
            userId: (int) $request->user()->id,
            outcome: $request->input('outcome'),
            refusalReason: $request->input('refusal_reason'),
            refusalProofFileId: $request->input('refusal_proof_file_id') ? (int) $request->input('refusal_proof_file_id') : null,
            studentBankDetails: $request->input('student_bank_details')
        );

        $result = $this->visaService->declareOutcome($dto);

        $msg = $dto->outcome === 'granted'
            ? 'Félicitations pour l\'obtention de votre visa ! L\'école vous attend au Brésil.'
            : 'Votre déclaration de refus a été prise en compte. Votre demande de remboursement à 85% a été automatiquement transmise à la comptabilité.';

        return $this->successResponse(
            data: $result,
            message: $msg
        );
    }
}
