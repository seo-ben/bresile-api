<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'level'        => [
                'id'   => $this->level?->id,
                'code' => $this->level?->code,
                'name' => $this->level?->name,
            ],
            'city'         => $this->city?->name,
            'study_mode'   => $this->study_mode,
            'rhythm'       => $this->rhythm,
            'status'       => $this->status,
            'total_amount' => (float) $this->total_amount,
            'currency'     => $this->currency,
            'installments' => $this->installments->map(fn($inst) => [
                'id'             => $inst->id,
                'installment_no' => $inst->installment_no,
                'title'          => "Tranche {$inst->installment_no}",
                'amount'         => (float) $inst->amount,
                'due_date'       => $inst->due_date?->toDateString(),
                'status'         => $inst->status,
            ]),
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
