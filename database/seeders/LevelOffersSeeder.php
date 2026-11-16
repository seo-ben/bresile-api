<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\LevelOffer;
use App\Models\OfferPaymentPlan;
use Illuminate\Database\Seeder;

class LevelOffersSeeder extends Seeder
{
    public function run(): void
    {
        $levels = Level::all();

        foreach ($levels as $level) {
            // Offre à distance
            $offerRemote = LevelOffer::firstOrCreate(
                [
                    'level_id'   => $level->id,
                    'study_mode' => 'remote_home_country',
                ],
                [
                    'is_available' => true,
                    'allow_requests' => true,
                    'base_price'   => 350.00,
                    'currency'     => 'EUR',
                    'is_active'    => true,
                ]
            );

            OfferPaymentPlan::firstOrCreate(
                [
                    'offer_id' => $offerRemote->id,
                    'code'     => 'cash',
                ],
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
                    'is_available' => true,
                    'allow_requests' => true,
                    'base_price'   => 500.00,
                    'currency'     => 'EUR',
                    'is_active'    => true,
                ]
            );

            OfferPaymentPlan::firstOrCreate(
                [
                    'offer_id' => $offerBrazil->id,
                    'code'     => 'cash',
                ],
                [
                    'label'              => 'Paiement en une fois (comptant)',
                    'installments_count' => 1,
                    'interval_months'    => 1,
                    'total_amount'       => 500.00,
                    'is_active'          => true,
                ]
            );
        }
    }
}
