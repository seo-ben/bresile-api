<?php

namespace App\DTOs;

readonly class RegisterStudentDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $firstName,
        public string $lastName,
        public ?string $phone = null,
        public ?string $countryCode = null,
        public string $preferredLocale = 'fr',
        public mixed $levelId = null,
        public ?string $levelChoice = null,
        public ?string $studyMode = null,
        public mixed $brazilCityId = null,
        public ?string $cityChoice = null,
        public ?string $targetUniversity = null,
        public ?string $country = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            phone: $data['phone'] ?? null,
            countryCode: $data['country_code'] ?? null,
            preferredLocale: $data['preferred_locale'] ?? 'fr',
            levelId: $data['level_id'] ?? null,
            levelChoice: $data['level_choice'] ?? null,
            studyMode: $data['study_mode'] ?? null,
            brazilCityId: $data['brazil_city_id'] ?? null,
            cityChoice: $data['city_choice'] ?? null,
            targetUniversity: $data['target_university'] ?? null,
            country: $data['country'] ?? null
        );
    }
}
