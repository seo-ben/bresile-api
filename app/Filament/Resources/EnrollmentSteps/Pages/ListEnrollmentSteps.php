<?php

namespace App\Filament\Resources\EnrollmentSteps\Pages;

use App\Filament\Resources\EnrollmentSteps\EnrollmentStepResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnrollmentSteps extends ListRecords
{
    protected static string $resource = EnrollmentStepResource::class;

    public function getTitle(): string
    {
        return 'Schéma Architectural des Étapes d’Inscription';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une Étape')
                ->icon('heroicon-m-plus')
                ->modalHeading('Nouvelle Étape du Schéma')
                ->modalSubmitActionLabel('Ajouter au schéma')
                ->modalWidth('3xl')
                ->createAnother(false),
        ];
    }
}
