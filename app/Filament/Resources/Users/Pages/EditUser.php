<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        $profile = $this->record->profile;
        if ($profile && ($profile->first_name || $profile->last_name)) {
            return "Compte — " . trim("{$profile->first_name} {$profile->last_name}");
        }
        return "Compte — " . $this->record->email;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Supprimer'),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
