<?php

namespace App\Filament\Resources\PartnerUniversities\Pages;

use App\Filament\Resources\PartnerUniversities\PartnerUniversityResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartnerUniversity extends CreateRecord
{
    protected static string $resource = PartnerUniversityResource::class;

    public function getTitle(): string
    {
        return 'Ajouter une Université Partenaire';
    }
}

