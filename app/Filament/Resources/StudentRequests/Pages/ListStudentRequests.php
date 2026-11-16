<?php

namespace App\Filament\Resources\StudentRequests\Pages;

use App\Filament\Resources\StudentRequests\StudentRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentRequests extends ListRecords
{
    protected static string $resource = StudentRequestResource::class;

    public function getTitle(): string
    {
        return 'Requêtes & Demandes Étudiantes';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle Requête')
                ->icon('heroicon-m-plus')
                ->modalHeading('Créer une Requête Étudiante')
                ->modalDescription('Saisissez une demande particulière au nom d’un étudiant.')
                ->modalWidth('3xl')
                ->createAnother(false),
        ];
    }
}
