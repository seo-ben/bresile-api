<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\BrazilCity;
use App\Models\CmsContent;
use App\Models\Level;
use App\Models\PartnerUniversity;
use App\Models\RefundPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicController extends Controller
{
    use ApiResponse;

    public function getLevels(): JsonResponse
    {
        $levels = Level::with(['rules', 'offers.paymentPlans'])
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        return $this->successResponse(
            data: $levels,
            message: 'Niveaux et règles académiques récupérés avec succès.'
        );
    }

    public function getCities(): JsonResponse
    {
        $cities = BrazilCity::where('is_available', true)
            ->orWhereNull('is_available')
            ->orderBy('position')
            ->get();

        return $this->successResponse(
            data: $cities,
            message: 'Villes disponibles au Brésil récupérées avec succès.'
        );
    }

    public function getUniversities(): JsonResponse
    {
        // Auto-seed initial universities if table is empty
        if (PartnerUniversity::count() === 0) {
            (new \Database\Seeders\PartnerUniversitySeeder())->run();
        }

        $universities = PartnerUniversity::where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return $this->successResponse(
            data: $universities,
            message: 'Universités partenaires récupérées avec succès.'
        );
    }

    public function getRefundPolicies(): JsonResponse
    {
        $policies = RefundPolicy::all();

        return $this->successResponse(
            data: $policies,
            message: 'Politiques de remboursement officielles récupérées.'
        );
    }

    public function getCmsContent(string $key, Request $request): JsonResponse
    {
        $locale = $request->query('locale', 'fr');
        $content = CmsContent::where('content_key', $key)
            ->where('locale', $locale)
            ->where('is_published', true)
            ->first();

        if (!$content) {
            return $this->errorResponse(
                message: "Contenu introuvable pour la clé '{$key}'.",
                code: 404
            );
        }

        return $this->successResponse(
            data: $content,
            message: 'Contenu récupéré avec succès.'
        );
    }
}
