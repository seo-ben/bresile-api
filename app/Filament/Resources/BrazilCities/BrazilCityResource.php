<?php

namespace App\Filament\Resources\BrazilCities;

use App\Filament\Resources\BrazilCities\Pages\CreateBrazilCity;
use App\Filament\Resources\BrazilCities\Pages\EditBrazilCity;
use App\Filament\Resources\BrazilCities\Pages\ListBrazilCities;
use App\Filament\Resources\BrazilCities\Schemas\BrazilCityForm;
use App\Filament\Resources\BrazilCities\Tables\BrazilCitiesTable;
use App\Models\BrazilCity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BrazilCityResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = "Configuration & CMS";
    protected static ?int $navigationSort = 2;

    protected static ?string $model = BrazilCity::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Villes au Brésil';
    protected static ?string $pluralModelLabel = 'Villes d’Accueil au Brésil';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Schema $schema): Schema
    {
        return BrazilCityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BrazilCitiesTable::configure($table);
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
            'index' => ListBrazilCities::route('/'),
        ];
    }
}
