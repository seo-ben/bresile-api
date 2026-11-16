<?php

namespace App\Filament\Resources\AppSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AppSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('setting_key')
                    ->label('Clé de Configuration')
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),

                TextInput::make('description')
                    ->label('Description du Paramètre')
                    ->default(null),

                Textarea::make('value_json')
                    ->label('Valeur (JSON ou Texte)')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
