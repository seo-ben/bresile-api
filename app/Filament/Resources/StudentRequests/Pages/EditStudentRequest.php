<?php

namespace App\Filament\Resources\StudentRequests\Pages;

use App\Filament\Resources\StudentRequests\StudentRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentRequest extends EditRecord
{
    protected static string $resource = StudentRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
