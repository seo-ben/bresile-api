<?php

namespace App\Filament\Resources\VisitorVisas;

use App\Filament\Resources\VisaApplications\Schemas\VisaApplicationForm;
use App\Filament\Resources\VisaApplications\Tables\VisaApplicationsTable;
use App\Filament\Resources\VisitorVisas\Pages\ListVisitorVisas;
use App\Models\VisaApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VisitorVisaResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'Inscriptions & Visas';
    protected static ?int $navigationSort = 3;

    protected static ?string $model = VisaApplication::class;
    protected static ?string $slug = 'visitor-visas';
    protected static ?string $navigationLabel = 'Dossiers Visa Visiteur';
    protected static ?string $pluralModelLabel = 'Dossiers Visa Visiteur';
    protected static ?string $modelLabel = 'Dossier Visa Visiteur';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('enrollment', function (Builder $query) {
            $query->where('visa_type', 'visitor_visa');
        });
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('visa_outcome', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getRecordTitle(?Model $record): ?string
    {
        if (! $record) {
            return 'Dossier Visa Visiteur';
        }

        $profile = $record->student?->profile;
        if ($profile && ($profile->first_name || $profile->last_name)) {
            return trim("{$profile->first_name} {$profile->last_name}");
        }

        return $record->student?->email ?? 'Dossier Visa Visiteur';
    }

    public static function form(Schema $schema): Schema
    {
        return VisaApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisaApplicationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisitorVisas::route('/'),
        ];
    }
}
