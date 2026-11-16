<?php

namespace App\DTOs;

readonly class SubmitFormDTO
{
    /**
     * @param int $formId
     * @param int $userId
     * @param array<string, mixed> $answers Keyed by field_key
     */
    public function __construct(
        public int $formId,
        public int $userId,
        public array $answers
    ) {}
}
