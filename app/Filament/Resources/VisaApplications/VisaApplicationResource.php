<?php

namespace App\Filament\Resources\VisaApplications;

use App\Filament\Resources\VisaApplications\Pages\CreateVisaApplication;
use App\Filament\Resources\VisaApplications\Pages\EditVisaApplication;
use App\Filament\Resources\VisaApplications\Pages\ListVisaApplications;
use App\Filament\Resources\VisaApplications\Schemas\VisaApplicationForm;
use App\Filament\Resources\VisaApplications\Tables\VisaApplicationsTable;
use App\Models\VisaApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VisaApplicationResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = "Inscriptions & Visas";
    protected static ?int $navigationSort = 2;

    protected static ?string $model = VisaApplication::class;
    protected static ?string $navigationLabel = 'Dossiers Visa VITEM IV';
    protected static ?string $pluralModelLabel = 'Dossiers Visa VITEM IV';

    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): ?string
    {
        if (! $record) {
            return 'Dossier Visa Consulaire';
        }

        $profile = $record->student?->profile;
        if ($profile && ($profile->first_name || $profile->last_name)) {
            return trim("{$profile->first_name} {$profile->last_name}");
        }

        return $record->student?->email ?? 'Dossier Visa';
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-americas';

    public static function form(Schema $schema): Schema
    {
        return VisaApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisaApplicationsTable::configure($table);
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
            'index' => ListVisaApplications::route('/'),
        ];
    }
}
