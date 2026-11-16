<?php

namespace App\Filament\Resources\ExamResults\Pages;

use App\Filament\Resources\ExamResults\ExamResultResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExamResults extends ListRecords
{
    protected static string $resource = ExamResultResource::class;

    public function getTitle(): string
    {
        return 'Résultats des Examens';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Enregistrer une Note')
                ->icon('heroicon-m-plus')
                ->modalHeading('Saisie d’une Note d’Examen')
                ->modalWidth('2xl')
                ->createAnother(false),
        ];
    }
}
