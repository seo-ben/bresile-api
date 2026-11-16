<?php

namespace App\Filament\Resources\LevelOffers;

use App\Filament\Resources\LevelOffers\Pages\CreateLevelOffer;
use App\Filament\Resources\LevelOffers\Pages\EditLevelOffer;
use App\Filament\Resources\LevelOffers\Pages\ListLevelOffers;
use App\Filament\Resources\LevelOffers\Schemas\LevelOfferForm;
use App\Filament\Resources\LevelOffers\Tables\LevelOffersTable;
use App\Models\LevelOffer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LevelOfferResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = "Finances & Trésorerie";
    protected static ?int $navigationSort = 1;

    protected static ?string $model = LevelOffer::class;

    protected static ?string $navigationLabel = "Tarifs & Scolarités";
    protected static ?string $pluralModelLabel = "Tarifs & Scolarités";
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Schema $schema): Schema
    {
        return LevelOfferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LevelOffersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentPlansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLevelOffers::route('/'),
            'create' => CreateLevelOffer::route('/create'),
            'edit' => EditLevelOffer::route('/{record}/edit'),
        ];
    }
}
