<?php

namespace App\Filament\Resources\Absences\Pages;

use App\Filament\Resources\Absences\AbsenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAbsences extends ListRecords
{
    protected static string $resource = AbsenceResource::class;

    public function getTitle(): string
    {
        return 'Suivi des Absences';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Signaler une Absence')
                ->icon('heroicon-m-plus')
                ->modalHeading('Enregistrer une Absence Étudiante')
                ->modalWidth('2xl')
                ->createAnother(false),
        ];
    }
}
