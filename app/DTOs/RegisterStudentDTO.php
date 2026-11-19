<?php

namespace App\DTOs;

readonly class RegisterStudentDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $firstName,
        public string $lastName,
        public ?string $birthDate = null,
        public ?string $phone = null,
        public ?string $dialCode = null,
        public ?string $phoneNumber = null,
        public ?string $countryCode = null,
        public string $preferredLocale = 'fr',
        public mixed $levelId = null,
        public ?string $levelChoice = null,
        public ?string $studyMode = null,
        public ?string $studyPurpose = null,
        public mixed $brazilCityId = null,
        public ?string $cityChoice = null,
        public ?string $targetUniversity = null,
        public ?string $country = null,
        public ?string $previousLevelCertificatePath = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            birthDate: $data['birth_date'] ?? null,
            phone: $data['phone'] ?? null,
            dialCode: $data['dial_code'] ?? null,
            phoneNumber: $data['phone_number'] ?? null,
            countryCode: $data['country_code'] ?? null,
            preferredLocale: $data['preferred_locale'] ?? 'fr',
            levelId: $data['level_id'] ?? null,
            levelChoice: $data['level_choice'] ?? null,
            studyMode: $data['study_mode'] ?? null,
            studyPurpose: $data['study_purpose'] ?? null,
            brazilCityId: $data['brazil_city_id'] ?? null,
            cityChoice: $data['city_choice'] ?? null,
            targetUniversity: $data['target_university'] ?? null,
            country: $data['country'] ?? null,
            previousLevelCertificatePath: $data['previous_level_certificate_path'] ?? null
        );
    }
}
