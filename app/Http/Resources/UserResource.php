<?php

namespace App\Http\Resources;

use App\Models\LevelOffer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $translator = app(\App\Services\AutoTranslationService::class);
        $lang = $translator->resolveLocale($request);

        return [
            'id'               => $this->id,
            'email'            => $this->email,
            'role'             => $this->role,
            'status'           => $this->status,
            'preferred_locale' => $this->preferred_locale,
            'profile'          => [
                'first_name'   => $this->profile?->first_name,
                'last_name'    => $this->profile?->last_name,
                'phone'        => $this->profile?->phone,
                'country_code' => $this->profile?->country_code,
            ],
            'enrollments'      => $this->whenLoaded('enrollments', function () use ($translator, $lang) {
                return $this->enrollments->map(function ($enrollment) use ($translator, $lang) {
                    $offer = $enrollment->offer
                        ?? LevelOffer::with('paymentPlans')->where('level_id', $enrollment->level_id)->first()
                        ?? LevelOffer::with('paymentPlans')->first();

                    $basePrice = (float) ($offer?->base_price ?? 500.00);
                    $currency = $offer?->currency ?? 'EUR';

                    // Vérifier si l'admin a défini des plans spécifiques sur l'offre
                    $cashPlan = $offer?->paymentPlans?->firstWhere('installments_count', 1);
                    $splitPlan = $offer?->paymentPlans?->firstWhere('installments_count', '>', 1);

                    $totalPrice = $cashPlan && (float) $cashPlan->total_amount > 0
                        ? (float) $cashPlan->total_amount
                        : $basePrice;

                    $splitTotal = $splitPlan && (float) $splitPlan->total_amount > 0
                        ? (float) $splitPlan->total_amount
                        : $basePrice;

                    // Si l'admin a créé des échéances personnalisées sur le dossier étudiant, on les utilise
                    if ($enrollment->installments && $enrollment->installments->count() > 0) {
                        $installments = $enrollment->installments->map(function ($inst) {
                            return [
                                'id'             => $inst->id,
                                'installment_no' => $inst->installment_no,
                                'due_date'       => $inst->due_date ? date('d/m/Y', strtotime($inst->due_date)) : null,
                                'amount'         => (float) $inst->amount,
                                'currency'       => $inst->currency ?? 'EUR',
                                'status'         => $inst->status,
                                'paid_at'        => $inst->paid_at ? date('d/m/Y', strtotime($inst->paid_at)) : null,
                            ];
                        })->values();
                    } else {
                        // Calcul automatique en 3 tranches à partir du tarif défini par l'admin
                        $inst2 = floor(($splitTotal / 3) / 5) * 5;
                        if ($inst2 <= 0) {
                            $inst2 = round($splitTotal / 3, 2);
                        }
                        $inst3 = $inst2;
                        $inst1 = round($splitTotal - $inst2 - $inst3, 2);

                        $installments = collect([
                            [
                                'id'             => null,
                                'installment_no' => 1,
                                'due_date'       => null,
                                'amount'         => (float) $inst1,
                                'currency'       => $currency,
                                'status'         => 'pending',
                                'paid_at'        => null,
                            ],
                            [
                                'id'             => null,
                                'installment_no' => 2,
                                'due_date'       => null,
                                'amount'         => (float) $inst2,
                                'currency'       => $currency,
                                'status'         => 'pending',
                                'paid_at'        => null,
                            ],
                            [
                                'id'             => null,
                                'installment_no' => 3,
                                'due_date'       => null,
                                'amount'         => (float) $inst3,
                                'currency'       => $currency,
                                'status'         => 'pending',
                                'paid_at'        => null,
                            ],
                        ]);
                    }

                    return [
                        'id'                => $enrollment->id,
                        'level_id'          => $enrollment->level_id,
                        'level_name'        => $translator->translate($enrollment->level?->name ?? 'Niveau A1', $lang),
                        'study_mode'        => $offer?->study_mode ?? 'in_person_brazil',
                        'total_price'       => $totalPrice,
                        'split_total_price' => $splitTotal,
                        'currency'          => $currency,
                        'target_university' => $enrollment->target_university ?? 'Non spécifiée',
                        'city_name'         => $enrollment->city?->name ?? 'Brasília',
                        'city_state'        => $enrollment->city?->state_code ?? 'DF',
                        'status'            => $enrollment->status ?? 'pending_payment',
                        'status_reason'     => $translator->translate($enrollment->status_reason ?? 'Inscription enregistrée', $lang),
                        'rhythm'            => $enrollment->rhythm ?? 'day',
                        'enrolled_at'       => $enrollment->enrolled_at ? ($enrollment->enrolled_at instanceof \DateTimeInterface ? $enrollment->enrolled_at->format('d/m/Y') : date('d/m/Y', strtotime($enrollment->enrolled_at))) : null,
                        'installments'      => $installments,
                    ];
                });
            }),
            'visa_applications' => $this->whenLoaded('visaApplications', function () {
                return $this->visaApplications->map(function ($visa) {
                    return [
                        'id'           => $visa->id,
                        'status'       => $visa->status ?? 'draft',
                        'visa_outcome' => $visa->visa_outcome ?? 'pending',
                    ];
                });
            }),
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
