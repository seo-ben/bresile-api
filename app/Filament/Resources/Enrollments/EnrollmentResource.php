<?php

namespace App\Filament\Resources\Enrollments;

use App\Filament\Resources\Enrollments\Pages\CreateEnrollment;
use App\Filament\Resources\Enrollments\Pages\EditEnrollment;
use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Filament\Resources\Enrollments\Schemas\EnrollmentForm;
use App\Filament\Resources\Enrollments\Tables\EnrollmentsTable;
use App\Models\Enrollment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EnrollmentResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = "Inscriptions & Visas";
    protected static ?int $navigationSort = 1;

    protected static ?string $model = Enrollment::class;
    protected static ?string $navigationLabel = 'Dossiers d’Inscription';
    protected static ?string $pluralModelLabel = 'Dossiers d’Inscription';

    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): ?string
    {
        if (! $record) {
            return 'Dossier d’Inscription';
        }

        $profile = $record->student?->profile;
        if ($profile && ($profile->first_name || $profile->last_name)) {
            return trim("{$profile->first_name} {$profile->last_name}");
        }

        return $record->student?->email ?? 'Dossier d’Inscription';
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Schema $schema): Schema
    {
        return EnrollmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnrollmentsTable::configure($table);
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
            'index' => ListEnrollments::route('/'),
        ];
    }
}
