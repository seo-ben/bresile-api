<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = "Inscriptions & Visas";
    protected static ?int $navigationSort = 3;

    protected static ?string $model = User::class;
    protected static ?string $navigationLabel = 'Étudiants & Utilisateurs';
    protected static ?string $pluralModelLabel = 'Étudiants & Utilisateurs';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function getRecordTitle(?Model $record): ?string
    {
        if (! $record) {
            return 'Compte Utilisateur';
        }

        $profile = $record->profile;
        if ($profile && ($profile->first_name || $profile->last_name)) {
            return trim("{$profile->first_name} {$profile->last_name}");
        }

        return $record->email ?? 'Utilisateur';
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
