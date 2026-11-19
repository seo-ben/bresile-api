<?php

namespace App\Filament\Resources\VisitorVisas\Pages;

use App\Filament\Resources\VisitorVisas\VisitorVisaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVisitorVisas extends ListRecords
{
    protected static string $resource = VisitorVisaResource::class;

    public function getTitle(): string
    {
        return 'Dossiers Visa Visiteur (VIVIS)';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau Dossier Visa Visiteur')
                ->icon('heroicon-m-plus')
                ->modalHeading('Nouveau Dossier de Visa Visiteur')
                ->modalDescription('Rattachez un étudiant et son inscription pour le suivi du Visa Visiteur (VIVIS).')
                ->modalSubmitActionLabel('Créer le Dossier Visa')
                ->modalCancelActionLabel('Annuler')
                ->modalWidth('2xl')
                ->createAnother(false),
        ];
    }
}
