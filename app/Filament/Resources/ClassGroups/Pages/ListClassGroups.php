<?php

namespace App\Filament\Resources\ClassGroups\Pages;

use App\Filament\Resources\ClassGroups\ClassGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClassGroups extends ListRecords
{
    protected static string $resource = ClassGroupResource::class;

    public function getTitle(): string
    {
        return 'Classes & Promotions Étudiantes';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle Promotion')
                ->icon('heroicon-m-plus')
                ->modalHeading('Créer une Nouvelle Promotion')
                ->modalDescription('Définissez une classe avec son niveau, sa ville d’accueil et son enseignant.')
                ->modalSubmitActionLabel('Créer la Promotion')
                ->modalCancelActionLabel('Annuler')
                ->modalWidth('2xl')
                ->createAnother(false),
        ];
    }
}
