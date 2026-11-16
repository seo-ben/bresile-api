<?php

namespace App\Filament\Resources\Forms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('purpose')
                    ->options([
            'registration' => 'Registration',
            'payment_confirmation' => 'Payment confirmation',
            'a1_brazil_request' => 'A1 brazil request',
            'visa_application' => 'Visa application',
            'refund_request' => 'Refund request',
            'absence_justification' => 'Absence justification',
            'other' => 'Other',
        ])
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('active_purpose')
                    ->default(null),
            ]);
    }
}
