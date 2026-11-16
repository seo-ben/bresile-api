<?php

namespace App\Filament\Resources\Exams\Pages;

use App\Filament\Resources\Exams\ExamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    public function getTitle(): string
    {
        return 'Examens & Épreuves';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel Examen')
                ->icon('heroicon-m-plus')
                ->modalHeading('Créer une Nouvelle Épreuve d’Examen')
                ->modalWidth('2xl')
                ->createAnother(false),
        ];
    }
}
