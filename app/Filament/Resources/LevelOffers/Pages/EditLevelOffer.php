<?php

namespace App\Filament\Resources\LevelOffers\Pages;

use App\Filament\Resources\LevelOffers\LevelOfferResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLevelOffer extends EditRecord
{
    protected static string $resource = LevelOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
