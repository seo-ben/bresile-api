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
        $dto = RegisterStudentDTO::fromArray($request->validated());
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
            data: new UserResource($request->user()->load(['profile', 'enrollments.level', 'enrollments.city', 'enrollments.offer', 'enrollments.installments', 'visaApplications'])),
            message: 'Profil utilisateur récupéré.'
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
