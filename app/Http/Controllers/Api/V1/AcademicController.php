<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\BrazilCity;
use App\Models\CmsContent;
use App\Models\Level;
use App\Models\PartnerUniversity;
use App\Models\RefundPolicy;
use App\Services\AutoTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AutoTranslationService $translator
    ) {}

    public function getLevels(Request $request): JsonResponse
    {
        $lang = $this->translator->resolveLocale($request);

        $levels = Level::with(['rules', 'offers.paymentPlans'])
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        if ($lang !== 'fr') {
            $levels->each(function ($level) use ($lang) {
                $level->name = $this->translator->translate($level->name, $lang);
                $level->offers?->each(function ($offer) use ($lang) {
                    $offer->paymentPlans?->each(function ($plan) use ($lang) {
                        $plan->label = $this->translator->translate($plan->label, $lang);
                    });
                });
            });
        }

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

    public function getUniversities(Request $request): JsonResponse
    {
        $lang = $this->translator->resolveLocale($request);

        if (PartnerUniversity::count() === 0) {
            (new \Database\Seeders\PartnerUniversitySeeder())->run();
        }

        $universities = PartnerUniversity::where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        if ($lang !== 'fr') {
            $universities->each(function ($uni) use ($lang) {
                $uni->full_name  = $this->translator->translate($uni->full_name, $lang);
                $uni->rank_badge = $this->translator->translate($uni->rank_badge, $lang);
                $uni->highlight  = $this->translator->translate($uni->highlight, $lang);
            });
        }

        return $this->successResponse(
            data: $universities,
            message: 'Universités partenaires récupérées avec succès.'
        );
    }

    public function getRefundPolicies(Request $request): JsonResponse
    {
        $lang = $this->translator->resolveLocale($request);
        $policies = RefundPolicy::all();

        if ($lang !== 'fr') {
            $policies->each(function ($policy) use ($lang) {
                $policy->description = $this->translator->translate($policy->description, $lang);
            });
        }

        return $this->successResponse(
            data: $policies,
            message: 'Politiques de remboursement officielles récupérées.'
        );
    }

    public function getCmsContent(string $key, Request $request): JsonResponse
    {
        $lang = $this->translator->resolveLocale($request);

        $content = CmsContent::where('content_key', $key)
            ->where('locale', $lang)
            ->where('is_published', true)
            ->first();

        // Si l'admin n'a saisi le contenu qu'en français, on le traduit automatiquement
        if (! $content && $lang !== 'fr') {
            $content = CmsContent::where('content_key', $key)
                ->where('locale', 'fr')
                ->where('is_published', true)
                ->first();

            if ($content) {
                $content->title        = $this->translator->translate($content->title, $lang);
                $content->body_content = $this->translator->translate($content->body_content, $lang);
                $content->locale       = $lang;
            }
        }

        if (! $content) {
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
