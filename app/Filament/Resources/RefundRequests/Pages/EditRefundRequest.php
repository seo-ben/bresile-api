<?php

namespace App\Filament\Resources\RefundRequests\Pages;

use App\Filament\Resources\RefundRequests\RefundRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRefundRequest extends EditRecord
{
    protected static string $resource = RefundRequestResource::class;

    public function getTitle(): string
    {
        $profile = $this->record->student?->profile;
        $name = ($profile && ($profile->first_name || $profile->last_name))
            ? trim("{$profile->first_name} {$profile->last_name}")
            : ($this->record->student?->email ?? 'Étudiant');

        return "Dossier de Remboursement 85% — {$name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Supprimer'),
        ];
    }
}
