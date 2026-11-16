<?php

namespace App\Filament\Resources\LevelOffers\Pages;

use App\Filament\Resources\LevelOffers\LevelOfferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLevelOffers extends ListRecords
{
    protected static string $resource = LevelOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
