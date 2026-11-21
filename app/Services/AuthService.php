<?php

namespace App\Services;

use App\DTOs\RegisterStudentDTO;
use App\Models\BrazilCity;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\LevelOffer;
use App\Models\OfferPaymentPlan;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\VisaApplication;
use App\Models\WhatsappLead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Register a new student with transaction safety, audit trail, and automatic Enrollment dossier.
     */
    public function registerStudent(RegisterStudentDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            // 1. Création du compte utilisateur
            $user = User::create([
                'email'             => strtolower(trim($dto->email)),
                'password_hash'     => Hash::make($dto->password),
                'role'              => 'student',
                'status'            => 'active',
                'preferred_locale'  => $dto->preferredLocale,
                'email_verified_at' => now(),
            ]);

            // 2. Création du profil utilisateur (résolution du code ISO 2 lettres pour country_code)
            $countryIsoMap = [
                'Sénégal'       => 'SN',
                'Senegal'       => 'SN',
                "Côte d'Ivoire" => 'CI',
                'Cameroun'      => 'CM',
                'Guinée'        => 'GN',
                'Guinee'        => 'GN',
                'Mali'          => 'ML',
                'Bénin'         => 'BJ',
                'Benin'         => 'BJ',
                'Togo'          => 'TG',
                'Congo'         => 'CG',
                'RD Congo'      => 'CD',
                'Burkina Faso'  => 'BF',
                'Gabon'         => 'GA',
                'Brésil'        => 'BR',
                'Bresil'        => 'BR',
                'France'        => 'FR',
            ];

            $isoCode = null;
            if ($dto->countryCode && strlen($dto->countryCode) === 2) {
                $isoCode = strtoupper($dto->countryCode);
            } elseif ($dto->country && isset($countryIsoMap[$dto->country])) {
                $isoCode = $countryIsoMap[$dto->country];
            } elseif ($dto->country) {
                $clean = strtoupper(preg_replace('/[^a-zA-Z]/', '', $dto->country));
                $isoCode = substr($clean, 0, 2);
            }

            $profile = UserProfile::create([
                'user_id'      => $user->id,
                'first_name'   => $dto->firstName,
                'last_name'    => $dto->lastName,
                'birth_date'   => $dto->birthDate,
                'phone'        => $dto->phone,
                'country_code' => $isoCode ?: 'SN',
            ]);

            // 2b. Enregistrement automatique du numéro dans "Contacts WhatsApp reçus depuis le site"
            if (! empty($dto->phone)) {
                try {
                    WhatsappLead::ensureTableExists();
                    $dialCode = $dto->dialCode;
                    $phoneOnly = $dto->phoneNumber;
                    if (! $dialCode && preg_match('/^(\+\d{1,4})\s*(.*)$/', trim($dto->phone), $m)) {
                        $dialCode = $m[1];
                        $phoneOnly = trim($m[2]);
                    }
                    $dialCode = $dialCode ?: '+';
                    $phoneOnly = $phoneOnly ?: trim($dto->phone);
                    $fullWhatsapp = trim($dto->phone);

                    WhatsappLead::create([
                        'dial_code'     => $dialCode,
                        'country_name'  => $dto->country,
                        'phone_number'  => $phoneOnly,
                        'full_whatsapp' => $fullWhatsapp,
                        'locale'        => $dto->preferredLocale ?: 'fr',
                        'status'        => 'enrolled',
                        'admin_note'    => trim("Inscription site : {$dto->firstName} {$dto->lastName} ({$dto->email}) — Niveau : " . ($dto->levelChoice ?: 'A1') . ($dto->studyPurpose ? " — Objet : {$dto->studyPurpose}" : '')),
                    ]);
                } catch (\Throwable) {
                    // Ne jamais bloquer l'inscription si la journalisation WhatsApp échoue
                }
            }

            // 3. Résolution du Niveau d'études (Level)
            $level = null;
            if ($dto->levelId && is_numeric($dto->levelId)) {
                $level = Level::find($dto->levelId);
            }
            if (!$level && $dto->levelChoice) {
                if (preg_match('/\b(A1|A2|B1|B2|C1|C2|CELPE-Bras)\b/i', $dto->levelChoice, $matches)) {
                    $codeMatch = strtoupper($matches[1]);
                    $level = Level::where('code', $codeMatch)->first();
                }
                if (!$level) {
                    $level = Level::where('name', 'like', "%{$dto->levelChoice}%")->first();
                }
            }
            if (!$level) {
                $level = Level::orderBy('position')->first();
            }

            // 4. Résolution du Mode d'apprentissage (Présentiel Visa VITEM IV, Visa Visiteur, ou En ligne)
            $rawMode = strtolower($dto->studyMode ?? '');
            if (str_contains($rawMode, 'ligne') || str_contains($rawMode, 'remote')) {
                $studyMode = 'remote_home_country';
                $visaType = null;
            } elseif (str_contains($rawMode, 'visiteur') || str_contains($rawMode, 'visitor') || str_contains($rawMode, 'visitante')) {
                $studyMode = 'visitor_visa_brazil';
                $visaType = 'visitor_visa';
            } else {
                $studyMode = 'in_person_brazil';
                $visaType = 'vitem_iv';
            }

            // 5. Résolution de l'Offre académique (LevelOffer)
            $offer = null;
            if ($level) {
                $offer = LevelOffer::where('level_id', $level->id)
                    ->where('study_mode', $studyMode)
                    ->first()
                    ?? ($studyMode === 'visitor_visa_brazil'
                        ? LevelOffer::where('level_id', $level->id)->where('study_mode', 'in_person_brazil')->first()
                        : null)
                    ?? LevelOffer::where('level_id', $level->id)->first();
            }
            if (!$offer) {
                $offer = LevelOffer::first();
            }

            // 6. Résolution de la Ville d'accueil (BrazilCity)
            $city = null;
            if (in_array($studyMode, ['in_person_brazil', 'visitor_visa_brazil'], true)) {
                if ($dto->brazilCityId && is_numeric($dto->brazilCityId)) {
                    $city = BrazilCity::find($dto->brazilCityId);
                }
                if (!$city && $dto->cityChoice) {
                    $cleanCity = trim(explode('(', $dto->cityChoice)[0]);
                    $city = BrazilCity::where('name', 'like', "%{$cleanCity}%")->first();
                }
                if (!$city) {
                    $city = BrazilCity::first();
                }
            }

            // 7. Plan de paiement par défaut
            $plan = $offer ? OfferPaymentPlan::where('offer_id', $offer->id)->first() : null;

            // 8. CRÉATION DU DOSSIER OFFICIEL D'INSCRIPTION (ENROLLMENT)
            // C'est ce qui apparaît dans le panneau Filament de l'administrateur sous "Dossiers d’Inscription"
            if ($level && $offer) {
                $enrollment = Enrollment::create([
                    'student_id'                      => $user->id,
                    'level_id'                        => $level->id,
                    'offer_id'                        => $offer->id,
                    'payment_plan_id'                 => $plan?->id,
                    'brazil_city_id'                  => $city?->id,
                    'target_university'               => $dto->targetUniversity,
                    'country_residence'               => $dto->country,
                    'study_purpose'                   => $dto->studyPurpose,
                    'visa_type'                       => $visaType,
                    'previous_level_certificate_path' => $dto->previousLevelCertificatePath,
                    'rhythm'                          => $dto->rhythm === 'evening' ? 'evening' : 'day',
                    'status'                          => 'pending_payment',
                    'status_reason'                   => 'Nouvelle inscription enregistrée via la plateforme web Brasil·É',
                    'enrolled_at'                     => now(),
                ]);

                // Initialisation du suivi de Visa pour les étudiants en présentiel (VITEM IV ou Visa Visiteur)
                if (in_array($studyMode, ['in_person_brazil', 'visitor_visa_brazil'], true)) {
                    VisaApplication::create([
                        'student_id'    => $user->id,
                        'enrollment_id' => $enrollment->id,
                        'status'        => 'draft',
                        'visa_outcome'  => 'pending',
                    ]);
                }
            }

            // 9. Journalisation d'audit
            $this->auditService->log(
                eventType: 'student_registered',
                entity: $user,
                newValues: ['email' => $user->email, 'role' => $user->role],
                actorId: $user->id
            );

            $token = $user->createToken('student-auth-token')->plainTextToken;

            return [
                'user'    => $user->load(['profile', 'enrollments.level', 'enrollments.city', 'enrollments.offer.paymentPlans', 'enrollments.installments', 'visaApplications']),
                'token'   => $token,
            ];
        });
    }

    /**
     * Authenticate any user (student, teacher, admin) and issue a token.
     */
    public function authenticate(string $email, string $password): array
    {
        $user = User::where('email', strtolower(trim($email)))->first();

        if (!$user || !Hash::check($password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => [__('Les identifiants fournis sont incorrects.')],
            ]);
        }

        if ($user->status === 'suspended') {
            throw ValidationException::withMessages([
                'account' => [__('Votre compte est actuellement suspendu. Veuillez contacter l\'administration.')],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $this->auditService->log(
            eventType: 'user_login',
            entity: $user,
            actorId: $user->id
        );

        $tokenName = match ($user->role) {
            'admin'   => 'admin-token',
            'teacher' => 'teacher-token',
            default   => 'student-token',
        };

        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user'  => $user->load(['profile', 'enrollments.level', 'enrollments.city', 'enrollments.offer.paymentPlans', 'enrollments.installments', 'visaApplications']),
            'token' => $token,
        ];
    }
}
