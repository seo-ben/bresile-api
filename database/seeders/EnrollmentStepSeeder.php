<?php

namespace Database\Seeders;

use App\Models\EnrollmentStep;
use Illuminate\Database\Seeder;

class EnrollmentStepSeeder extends Seeder
{
    public function run(): void
    {
        EnrollmentStep::ensureTableExists();

        if (EnrollmentStep::count() > 0) {
            return;
        }

        $steps = [
            [
                'position'      => 1,
                'title'         => 'Inscription & Dossier en Ligne',
                'subtitle'      => 'Création de votre espace étudiant, choix du niveau CECRL (A1 à B2) et de votre objet d’étude.',
                'icon'          => 'file-signature',
                'accent_color'  => 'navy',
                'branches_json' => [
                    ['label' => 'Niveau A1 : Accès direct', 'tone' => 'emerald'],
                    ['label' => 'Niveaux A2 / B1 / B2 : Justificatif du niveau précédent', 'tone' => 'sky'],
                ],
                'is_active'     => true,
            ],
            [
                'position'      => 2,
                'title'         => 'Choix du Parcours & Visa',
                'subtitle'      => 'Sélectionnez la formule adaptée à votre projet académique ou professionnel au Brésil.',
                'icon'          => 'compass',
                'accent_color'  => 'sky',
                'branches_json' => [
                    ['label' => 'Présentiel Brésil — Visa VITEM IV', 'tone' => 'emerald'],
                    ['label' => 'Présentiel Brésil — Visa Visiteur', 'tone' => 'amber'],
                    ['label' => '100 % En Ligne depuis votre pays', 'tone' => 'sky'],
                ],
                'is_active'     => true,
            ],
            [
                'position'      => 3,
                'title'         => 'Frais de Service & Scolarité',
                'subtitle'      => 'Paiement sécurisé par carte bancaire ou virement avec reçus officiels PDF immédiats.',
                'icon'          => 'credit-card',
                'accent_color'  => 'amber',
                'branches_json' => [
                    ['label' => 'Frais de Service (Plateforme & Démarches)', 'tone' => 'sky'],
                    ['label' => 'Scolarité : Comptant (1 fois) ou 3 Tranches', 'tone' => 'emerald'],
                ],
                'is_active'     => true,
            ],
            [
                'position'      => 4,
                'title'         => 'Documents Officiels & Consulat',
                'subtitle'      => 'L’école délivre vos attestations officielles et suit votre demande de visa étape par étape.',
                'icon'          => 'passport',
                'accent_color'  => 'emerald',
                'branches_json' => [
                    ['label' => 'Visa Accordé → Départ & Cours au Brésil', 'tone' => 'emerald'],
                    ['label' => 'Visa Refusé → Garantie 85 % remboursés sous 14 jours', 'tone' => 'rose'],
                ],
                'is_active'     => true,
            ],
            [
                'position'      => 5,
                'title'         => 'Intégration & Diplôme CELPE-Bras',
                'subtitle'      => 'Accueil sur campus (logement, CPF, Police Fédérale) et passerelle vers les universités fédérales.',
                'icon'          => 'graduation-cap',
                'accent_color'  => 'navy',
                'branches_json' => [
                    ['label' => 'UnB, USP, UNICAMP, UFRJ, UFMG, UFRGS', 'tone' => 'emerald'],
                ],
                'is_active'     => true,
            ],
        ];

        foreach ($steps as $step) {
            EnrollmentStep::create($step);
        }
    }
}
