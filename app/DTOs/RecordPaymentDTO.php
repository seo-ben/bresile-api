<?php

namespace App\DTOs;

readonly class RecordPaymentDTO
{
    public function __construct(
        public int $userId,
        public int $enrollmentId,
        public ?int $installmentId,
        public float $amount,
        public string $currency,
        public string $paymentMethod,
        public ?string $gatewayReference = null,
        public ?int $proofFileId = null,
        public ?string $notes = null
    ) {}
}
