<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\BrazilCity;
use App\Models\CmsContent;
use App\Models\Level;
use App\Models\PartnerUniversity;
use App\Models\RefundPolicy;
use App\Models\WhatsappLead;
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

        $activeCurrency = \App\Services\CurrencyService::getActiveCurrency();

        $levels->each(function ($level) use ($lang, $activeCurrency) {
            if ($lang !== 'fr') {
                $level->name = $this->translator->translate($level->name, $lang);
            }
            $level->offers?->each(function ($offer) use ($lang, $activeCurrency) {
                $sourceCurr = $offer->currency ?: 'EUR';
                $offer->service_fee = \App\Services\CurrencyService::convert((float) ($offer->service_fee ?? 150), $sourceCurr, $activeCurrency);
                $offer->base_price = \App\Services\CurrencyService::convert((float) $offer->base_price, $sourceCurr, $activeCurrency);
                if ($offer->installment_1_amount !== null) {
                    $offer->installment_1_amount = \App\Services\CurrencyService::convert((float) $offer->installment_1_amount, $sourceCurr, $activeCurrency);
                }
                if ($offer->installment_2_amount !== null) {
                    $offer->installment_2_amount = \App\Services\CurrencyService::convert((float) $offer->installment_2_amount, $sourceCurr, $activeCurrency);
                }
                if ($offer->installment_3_amount !== null) {
                    $offer->installment_3_amount = \App\Services\CurrencyService::convert((float) $offer->installment_3_amount, $sourceCurr, $activeCurrency);
                }
                $offer->currency = $activeCurrency;
                $offer->paymentPlans?->each(function ($plan) use ($lang, $sourceCurr, $activeCurrency) {
                    if ($lang !== 'fr') {
                        $plan->label = $this->translator->translate($plan->label, $lang);
                    }
                    $plan->total_amount = \App\Services\CurrencyService::convert((float) $plan->total_amount, $sourceCurr, $activeCurrency);
                });
            });
        });

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

    public function storeWhatsappLead(Request $request): JsonResponse
    {
        $request->validate([
            'dial_code'    => 'required|string|max:12',
            'phone_number' => 'required|string|min:5|max:40',
            'country_name' => 'nullable|string|max:80',
            'locale'       => 'nullable|string|max:10',
        ]);

        WhatsappLead::ensureTableExists();

        $dialCode = trim((string) $request->input('dial_code', '+221'));
        $rawPhone = preg_replace('/[^0-9\s-]/', '', (string) $request->input('phone_number'));
        $phone = trim($rawPhone);

        if ($phone === '') {
            return $this->errorResponse(
                message: 'Veuillez saisir un numéro WhatsApp valide (chiffres uniquement).',
                code: 422
            );
        }

        $fullWhatsapp = trim("{$dialCode} {$phone}");

        $lead = WhatsappLead::create([
            'dial_code'     => $dialCode,
            'country_name'  => $request->input('country_name'),
            'phone_number'  => $phone,
            'full_whatsapp' => $fullWhatsapp,
            'locale'        => $this->translator->resolveLocale($request),
            'status'        => 'new',
        ]);

        return $this->successResponse(
            data: $lead,
            message: 'Numéro WhatsApp transmis à l\'administration avec succès.',
            code: 201
        );
    }

    public function getSiteSettings(Request $request): JsonResponse
    {
        $lang = $this->translator->resolveLocale($request);

        return $this->successResponse(
            data: \App\Services\SiteSettingsService::toPublicArray($lang),
            message: 'Paramètres du site récupérés avec succès.'
        );
    }
}
