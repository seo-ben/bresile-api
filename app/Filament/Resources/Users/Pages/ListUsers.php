<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Étudiants & Utilisateurs';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Créer un Utilisateur')
                ->icon('heroicon-m-plus')
                ->modalHeading('Créer un Compte Utilisateur / Étudiant')
                ->modalDescription('Enregistrez les coordonnées et les identifiants de connexion.')
                ->modalSubmitActionLabel('Créer le Compte')
                ->modalCancelActionLabel('Annuler')
                ->modalWidth('2xl')
                ->createAnother(false),
        ];
    }
}
