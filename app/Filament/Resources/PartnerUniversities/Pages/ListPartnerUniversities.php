<?php

namespace App\Filament\Resources\PartnerUniversities\Pages;

use App\Filament\Resources\PartnerUniversities\PartnerUniversityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartnerUniversities extends ListRecords
{
    protected static string $resource = PartnerUniversityResource::class;

    public function getTitle(): string
    {
        return 'Écoles & Universités Partenaires';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une Université / École')
                ->icon('heroicon-m-plus'),
        ];
    }
}

