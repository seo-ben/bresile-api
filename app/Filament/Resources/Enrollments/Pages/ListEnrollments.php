<?php

namespace App\Filament\Resources\Enrollments\Pages;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Filament\Resources\Enrollments\Schemas\EnrollmentForm;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Wizard;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    public function getTitle(): string
    {
        return 'Dossiers d’Inscription Étudiants';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle Inscription')
                ->icon('heroicon-m-plus')
                ->modalHeading('Nouvelle Inscription Étudiante')
                ->modalDescription('Complétez les étapes pas-à-pas pour enregistrer le dossier.')
                ->modalWidth('4xl')
                ->modalSubmitActionLabel('Créer l’Inscription')
                ->steps(EnrollmentForm::getSteps())
                ->skippableSteps()
                ->modifyWizardUsing(fn (Wizard $wizard) => $wizard
                    ->nextAction(fn ($action) => $action->label('Suivant')->icon('heroicon-m-arrow-right'))
                    ->previousAction(fn ($action) => $action->label('Précédent')->icon('heroicon-m-arrow-left'))
                )
                ->createAnother(false),
        ];
    }
}
