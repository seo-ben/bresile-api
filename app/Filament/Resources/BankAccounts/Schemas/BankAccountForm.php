<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Libellé du compte')
                    ->placeholder('Ex: Compte Virement EUR / BCEAO FCFA')
                    ->required(),
                TextInput::make('bank_name')
                    ->label('Nom de la banque')
                    ->placeholder('Ex: Ecobank / Société Générale / Banco do Brasil')
                    ->required(),
                TextInput::make('account_holder')
                    ->label('Titulaire du compte')
                    ->default('Brasil·É Escola de Idiomas')
                    ->required(),
                TextInput::make('account_number')
                    ->label('IBAN / Numéro de compte')
                    ->required(),
                TextInput::make('swift_bic')
                    ->label('Code SWIFT / BIC')
                    ->default(null),
                TextInput::make('country_code')
                    ->label('Code Pays (ISO)')
                    ->default('BJ'),
                Select::make('currency')
                    ->label('Devise du Compte')
                    ->options([
                        'EUR' => 'EUR (€) — Euro',
                        'XOF' => 'XOF (FCFA) — Franc CFA',
                        'BRL' => 'BRL (R$) — Real Brésilien',
                    ])
                    ->default('EUR')
                    ->required(),
                Textarea::make('instructions')
                    ->label('Instructions de virement à destination de l’étudiant')
                    ->placeholder('Ex: Préciser impérativement le nom de l’étudiant en motif de virement.')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Compte actif pour les règlements')
                    ->default(true)
                    ->required(),
            ]);
    }
}
