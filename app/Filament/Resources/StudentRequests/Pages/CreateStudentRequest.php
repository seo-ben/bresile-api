<?php

namespace App\Filament\Resources\StudentRequests\Pages;

use App\Filament\Resources\StudentRequests\StudentRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentRequest extends CreateRecord
{
    protected static string $resource = StudentRequestResource::class;
}
