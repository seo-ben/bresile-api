<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\RegisterStudentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('previous_level_certificate')) {
            $path = $request->file('previous_level_certificate')->store('level_certificates', 'public');
            $validated['previous_level_certificate_path'] = $path;
        }

        $dto = RegisterStudentDTO::fromArray($validated);
        $result = $this->authService->registerStudent($dto);

        return $this->successResponse(
            data: [
                'user'  => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            message: 'Compte étudiant créé avec succès.',
            code: Response::HTTP_CREATED
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->authenticate(
            email: $request->input('email'),
            password: $request->input('password')
        );

        return $this->successResponse(
            data: [
                'user'  => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            message: 'Connexion réussie.'
        );
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            data: new UserResource($request->user()->load(['profile', 'enrollments.level', 'enrollments.city', 'enrollments.offer.paymentPlans', 'enrollments.installments', 'visaApplications.documents.file'])),
            message: 'Profil utilisateur récupéré.'
        );
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'birth_date'    => 'nullable|date',
            'phone'         => 'nullable|string|max:40',
            'country_code'  => 'nullable|string|max:10',
            'study_purpose' => 'nullable|string|max:120',
            'rhythm'        => 'nullable|string|in:day,evening',
        ]);

        if ($user->profile) {
            $user->profile->update([
                'first_name'   => $validated['first_name'],
                'last_name'    => $validated['last_name'],
                'birth_date'   => $validated['birth_date'] ?? $user->profile->birth_date,
                'phone'        => $validated['phone'] ?? $user->profile->phone,
                'country_code' => ! empty($validated['country_code']) ? strtoupper(substr(trim($validated['country_code']), 0, 2)) : $user->profile->country_code,
            ]);
        }

        $latestEnrollment = $user->enrollments()->latest('id')->first();
        if ($latestEnrollment) {
            $enrollmentUpdate = [];
            if (array_key_exists('study_purpose', $validated) && $validated['study_purpose'] !== null) {
                $enrollmentUpdate['study_purpose'] = $validated['study_purpose'];
            }
            if (! empty($validated['rhythm'])) {
                $enrollmentUpdate['rhythm'] = $validated['rhythm'];
            }
            if (! empty($enrollmentUpdate)) {
                $latestEnrollment->update($enrollmentUpdate);
            }
        }

        return $this->successResponse(
            data: new UserResource($user->fresh(['profile', 'enrollments.level', 'enrollments.city', 'enrollments.offer.paymentPlans', 'enrollments.installments', 'visaApplications.documents.file'])),
            message: 'Vos informations ont été mises à jour avec succès.'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(
            message: 'Déconnexion effectuée avec succès.'
        );
    }
}
