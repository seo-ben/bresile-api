<?php

namespace App\Filament\Resources\BrazilCities\Pages;

use App\Filament\Resources\BrazilCities\BrazilCityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBrazilCity extends EditRecord
{
    protected static string $resource = BrazilCityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
