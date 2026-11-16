<?php

namespace App\DTOs;

readonly class DeclareVisaOutcomeDTO
{
    public function __construct(
        public int $visaApplicationId,
        public int $userId,
        public string $outcome, // 'granted' | 'refused'
        public ?string $refusalReason = null,
        public ?int $refusalProofFileId = null,
        public ?string $studentBankDetails = null
    ) {}
}
