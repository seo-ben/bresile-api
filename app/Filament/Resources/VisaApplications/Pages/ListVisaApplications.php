<?php

namespace App\Filament\Resources\VisaApplications\Pages;

use App\Filament\Resources\VisaApplications\VisaApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVisaApplications extends ListRecords
{
    protected static string $resource = VisaApplicationResource::class;

    public function getTitle(): string
    {
        return 'Dossiers Visa Consulaire VITEM IV';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau Dossier Visa')
                ->icon('heroicon-m-plus')
                ->modalHeading('Nouveau Dossier de Visa Consulaire')
                ->modalDescription('Rattachez un étudiant et son inscription pour le suivi du visa étudiant VITEM IV.')
                ->modalSubmitActionLabel('Créer le Dossier Visa')
                ->modalCancelActionLabel('Annuler')
                ->modalWidth('2xl')
                ->createAnother(false),
        ];
    }
}
