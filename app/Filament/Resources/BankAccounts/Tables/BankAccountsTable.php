<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Libellé du Compte')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('bank_name')
                    ->label('Banque')
                    ->icon('heroicon-m-building-library')
                    ->searchable(),

                TextColumn::make('account_holder')
                    ->label('Titulaire du Compte')
                    ->searchable(),

                TextColumn::make('account_number')
                    ->label('IBAN / Numéro de Compte')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('swift_bic')
                    ->label('SWIFT / BIC / Clé Pix')
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('currency')
                    ->label('Devise')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Actif pour réceptions')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading(fn (\App\Models\BankAccount $record) => "Modifier Compte — {$record->label}")
                    ->modalSubmitActionLabel('Enregistrer les Modifications')
                    ->modalCancelActionLabel('Annuler')
                    ->modalWidth('2xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ]);
    }
}
