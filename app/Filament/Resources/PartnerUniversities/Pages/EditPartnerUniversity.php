<?php

namespace App\Filament\Resources\PartnerUniversities\Pages;

use App\Filament\Resources\PartnerUniversities\PartnerUniversityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerUniversity extends EditRecord
{
    protected static string $resource = PartnerUniversityResource::class;

    public function getTitle(): string
    {
        return 'Modifier l\'Université Partenaire';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Supprimer'),
        ];
    }
}

