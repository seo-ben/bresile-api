<?php

namespace App\Filament\Resources\CmsContents\Pages;

use App\Filament\Resources\CmsContents\CmsContentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCmsContents extends ListRecords
{
    protected static string $resource = CmsContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
