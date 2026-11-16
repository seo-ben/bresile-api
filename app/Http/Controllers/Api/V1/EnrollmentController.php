<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\EnrollmentResource;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnrollmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EnrollmentService $enrollmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $enrollments = Enrollment::with(['level', 'city', 'installments', 'levelResult'])
            ->where('student_id', $request->user()->id)
            ->latest()
            ->get();

        return $this->successResponse(
            data: EnrollmentResource::collection($enrollments),
            message: 'Inscriptions récupérées avec succès.'
        );
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $enrollment = Enrollment::with(['level.rules', 'city', 'installments', 'absences', 'examResults.exam', 'levelResult'])
            ->where('student_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse(
            data: new EnrollmentResource($enrollment),
            message: 'Détail de l\'inscription récupéré avec succès.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'level_id'        => ['required', 'integer', 'exists:levels,id'],
            'study_mode'      => ['required', 'string', 'in:remote_home_country,in_person_brazil'],
            'rhythm'          => ['required', 'string', 'in:day,evening'],
            'brazil_city_id'  => ['nullable', 'required_if:study_mode,in_person_brazil', 'integer', 'exists:brazil_cities,id'],
            'payment_plan_id' => ['nullable', 'integer', 'exists:offer_payment_plans,id'],
        ]);

        $enrollment = $this->enrollmentService->createEnrollment(
            studentId: (int) $request->user()->id,
            levelId: (int) $validated['level_id'],
            studyMode: $validated['study_mode'],
            rhythm: $validated['rhythm'],
            brazilCityId: $validated['brazil_city_id'] ?? null,
            paymentPlanId: $validated['payment_plan_id'] ?? null
        );

        return $this->successResponse(
            data: new EnrollmentResource($enrollment),
            message: 'Dossier d\'inscription créé avec succès. Votre échéancier de paiement a été généré.',
            code: Response::HTTP_CREATED
        );
    }
}
