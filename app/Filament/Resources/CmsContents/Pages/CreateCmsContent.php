<?php

namespace App\Filament\Resources\CmsContents\Pages;

use App\Filament\Resources\CmsContents\CmsContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCmsContent extends CreateRecord
{
    protected static string $resource = CmsContentResource::class;
}
