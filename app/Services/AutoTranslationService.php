<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AutoTranslationService
{
    /**
     * Dictionnaire instantané (0ms) pour les valeurs académiques de base.
     */
    protected static array $dictionary = [
        'en' => [
            'Niveau A1' => 'Level A1',
            'Niveau A2' => 'Level A2',
            'Niveau B1' => 'Level B1',
            'Niveau B2' => 'Level B2',
            'Niveau A1 — Débutant' => 'Level A1 — Beginner',
            'Niveau A2 — Élémentaire' => 'Level A2 — Elementary',
            'Niveau B1 — Intermédiaire' => 'Level B1 — Intermediate',
            'Niveau B2 — Avancé' => 'Level B2 — Advanced',
            'Préparation Intensive CELPE-Bras' => 'Intensive CELPE-Bras Preparation',
            'Paiement en une fois (comptant)' => 'Single payment (in full)',
            'Paiement échelonné (3 mensualités)' => 'Installment plan (3 monthly payments)',
            'Université Fédérale de Brasília' => 'Federal University of Brasília',
            'Campus Oscar Niemeyer • Admission sur dossier CELPE-Bras' => 'Oscar Niemeyer Campus • Direct CELPE-Bras admission',
            'Top 5 Fédéral' => 'Top 5 Federal',
            'Université d’État de São Paulo' => 'University of São Paulo',
            'Leader mondial de recherche et d’innovation académique' => 'Global leader in research and academic innovation',
            'N°1 Amérique Latine' => '#1 Latin America',
            'Université d’État de Campinas' => 'State University of Campinas',
            '15% de l’ensemble des brevets tech et recherche du Brésil' => '15% of all tech patents and research in Brazil',
            'Top 3 Recherche' => 'Top 3 Research',
            'Université Fédérale de Rio de Janeiro' => 'Federal University of Rio de Janeiro',
            'Plus ancienne et illustre institution fédérale du Brésil' => 'Oldest and most prestigious federal institution in Brazil',
            'Pôle d’Excellence' => 'Center of Excellence',
            'Université Fédérale de Minas Gerais' => 'Federal University of Minas Gerais',
            'Campus d’excellence de 8,7 millions de m² à Pampulha' => '8.7 million m² campus of excellence in Pampulha',
            'Top 5 National' => 'Top 5 National',
            'Université Fédérale du Rio Grande do Sul' => 'Federal University of Rio Grande do Sul',
            'Centre universitaire de pointe du Sud brésilien' => 'Leading technological university hub in Southern Brazil',
            'Pôle Technologique' => 'Tech Hub',
            "Inscription : mes projets d'avenir" => 'Application: my future study plans',
            "Formulaire d'inscription initial" => 'Initial application form',
            'Prénom' => 'First Name',
            'Nom' => 'Last Name',
            'Téléphone' => 'Phone',
            'Pays de résidence' => 'Country of residence',
            'Adresse' => 'Address',
            'Où souhaitez-vous suivre vos cours ?' => 'Where would you like to attend your classes?',
            'Dans mon pays (cours en ligne à distance)' => 'In my home country (remote online classes)',
            'Au Brésil (en présentiel)' => 'In Brazil (in-person classes)',
            'Ville du Brésil souhaitée' => 'Desired city in Brazil',
            'Cours du jour ou du soir ?' => 'Daytime or evening classes?',
            'Cours du jour' => 'Daytime classes',
            'Cours du soir' => 'Evening classes',
            "Quel est votre projet d'avenir ?" => 'What is your future study goal?',
            'Pourquoi souhaitez-vous apprendre le portugais brésilien ?' => 'Why do you want to learn Brazilian Portuguese?',
            'Remboursement en cas de refus de visa par les autorités' => 'Refund in case of visa refusal by the consular authorities',
        ],
        'pt' => [
            'Niveau A1' => 'Nível A1',
            'Niveau A2' => 'Nível A2',
            'Niveau B1' => 'Nível B1',
            'Niveau B2' => 'Nível B2',
            'Niveau A1 — Débutant' => 'Nível A1 — Iniciante',
            'Niveau A2 — Élémentaire' => 'Nível A2 — Básico',
            'Niveau B1 — Intermédiaire' => 'Nível B1 — Intermediário',
            'Niveau B2 — Avancé' => 'Nível B2 — Avançado',
            'Préparation Intensive CELPE-Bras' => 'Preparação Intensiva CELPE-Bras',
            'Paiement en une fois (comptant)' => 'Pagamento à vista (parcela única)',
            'Paiement échelonné (3 mensualités)' => 'Pagamento parcelado (3 mensalidades)',
            'Université Fédérale de Brasília' => 'Universidade Federal de Brasília',
            'Campus Oscar Niemeyer • Admission sur dossier CELPE-Bras' => 'Campus Oscar Niemeyer • Admissão via certificado CELPE-Bras',
            'Top 5 Fédéral' => 'Top 5 Federal',
            'Université d’État de São Paulo' => 'Universidade de São Paulo',
            'Leader mondial de recherche et d’innovation académique' => 'Líder mundial em pesquisa e inovação acadêmica',
            'N°1 Amérique Latine' => 'Nº 1 América Latina',
            'Université d’État de Campinas' => 'Universidade Estadual de Campinas',
            '15% de l’ensemble des brevets tech et recherche du Brésil' => '15% de todas as patentes tecnológicas e pesquisas do Brasil',
            'Top 3 Recherche' => 'Top 3 Pesquisa',
            'Université Fédérale de Rio de Janeiro' => 'Universidade Federal do Rio de Janeiro',
            'Plus ancienne et illustre institution fédérale du Brésil' => 'A mais antiga e prestigiada instituição federal do Brasil',
            'Pôle d’Excellence' => 'Polo de Excelência',
            'Université Fédérale de Minas Gerais' => 'Universidade Federal de Minas Gerais',
            'Campus d’excellence de 8,7 millions de m² à Pampulha' => 'Campus de excelência com 8,7 milhões de m² na Pampulha',
            'Top 5 National' => 'Top 5 Nacional',
            'Université Fédérale du Rio Grande do Sul' => 'Universidade Federal do Rio Grande do Sul',
            'Centre universitaire de pointe du Sud brésilien' => 'Polo universitário tecnológico de ponta no Sul do Brasil',
            'Pôle Technologique' => 'Polo Tecnológico',
            "Inscription : mes projets d'avenir" => 'Inscrição: meus projetos de futuro',
            "Formulaire d'inscription initial" => 'Formulário de inscrição inicial',
            'Prénom' => 'Nome',
            'Nom' => 'Sobrenome',
            'Téléphone' => 'Telefone',
            'Pays de résidence' => 'País de residência',
            'Adresse' => 'Endereço',
            'Où souhaitez-vous suivre vos cours ?' => 'Onde você deseja assistir às aulas?',
            'Dans mon pays (cours en ligne à distance)' => 'No meu país (aulas online à distância)',
            'Au Brésil (en présentiel)' => 'No Brasil (presencial)',
            'Ville du Brésil souhaitée' => 'Cidade desejada no Brasil',
            'Cours du jour ou du soir ?' => 'Aulas diurnas ou noturnas?',
            'Cours du jour' => 'Aulas diurnas',
            'Cours du soir' => 'Aulas noturnas',
            "Quel est votre projet d'avenir ?" => 'Qual é o seu projeto de futuro?',
            'Pourquoi souhaitez-vous apprendre le portugais brésilien ?' => 'Por que você deseja aprender português brasileiro?',
            'Remboursement en cas de refus de visa par les autorités' => 'Reembolso em caso de recusa de visto pelas autoridades consulares',
        ],
    ];

    /**
     * Détermine la langue demandée par le client (fr, en, pt).
     */
    public function resolveLocale(?Request $request = null): string
    {
        $request = $request ?? request();
        $raw = strtolower((string) ($request->query('lang') ?? $request->query('locale') ?? $request->header('X-Locale') ?? $request->header('Accept-Language', 'fr')));

        if (str_starts_with($raw, 'en')) {
            return 'en';
        }
        if (str_starts_with($raw, 'pt')) {
            return 'pt';
        }

        return 'fr';
    }

    /**
     * Traduit automatiquement toute donnée saisie par l'admin en français vers EN ou PT-BR,
     * avec mise en cache permanente en base de données.
     */
    public function translate(?string $text, string $lang): ?string
    {
        if ($text === null || trim($text) === '' || $lang === 'fr') {
            return $text;
        }

        $trimmed = trim($text);

        if (isset(self::$dictionary[$lang][$trimmed])) {
            return self::$dictionary[$lang][$trimmed];
        }

        $cacheKey = 'auto_i18n_v2_' . $lang . '_' . md5($trimmed);

        try {
            $cached = Cache::get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }

            $targetCode = $lang === 'pt' ? 'pt-BR' : 'en';

            // 1. API MyMemory (JSON REST officiel sans clé requise)
            $response = Http::withoutVerifying()
                ->timeout(4)
                ->get('https://api.mymemory.translated.net/get', [
                    'q'        => $trimmed,
                    'langpair' => "fr|{$targetCode}",
                ]);

            if ($response->successful()) {
                $translated = $response->json('responseData.translatedText');
                if (is_string($translated) && trim($translated) !== '' && ! str_contains($translated, 'MYMEMORY WARNING')) {
                    $clean = html_entity_decode(trim($translated), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    Cache::forever($cacheKey, $clean);
                    return $clean;
                }
            }
        } catch (\Throwable) {
            // Repli silencieux sur le texte d'origine en cas d'indisponibilité réseau
        }

        return $trimmed;
    }
}
