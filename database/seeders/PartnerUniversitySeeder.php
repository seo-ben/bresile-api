<?php

namespace Database\Seeders;

use App\Models\PartnerUniversity;
use Illuminate\Database\Seeder;

class PartnerUniversitySeeder extends Seeder
{
    public function run(): void
    {
        $universities = [
            [
                'sigle' => 'UnB',
                'name' => 'Universidade de Brasília',
                'full_name' => 'Université Fédérale de Brasília',
                'city' => 'Brasília',
                'state' => 'Distrito Federal',
                'rank_badge' => 'Top 5 Fédéral',
                'highlight' => 'Campus Oscar Niemeyer • Admission sur dossier CELPE-Bras',
                'badge_gradient' => 'linear-gradient(135deg, #091e42 0%, #1877f2 100%)',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'sigle' => 'USP',
                'name' => 'Universidade de São Paulo',
                'full_name' => 'Université d’État de São Paulo',
                'city' => 'São Paulo',
                'state' => 'São Paulo (SP)',
                'rank_badge' => 'N°1 Amérique Latine',
                'highlight' => 'Leader mondial de recherche et d’innovation académique',
                'badge_gradient' => 'linear-gradient(135deg, #064e3b 0%, #00a859 100%)',
                'position' => 2,
                'is_active' => true,
            ],
            [
                'sigle' => 'UNICAMP',
                'name' => 'Univ. Estadual de Campinas',
                'full_name' => 'Université d’État de Campinas',
                'city' => 'Campinas',
                'state' => 'São Paulo (SP)',
                'rank_badge' => 'Top 3 Recherche',
                'highlight' => '15% de l’ensemble des brevets tech et recherche du Brésil',
                'badge_gradient' => 'linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%)',
                'position' => 3,
                'is_active' => true,
            ],
            [
                'sigle' => 'UFRJ',
                'name' => 'Univ. Federal do Rio de Janeiro',
                'full_name' => 'Université Fédérale de Rio de Janeiro',
                'city' => 'Rio de Janeiro',
                'state' => 'Rio de Janeiro (RJ)',
                'rank_badge' => 'Pôle d’Excellence',
                'highlight' => 'Plus ancienne et illustre institution fédérale du Brésil',
                'badge_gradient' => 'linear-gradient(135deg, #0f172a 0%, #0284c7 100%)',
                'position' => 4,
                'is_active' => true,
            ],
            [
                'sigle' => 'UFMG',
                'name' => 'Univ. Federal de Minas Gerais',
                'full_name' => 'Université Fédérale de Minas Gerais',
                'city' => 'Belo Horizonte',
                'state' => 'Minas Gerais (MG)',
                'rank_badge' => 'Top 5 National',
                'highlight' => 'Campus d’excellence de 8,7 millions de m² à Pampulha',
                'badge_gradient' => 'linear-gradient(135deg, #78350f 0%, #d97706 100%)',
                'position' => 5,
                'is_active' => true,
            ],
            [
                'sigle' => 'UFRGS',
                'name' => 'Univ. Federal do Rio Grande do Sul',
                'full_name' => 'Université Fédérale du Rio Grande do Sul',
                'city' => 'Porto Alegre',
                'state' => 'Rio Grande do Sul (RS)',
                'rank_badge' => 'Pôle Technologique',
                'highlight' => 'Centre universitaire de pointe du Sud brésilien',
                'badge_gradient' => 'linear-gradient(135deg, #1e1b4b 0%, #4338ca 100%)',
                'position' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($universities as $uni) {
            PartnerUniversity::firstOrCreate(
                ['sigle' => $uni['sigle']],
                $uni
            );
        }
    }
}

