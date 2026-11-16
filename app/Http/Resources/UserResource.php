<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
            'enrollments'      => $this->whenLoaded('enrollments', function () {
                return $this->enrollments->map(function ($enrollment) {
                    return [
                        'id'                => $enrollment->id,
                        'level_id'          => $enrollment->level_id,
                        'level_name'        => $enrollment->level?->name ?? 'Niveau A1',
                        'study_mode'        => $enrollment->offer?->study_mode ?? 'in_person_brazil',
                        'target_university' => $enrollment->target_university ?? 'Non spécifiée',
                        'city_name'         => $enrollment->city?->name ?? 'Brasília',
                        'city_state'        => $enrollment->city?->state_code ?? 'DF',
                        'status'            => $enrollment->status ?? 'pending_payment',
                        'status_reason'     => $enrollment->status_reason ?? 'Inscription enregistrée',
                        'rhythm'            => $enrollment->rhythm ?? 'day',
                        'enrolled_at'       => $enrollment->enrolled_at ? ($enrollment->enrolled_at instanceof \DateTimeInterface ? $enrollment->enrolled_at->format('d/m/Y') : date('d/m/Y', strtotime($enrollment->enrolled_at))) : null,
                        'installments'      => $enrollment->installments->map(function ($inst) {
                            return [
                                'id'             => $inst->id,
                                'installment_no' => $inst->installment_no,
                                'due_date'       => $inst->due_date ? date('d/m/Y', strtotime($inst->due_date)) : null,
                                'amount'         => (float) $inst->amount,
                                'currency'       => $inst->currency ?? 'EUR',
                                'status'         => $inst->status,
                                'paid_at'        => $inst->paid_at ? date('d/m/Y', strtotime($inst->paid_at)) : null,
                            ];
                        }),
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
