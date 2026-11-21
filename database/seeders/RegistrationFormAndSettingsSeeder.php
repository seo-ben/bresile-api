<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegistrationFormAndSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Formulaire d'inscription principal
        $form = DB::table('forms')->where('purpose', 'registration')->first();
        if (! $form) {
            $formId = DB::table('forms')->insertGetId([
                'purpose'     => 'registration',
                'title'       => "Inscription : mes projets d'avenir",
                'description' => "Formulaire d'inscription initial",
                'is_active'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } else {
            $formId = $form->id;
        }

        // 2. Champs du formulaire d'inscription (10 champs)
        $fields = [
            [
                'field_key'                => 'first_name',
                'label'                    => 'Prénom',
                'field_type'               => 'text',
                'system_key'               => 'first_name',
                'is_required'              => 1,
                'is_editable_after_submit' => 0,
                'is_active'                => 1,
                'position'                 => 1,
                'visible_if'               => null,
            ],
            [
                'field_key'                => 'last_name',
                'label'                    => 'Nom',
                'field_type'               => 'text',
                'system_key'               => 'last_name',
                'is_required'              => 1,
                'is_editable_after_submit' => 0,
                'is_active'                => 1,
                'position'                 => 2,
                'visible_if'               => null,
            ],
            [
                'field_key'                => 'phone',
                'label'                    => 'Téléphone',
                'field_type'               => 'phone',
                'system_key'               => 'phone',
                'is_required'              => 1,
                'is_editable_after_submit' => 1,
                'is_active'                => 1,
                'position'                 => 3,
                'visible_if'               => null,
            ],
            [
                'field_key'                => 'country',
                'label'                    => 'Pays de résidence',
                'field_type'               => 'country',
                'system_key'               => 'country_code',
                'is_required'              => 1,
                'is_editable_after_submit' => 1,
                'is_active'                => 1,
                'position'                 => 4,
                'visible_if'               => null,
            ],
            [
                'field_key'                => 'address',
                'label'                    => 'Adresse',
                'field_type'               => 'textarea',
                'system_key'               => null,
                'is_required'              => 1,
                'is_editable_after_submit' => 1,
                'is_active'                => 1,
                'position'                 => 5,
                'visible_if'               => null,
            ],
            [
                'field_key'                => 'study_mode',
                'label'                    => 'Où souhaitez-vous suivre vos cours ?',
                'field_type'               => 'select',
                'system_key'               => 'study_mode',
                'is_required'              => 1,
                'is_editable_after_submit' => 1,
                'is_active'                => 1,
                'position'                 => 6,
                'visible_if'               => null,
                'options'                  => [
                    [
                        'option_value' => 'remote_home_country',
                        'option_label' => 'Dans mon pays (cours en ligne à distance)',
                        'position'     => 1,
                        'is_active'    => 1,
                    ],
                    [
                        'option_value' => 'in_person_brazil',
                        'option_label' => 'Au Brésil (en présentiel)',
                        'position'     => 2,
                        'is_active'    => 1,
                    ],
                ],
            ],
            [
                'field_key'                => 'brazil_city',
                'label'                    => 'Ville du Brésil souhaitée',
                'field_type'               => 'brazil_city',
                'system_key'               => 'brazil_city_id',
                'is_required'              => 0,
                'is_editable_after_submit' => 1,
                'is_active'                => 1,
                'position'                 => 7,
                'visible_if'               => json_encode(['field' => 'study_mode', 'equals' => 'in_person_brazil']),
            ],
            [
                'field_key'                => 'rhythm',
                'label'                    => 'Cours du jour ou du soir ?',
                'field_type'               => 'select',
                'system_key'               => 'rhythm',
                'is_required'              => 1,
                'is_editable_after_submit' => 1,
                'is_active'                => 1,
                'position'                 => 8,
                'visible_if'               => null,
                'options'                  => [
                    [
                        'option_value' => 'day',
                        'option_label' => 'Cours du jour',
                        'position'     => 1,
                        'is_active'    => 1,
                    ],
                    [
                        'option_value' => 'evening',
                        'option_label' => 'Cours du soir',
                        'position'     => 2,
                        'is_active'    => 1,
                    ],
                ],
            ],
            [
                'field_key'                => 'future_project',
                'label'                    => "Quel est votre projet d'avenir ?",
                'field_type'               => 'textarea',
                'system_key'               => null,
                'is_required'              => 1,
                'is_editable_after_submit' => 1,
                'is_active'                => 1,
                'position'                 => 9,
                'visible_if'               => null,
            ],
            [
                'field_key'                => 'reason',
                'label'                    => 'Pourquoi souhaitez-vous apprendre le portugais brésilien ?',
                'field_type'               => 'textarea',
                'system_key'               => null,
                'is_required'              => 1,
                'is_editable_after_submit' => 1,
                'is_active'                => 1,
                'position'                 => 10,
                'visible_if'               => null,
            ],
        ];

        foreach ($fields as $f) {
            $options = $f['options'] ?? [];
            unset($f['options']);

            $existingField = DB::table('form_fields')
                ->where('form_id', $formId)
                ->where('field_key', $f['field_key'])
                ->first();

            if (! $existingField) {
                $fieldId = DB::table('form_fields')->insertGetId(array_merge($f, [
                    'form_id'    => $formId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                $fieldId = $existingField->id;
            }

            foreach ($options as $opt) {
                $existsOpt = DB::table('form_field_options')
                    ->where('field_id', $fieldId)
                    ->where('option_value', $opt['option_value'])
                    ->exists();

                if (! $existsOpt) {
                    DB::table('form_field_options')->insert(array_merge($opt, [
                        'field_id' => $fieldId,
                    ]));
                }
            }
        }

        // 3. Règles académiques par niveau (level_rules)
        $levels = DB::table('levels')->pluck('id');
        foreach ($levels as $levelId) {
            if (! DB::table('level_rules')->where('level_id', $levelId)->exists()) {
                DB::table('level_rules')->insert([
                    'level_id'                  => $levelId,
                    'max_absence_minutes'       => 240,
                    'absence_period'            => 'per_level',
                    'count_justified_absences'  => 0,
                    'absence_failure_refund'    => 'no_refund',
                    'absence_failure_refund_pct'=> null,
                    'grade_scale_max'           => 20.00,
                    'passing_average'           => 12.00,
                    'below_average_action'      => 'retake_exam',
                    'payment_grace_days'        => 0,
                    'updated_at'                => now(),
                ]);
            }
        }

        // 4. Politique de remboursement 85% (refund_policies)
        if (! DB::table('refund_policies')->where('code', 'visa_refusal')->exists()) {
            DB::table('refund_policies')->insert([
                'code'                  => 'visa_refusal',
                'description'           => 'Remboursement en cas de refus de visa par les autorités',
                'refund_percent'        => 85.00,
                'request_deadline_days' => null,
                'is_active'             => 1,
            ]);
        }

        // 5. Paramètres système (app_settings)
        // Copier le logo par défaut dans storage/app/public/site_branding/logo.png s'il n'existe pas encore
        $defaultLogoDest = storage_path('app/public/site_branding/logo.png');
        $sourceLogo = public_path('logo.png');
        if (file_exists($sourceLogo) && ! file_exists($defaultLogoDest)) {
            if (! is_dir(dirname($defaultLogoDest))) {
                mkdir(dirname($defaultLogoDest), 0755, true);
            }
            @copy($sourceLogo, $defaultLogoDest);
        }

        $settings = [
            [
                'setting_key' => 'bank_transfer.confirmation_delay_hours',
                'value_json'  => '72',
                'description' => 'Délai (en heures) pour confirmer un virement bancaire',
            ],
            [
                'setting_key' => 'registration.require_terms_acceptance',
                'value_json'  => 'true',
                'description' => "Exiger l'acceptation des conditions à l'inscription",
            ],
            [
                'setting_key' => 'system.default_currency',
                'value_json'  => '"EUR"',
                'description' => 'Devise active globale du système (EUR, XOF/FCFA ou BRL/R$)',
            ],
            [
                'setting_key' => 'site.branding',
                'value_json'  => json_encode([
                    'site_name'       => 'Brasil·É',
                    'tagline_fr'      => 'ÉCOLE DE PORTUGAIS & MOBILITÉ AU BRÉSIL',
                    'tagline_en'      => 'PORTUGUESE SCHOOL & STUDENT MOBILITY IN BRAZIL',
                    'tagline_pt'      => 'ESCOLA DE PORTUGUÊS & MOBILIDADE NO BRASIL',
                    'logo_path'       => file_exists($defaultLogoDest) ? 'site_branding/logo.png' : null,
                    'contact_email'   => 'contact@brasil-e.com',
                    'contact_phone'   => '+55 61 99999-0000',
                    'contact_address' => 'Brasília, Distrito Federal — Brésil',
                    'website_url'     => 'www.brasil-e.com',
                ], JSON_UNESCAPED_UNICODE),
                'description' => 'Identité du site : Logo, nom, slogan multilingue (FR/EN/PT) et coordonnées de contact',
            ],
        ];

        foreach ($settings as $s) {
            if (! DB::table('app_settings')->where('setting_key', $s['setting_key'])->exists()) {
                DB::table('app_settings')->insert(array_merge($s, [
                    'updated_at' => now(),
                ]));
            }
        }

        // 5b. Remplir les 3 tranches par défaut sur les level_offers existantes qui ne sont pas encore configurées
        $offersWithoutInstallments = DB::table('level_offers')
            ->whereNull('installment_1_amount')
            ->get();

        foreach ($offersWithoutInstallments as $offer) {
            $base = (float) $offer->base_price;
            $t2 = floor(($base / 3) / 5) * 5;
            if ($t2 <= 0) {
                $t2 = round($base / 3, 2);
            }
            $t3 = $t2;
            $t1 = round($base - $t2 - $t3, 2);

            DB::table('level_offers')
                ->where('id', $offer->id)
                ->update([
                    'installment_1_amount' => $t1,
                    'installment_2_amount' => $t2,
                    'installment_3_amount' => $t3,
                ]);
        }

        // 6. Comptes bancaires (bank_accounts)
        $accounts = [
            [
                'label'          => 'Compte Principal Virement FCFA (UEMOA)',
                'bank_name'      => 'Ecobank Bénin',
                'account_holder' => 'Brasil·É Mobilité & Langue',
                'account_number' => 'BJ0610100100145896321045',
                'swift_bic'      => 'ECOCBJBJ',
                'country_code'   => 'BJ',
                'currency'       => 'XOF',
                'instructions'   => 'Virement bancaire en FCFA. Indiquer le Nom et Prénom de l’étudiant en libellé.',
                'is_active'      => 1,
            ],
            [
                'label'          => 'Compte International Virement Euros (€)',
                'bank_name'      => 'Société Générale (Zone SEPA)',
                'account_holder' => 'Brasil·É Escola & Mobilidade',
                'account_number' => 'FR7630006000011234567890189',
                'swift_bic'      => 'SOGEFR2A',
                'country_code'   => 'FR',
                'currency'       => 'EUR',
                'instructions'   => 'Virement international en Euros. Indiquer le Nom et Prénom de l’étudiant.',
                'is_active'      => 1,
            ],
            [
                'label'          => 'Compte Brésil Pix / Real Brésilien (R$)',
                'bank_name'      => 'Banco do Brasil',
                'account_holder' => 'Brasil·É Escola de Idiomas LTDA',
                'account_number' => 'PIX-BRASILE-DF-2026',
                'swift_bic'      => 'BRASBRRJ',
                'country_code'   => 'BR',
                'currency'       => 'BRL',
                'instructions'   => 'Paiement local au Brésil via Pix ou TED en Reais (R$). Indiquer le nom de l’étudiant.',
                'is_active'      => 1,
            ],
        ];

        foreach ($accounts as $acc) {
            if (! DB::table('bank_accounts')->where('account_number', $acc['account_number'])->exists()) {
                DB::table('bank_accounts')->insert(array_merge($acc, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 7. Page "À Propos" (cms_contents, clé: about_us) — éditable par l'admin dans Contenus du Site Web
        if (! DB::table('cms_contents')->where('content_key', 'about_us')->where('locale', 'fr')->exists()) {
            DB::table('cms_contents')->insert([
                'content_key'  => 'about_us',
                'locale'       => 'fr',
                'title'        => 'À propos de Brasil·É — Notre mission et nos engagements',
                'body'         => <<<'HTML'
<h2>Pourquoi nous avons créé ce projet</h2>
<p><strong>Brasil·É</strong> est né d’un constat simple : de nombreux étudiants francophones et internationaux rêvent d’apprendre le portugais brésilien et de poursuivre leurs études ou projets professionnels au Brésil, mais se heurtent à des démarches consulaires complexes, à un manque de transparence financière et à l’absence d’un encadrement fiable sur place.</p>
<p>Notre mission est d’offrir une passerelle sérieuse, structurée et humaine entre votre pays d’origine et le Brésil, du premier cours de portugais jusqu’à votre installation et votre intégration universitaire ou professionnelle.</p>

<h2>Une formation reconnue et un cadre officiel</h2>
<ul>
  <li><strong>Cursus progressif CECRL (A1, A2, B1, B2)</strong> : en présentiel au Brésil (Visa VITEM IV ou Visa Visiteur) ou 100 % en ligne depuis votre pays.</li>
  <li><strong>Préparation officielle au diplôme CELPE-Bras</strong> : le seul certificat de maîtrise du portugais brésilien officiellement reconnu par le Ministère de l’Éducation du Brésil (MEC / INEP) et exigé par les grandes universités fédérales.</li>
  <li><strong>Passerelle vers les meilleures universités brésiliennes</strong> : UnB Brasília, USP São Paulo, UNICAMP, UFRJ, UFMG, UFRGS.</li>
</ul>

<h2>Nos garanties pour rassurer les étudiants et leurs familles</h2>
<ul>
  <li><strong>Garantie contractuelle de remboursement 85 %</strong> : en cas de refus officiel de visa par le consulat, 85 % des frais de scolarité réglés sont restitués par virement sous 14 jours ouvrés.</li>
  <li><strong>Transparence totale des paiements</strong> : paiement comptant ou échelonné en 3 tranches, avec reçus officiels téléchargeables instantanément dans votre espace étudiant.</li>
  <li><strong>Accompagnement de bout en bout</strong> : attestations officielles conformes pour le consulat, suivi du dossier visa en ligne, accueil et aide à l’installation au Brésil (logement, CPF, enregistrement Police Fédérale).</li>
</ul>

<h2>Une présence multi-campus au Brésil</h2>
<p>Nos équipes pédagogiques et administratives vous accompagnent dans les grands pôles universitaires brésiliens : <strong>Brasília (DF)</strong>, <strong>São Paulo (SP)</strong>, <strong>Rio de Janeiro (RJ)</strong>, <strong>Belo Horizonte (MG)</strong>, <strong>Porto Alegre (RS)</strong>, ainsi que sur notre <strong>Campus en Ligne</strong>.</p>
HTML,
                'is_published' => 1,
                'version'      => 1,
                'updated_by'   => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
