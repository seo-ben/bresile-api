<?php

namespace App\Filament\Resources\VisaApplications\Pages;

use App\Filament\Resources\VisaApplications\VisaApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVisaApplication extends EditRecord
{
    protected static string $resource = VisaApplicationResource::class;

    public function getTitle(): string
    {
        $profile = $this->record->student?->profile;
        $name = ($profile && ($profile->first_name || $profile->last_name))
            ? trim("{$profile->first_name} {$profile->last_name}")
            : ($this->record->student?->email ?? 'Étudiant');

        return "Procédure Consulaire (Visa) — {$name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Supprimer Dossier'),
        ];
    }
}
