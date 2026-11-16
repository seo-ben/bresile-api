<?php

namespace App\Filament\Resources\ClassGroups;

use App\Filament\Resources\ClassGroups\Pages\ListClassGroups;
use App\Filament\Resources\ClassGroups\Schemas\ClassGroupForm;
use App\Filament\Resources\ClassGroups\Tables\ClassGroupsTable;
use App\Models\ClassGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClassGroupResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = "Pédagogie & Classes";
    protected static ?int $navigationSort = 3;

    protected static ?string $model = ClassGroup::class;

    protected static ?string $navigationLabel = "Classes & Groupes";
    protected static ?string $pluralModelLabel = "Classes & Groupes";
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    public static function getRecordTitle(?Model $record): ?string
    {
        return $record?->name ?? 'Classe / Promotion';
    }

    public static function form(Schema $schema): Schema
    {
        return ClassGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassGroupsTable::configure($table);
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
            'index' => ListClassGroups::route('/'),
        ];
    }
}
