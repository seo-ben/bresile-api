<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\SubmitFormDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\SubmitFormRequest;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\FormResource;
use App\Services\DynamicFormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DynamicFormService $formService
    ) {}

    public function getRegistrationForm(): JsonResponse
    {
        $form = $this->formService->getFormByPurpose('registration');

        return $this->successResponse(
            data: new FormResource($form),
            message: 'Formulaire d\'inscription récupéré avec succès.'
        );
    }

    public function submit(SubmitFormRequest $request): JsonResponse
    {
        $dto = new SubmitFormDTO(
            formId: (int) $request->input('form_id'),
            userId: (int) $request->user()->id,
            answers: $request->input('answers', [])
        );

        $submission = $this->formService->submitForm($dto);

        return $this->successResponse(
            data: [
                'submission_id' => $submission->id,
                'status'        => $submission->status,
                'submitted_at'  => $submission->submitted_at?->toIso8601String(),
            ],
            message: 'Vos réponses ont été enregistrées avec succès.',
            code: Response::HTTP_CREATED
        );
    }
}
