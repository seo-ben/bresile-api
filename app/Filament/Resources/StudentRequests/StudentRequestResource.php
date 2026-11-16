<?php

namespace App\Filament\Resources\StudentRequests;

use App\Filament\Resources\StudentRequests\Pages\CreateStudentRequest;
use App\Filament\Resources\StudentRequests\Pages\EditStudentRequest;
use App\Filament\Resources\StudentRequests\Pages\ListStudentRequests;
use App\Filament\Resources\StudentRequests\Schemas\StudentRequestForm;
use App\Filament\Resources\StudentRequests\Tables\StudentRequestsTable;
use App\Models\StudentRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentRequestResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = "Inscriptions & Visas";
    protected static ?int $navigationSort = 4;

    protected static ?string $model = StudentRequest::class;

    protected static ?string $navigationLabel = "Requêtes Étudiantes";
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function form(Schema $schema): Schema
    {
        return StudentRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentRequests::route('/'),
        ];
    }
}
