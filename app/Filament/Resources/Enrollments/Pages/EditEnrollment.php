<?php

namespace App\Filament\Resources\Enrollments\Pages;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    public function getTitle(): string
    {
        $profile = $this->record->student?->profile;
        $studentName = ($profile && ($profile->first_name || $profile->last_name))
            ? trim("{$profile->first_name} {$profile->last_name}")
            : ($this->record->student?->email ?? 'Étudiant');

        $levelName = $this->record->level?->name ?? '';

        return "Dossier d’Inscription — {$studentName}" . ($levelName ? " ({$levelName})" : '');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Supprimer Dossier'),
        ];
    }
}
