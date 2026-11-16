<?php

namespace Database\Seeders;

use App\Models\BrazilCity;
use App\Models\Level;
use App\Models\LevelOffer;
use App\Models\OfferPaymentPlan;
use Illuminate\Database\Seeder;

class AcademicReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Niveaux CECRL
        $levelsData = [
            ['code' => 'A1', 'name' => 'Niveau A1 — Débutant', 'position' => 1],
            ['code' => 'A2', 'name' => 'Niveau A2 — Élémentaire', 'position' => 2],
            ['code' => 'B1', 'name' => 'Niveau B1 — Intermédiaire', 'position' => 3],
            ['code' => 'B2', 'name' => 'Niveau B2 — Avancé', 'position' => 4],
            ['code' => 'CELPE', 'name' => 'Préparation Intensive CELPE-Bras', 'position' => 5],
        ];

        foreach ($levelsData as $ld) {
            $level = Level::firstOrCreate(
                ['code' => $ld['code']],
                [
                    'name'      => $ld['name'],
                    'position'  => $ld['position'],
                    'is_active' => true,
                ]
            );

            // Offre à distance
            $offerRemote = LevelOffer::firstOrCreate(
                [
                    'level_id'   => $level->id,
                    'study_mode' => 'remote_home_country',
                ],
                [
                    'is_available'   => true,
                    'allow_requests' => true,
                    'base_price'     => 350.00,
                    'currency'       => 'EUR',
                    'is_active'      => true,
                ]
            );

            OfferPaymentPlan::firstOrCreate(
                ['offer_id' => $offerRemote->id, 'code' => 'cash'],
                [
                    'label'              => 'Paiement en une fois (comptant)',
                    'installments_count' => 1,
                    'interval_months'    => 1,
                    'total_amount'       => 350.00,
                    'is_active'          => true,
                ]
            );

            // Offre en présentiel au Brésil
            $offerBrazil = LevelOffer::firstOrCreate(
                [
                    'level_id'   => $level->id,
                    'study_mode' => 'in_person_brazil',
                ],
                [
                    'is_available'   => true,
                    'allow_requests' => true,
                    'base_price'     => 500.00,
                    'currency'       => 'EUR',
                    'is_active'      => true,
                ]
            );

            OfferPaymentPlan::firstOrCreate(
                ['offer_id' => $offerBrazil->id, 'code' => 'cash'],
                [
                    'label'              => 'Paiement en une fois (comptant)',
                    'installments_count' => 1,
                    'interval_months'    => 1,
                    'total_amount'       => 500.00,
                    'is_active'          => true,
                ]
            );
        }

        // 2. Villes d'accueil au Brésil
        $citiesData = [
            ['name' => 'Brasília', 'state_code' => 'DF', 'position' => 1],
            ['name' => 'São Paulo', 'state_code' => 'SP', 'position' => 2],
            ['name' => 'Campinas', 'state_code' => 'SP', 'position' => 3],
            ['name' => 'Rio de Janeiro', 'state_code' => 'RJ', 'position' => 4],
            ['name' => 'Belo Horizonte', 'state_code' => 'MG', 'position' => 5],
            ['name' => 'Porto Alegre', 'state_code' => 'RS', 'position' => 6],
            ['name' => 'Salvador', 'state_code' => 'BA', 'position' => 7],
            ['name' => 'Curitiba', 'state_code' => 'PR', 'position' => 8],
        ];

        foreach ($citiesData as $cd) {
            BrazilCity::firstOrCreate(
                ['name' => $cd['name']],
                [
                    'state_code'   => $cd['state_code'],
                    'position'     => $cd['position'],
                    'is_available' => true,
                ]
            );
        }
    }
}

