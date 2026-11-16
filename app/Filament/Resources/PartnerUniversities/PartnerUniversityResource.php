<?php

namespace App\Filament\Resources\PartnerUniversities;

use App\Filament\Resources\PartnerUniversities\Pages\CreatePartnerUniversity;
use App\Filament\Resources\PartnerUniversities\Pages\EditPartnerUniversity;
use App\Filament\Resources\PartnerUniversities\Pages\ListPartnerUniversities;
use App\Filament\Resources\PartnerUniversities\Schemas\PartnerUniversityForm;
use App\Filament\Resources\PartnerUniversities\Tables\PartnerUniversitiesTable;
use App\Models\PartnerUniversity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PartnerUniversityResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = "Configuration & CMS";
    protected static ?int $navigationSort = 3;

    protected static ?string $model = PartnerUniversity::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Universités & Écoles';
    protected static ?string $pluralModelLabel = 'Écoles & Universités Partenaires';
    protected static ?string $modelLabel = 'Université / École';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Schema $schema): Schema
    {
        return PartnerUniversityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerUniversitiesTable::configure($table);
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
            'index' => ListPartnerUniversities::route('/'),
        ];
    }
}

