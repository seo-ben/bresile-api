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
                'birth_date'   => $this->profile?->birth_date,
                'phone'        => $this->profile?->phone,
                'country_code' => $this->profile?->country_code,
            ],
            'enrollments'      => $this->whenLoaded('enrollments', function () use ($translator, $lang) {
                return $this->enrollments->map(function ($enrollment) use ($translator, $lang) {
                    $offer = $enrollment->offer
                        ?? LevelOffer::with('paymentPlans')->where('level_id', $enrollment->level_id)->first()
                        ?? LevelOffer::with('paymentPlans')->first();

                    $basePrice = (float) ($offer?->base_price ?? 500.00);
                    $rawServiceFee = (float) ($offer?->service_fee ?? 150.00);
                    $offerCurrency = $offer?->currency ?? 'EUR';
                    $activeCurrency = \App\Services\CurrencyService::getActiveCurrency();
                    $currency = $activeCurrency ?: $offerCurrency;

                    // Vérifier si l'admin a défini des plans spécifiques sur l'offre
                    $cashPlan = $offer?->paymentPlans?->firstWhere('installments_count', 1);
                    $splitPlan = $offer?->paymentPlans?->firstWhere('installments_count', '>', 1);

                    $rawTotalPrice = $cashPlan && (float) $cashPlan->total_amount > 0
                        ? (float) $cashPlan->total_amount
                        : $basePrice;

                    $rawSplitTotal = $splitPlan && (float) $splitPlan->total_amount > 0
                        ? (float) $splitPlan->total_amount
                        : $basePrice;

                    $serviceFee = \App\Services\CurrencyService::convert($rawServiceFee, $offerCurrency, $currency);
                    $totalPrice = \App\Services\CurrencyService::convert($rawTotalPrice, $offerCurrency, $currency);
                    $splitTotal = \App\Services\CurrencyService::convert($rawSplitTotal, $offerCurrency, $currency);

                    // Paiements confirmés sur ce dossier (séparés : Frais de Service vs Frais de Scolarité)
                    $confirmedPayments = \App\Models\Payment::where('enrollment_id', $enrollment->id)
                        ->where('status', 'confirmed')
                        ->orderBy('confirmed_at')
                        ->get();

                    $serviceFeePaidAmount = 0.0;
                    $tuitionPaidAmount = 0.0;
                    $serviceFeePaidAt = null;
                    $lastTuitionPaidAt = null;

                    foreach ($confirmedPayments as $p) {
                        $conv = \App\Services\CurrencyService::convert(
                            (float) $p->amount,
                            $p->currency ?: 'EUR',
                            $currency
                        );
                        if ($p->purpose === 'service_fee') {
                            $serviceFeePaidAmount += $conv;
                            $serviceFeePaidAt = $p->confirmed_at?->format('d/m/Y');
                        } else {
                            $tuitionPaidAmount += $conv;
                            $lastTuitionPaidAt = $p->confirmed_at?->format('d/m/Y');
                        }
                    }

                    $serviceFeePaidAmount = round($serviceFeePaidAmount, 2);
                    $serviceFeePaid = $serviceFee <= 0 || $serviceFeePaidAmount >= ($serviceFee - 0.5);
                    $totalPaid = round($tuitionPaidAmount, 2);

                    // Si l'admin a créé des échéances personnalisées sur le dossier étudiant, on les utilise
                    if ($enrollment->installments && $enrollment->installments->count() > 0) {
                        $installments = $enrollment->installments->map(function ($inst) use ($currency) {
                            $instCurrency = $inst->currency ?? 'EUR';
                            $convertedAmount = \App\Services\CurrencyService::convert((float) $inst->amount, $instCurrency, $currency);
                            return [
                                'id'             => $inst->id,
                                'installment_no' => $inst->installment_no,
                                'due_date'       => $inst->due_date ? date('d/m/Y', strtotime($inst->due_date)) : null,
                                'amount'         => (float) $convertedAmount,
                                'currency'       => $currency,
                                'status'         => $inst->status,
                                'paid_at'        => $inst->paid_at ? date('d/m/Y', strtotime($inst->paid_at)) : null,
                            ];
                        })->values();
                        $splitTotal = round((float) $installments->sum('amount'), 2);
                    } else {
                        // Utiliser les 3 montants de tranches définis explicitement par l'admin sur l'offre
                        $hasAdminInstallments = $offer && (
                            (float) ($offer->installment_1_amount ?? 0) > 0 ||
                            (float) ($offer->installment_2_amount ?? 0) > 0 ||
                            (float) ($offer->installment_3_amount ?? 0) > 0
                        );

                        if ($hasAdminInstallments) {
                            $inst1 = \App\Services\CurrencyService::convert((float) ($offer->installment_1_amount ?? 0), $offerCurrency, $currency);
                            $inst2 = \App\Services\CurrencyService::convert((float) ($offer->installment_2_amount ?? 0), $offerCurrency, $currency);
                            $inst3 = \App\Services\CurrencyService::convert((float) ($offer->installment_3_amount ?? 0), $offerCurrency, $currency);
                            $splitTotal = round($inst1 + $inst2 + $inst3, 2);
                        } else {
                            // Valeur de secours uniquement si l'admin n'a pas encore renseigné les 3 tranches
                            $inst2 = floor(($splitTotal / 3) / 5) * 5;
                            if ($inst2 <= 0) {
                                $inst2 = round($splitTotal / 3, 2);
                            }
                            $inst3 = $inst2;
                            $inst1 = round($splitTotal - $inst2 - $inst3, 2);
                        }

                        // Déterminer quelles tranches de scolarité sont déjà payées
                        $remainingPaidPool = $totalPaid;
                        $lastPaidDate = $lastTuitionPaidAt;
                        $instAmounts = [$inst1, $inst2, $inst3];
                        $installments = collect();
                        foreach ($instAmounts as $idx => $amt) {
                            $isInstPaid = $amt > 0 && $remainingPaidPool >= ($amt - 0.5);
                            if ($isInstPaid) {
                                $remainingPaidPool = max(0, $remainingPaidPool - $amt);
                            }
                            $installments->push([
                                'id'             => null,
                                'installment_no' => $idx + 1,
                                'due_date'       => null,
                                'amount'         => (float) $amt,
                                'currency'       => $currency,
                                'status'         => $isInstPaid ? 'paid' : 'pending',
                                'paid_at'        => $isInstPaid ? $lastPaidDate : null,
                            ]);
                        }
                    }

                    // Le solde restant est la somme exacte des tranches non encore payées (si échelonné) ou totalPrice - totalPaid
                    $pendingInstallmentsSum = round(
                        (float) $installments->where('status', '!=', 'paid')->sum('amount'),
                        2
                    );
                    $hasPaidInstallment = $installments->where('status', 'paid')->count() > 0;
                    $effectiveTargetTotal = $hasPaidInstallment ? $splitTotal : $totalPrice;
                    $remainingAmount = $hasPaidInstallment
                        ? $pendingInstallmentsSum
                        : max(0, round($effectiveTargetTotal - $totalPaid, 2));
                    $isFullyPaid = $remainingAmount <= 0.5 && $totalPaid > 0;

                    $paymentsList = $confirmedPayments->map(function ($p) use ($currency) {
                        return [
                            'id'                => $p->id,
                            'purpose'           => $p->purpose ?? 'level_fee',
                            'amount'            => \App\Services\CurrencyService::convert((float) $p->amount, $p->currency ?: 'EUR', $currency),
                            'currency'          => $currency,
                            'method'            => $p->method,
                            'status'            => $p->status,
                            'gateway_reference' => $p->gateway_reference,
                            'confirmed_at'      => $p->confirmed_at?->format('d/m/Y H:i'),
                        ];
                    })->values();

                    return [
                        'id'                              => $enrollment->id,
                        'level_id'                        => $enrollment->level_id,
                        'level_code'                      => $enrollment->level?->code ?? 'A1',
                        'level_name'                      => $translator->translate($enrollment->level?->name ?? 'Niveau A1', $lang),
                        'study_mode'                      => $enrollment->visa_type === 'visitor_visa' ? 'visitor_visa_brazil' : ($offer?->study_mode ?? 'in_person_brazil'),
                        'visa_type'                       => $enrollment->visa_type,
                        'study_purpose'                   => $enrollment->study_purpose,
                        'previous_level_certificate_url'  => $enrollment->previous_level_certificate_path ? asset('storage/' . $enrollment->previous_level_certificate_path) : null,
                        'service_fee'                     => $serviceFee,
                        'service_fee_paid'                => $serviceFeePaid,
                        'service_fee_paid_amount'         => $serviceFeePaidAmount,
                        'service_fee_paid_at'             => $serviceFeePaidAt,
                        'total_price'                     => $totalPrice,
                        'split_total_price'               => $splitTotal,
                        'total_paid'                      => $totalPaid,
                        'remaining_amount'                => $remainingAmount,
                        'is_fully_paid'                   => $isFullyPaid,
                        'currency'                        => $currency,
                        'target_university'               => $enrollment->target_university ?? 'Non spécifiée',
                        'city_name'                       => $enrollment->city?->name ?? 'Brasília',
                        'city_state'                      => $enrollment->city?->state_code ?? 'DF',
                        'status'                          => $enrollment->status ?? 'pending_payment',
                        'status_reason'                   => $translator->translate($enrollment->status_reason ?? 'Inscription enregistrée', $lang),
                        'rhythm'                          => $enrollment->rhythm ?? 'day',
                        'enrolled_at'                     => $enrollment->enrolled_at ? ($enrollment->enrolled_at instanceof \DateTimeInterface ? $enrollment->enrolled_at->format('d/m/Y') : date('d/m/Y', strtotime($enrollment->enrolled_at))) : null,
                        'installments'                    => $installments,
                        'payments'                        => $paymentsList,
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
            'official_documents' => \App\Models\VisaDocument::with('file')
                ->whereHas('visaApplication', fn ($q) => $q->where('student_id', $this->id))
                ->orderByDesc('issued_at')
                ->orderByDesc('id')
                ->get()
                ->map(function ($doc) {
                    $path = $doc->file?->storage_path;
                    return [
                        'id'        => $doc->id,
                        'title'     => $doc->title,
                        'file_name' => $doc->file?->original_name,
                        'file_url'  => $path ? asset('storage/' . ltrim($path, '/')) : null,
                        'issued_at' => $doc->issued_at ? $doc->issued_at->format('d/m/Y') : null,
                    ];
                })->values(),
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
