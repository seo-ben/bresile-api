<?php

namespace App\Filament\Resources\Levels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Niveau CECRL')
                    ->badge()
                    ->color('emerald')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Intitulé de la Formation')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('position')
                    ->label('Ordre dans le cursus')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Ouvert aux inscriptions')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->defaultSort('position', 'asc')
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Gérer Niveau & Tarifs')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading(fn (\App\Models\Level $record) => "Modifier Niveau — {$record->name}")
                    ->modalSubmitActionLabel('Enregistrer les Modifications')
                    ->modalCancelActionLabel('Annuler')
                    ->modalWidth('lg'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ]);
    }
}
