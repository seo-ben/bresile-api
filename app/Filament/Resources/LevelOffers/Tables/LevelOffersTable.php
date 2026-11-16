<?php

namespace App\Filament\Resources\LevelOffers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LevelOffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('level.name')
                    ->label('Niveau de Langue')
                    ->weight('bold')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('study_mode')
                    ->label('Modalité d’Études')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_person_brazil' => '🇧🇷 Présentiel au Brésil',
                        'remote_home_country' => '💻 En Ligne Direct',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in_person_brazil' => 'success',
                        'remote_home_country' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('base_price')
                    ->label('Tarif de Scolarité')
                    ->money(fn ($record) => $record->currency ?? 'EUR')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('Devise')
                    ->badge(),
                IconColumn::make('is_available')
                    ->label('Disponible')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Date de création')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Modifier Offre')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Modifier l’Offre de Formation')
                    ->modalSubmitActionLabel('Enregistrer les Modifications')
                    ->modalCancelActionLabel('Annuler')
                    ->modalWidth('2xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
