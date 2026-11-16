<?php

namespace App\Filament\Resources\Exams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Épreuve / Examen')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('level.name')
                    ->label('Niveau CECRL')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('classGroup.name')
                    ->label('Promotion / Groupe')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Toutes promotions')
                    ->sortable(),

                TextColumn::make('exam_date')
                    ->label('Date d’Examen')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('max_score')
                    ->label('Barème')
                    ->formatStateUsing(fn ($state) => "/ {$state}")
                    ->alignEnd(),

                TextColumn::make('weight')
                    ->label('Coeff.')
                    ->alignEnd(),
            ])
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Modifier l’Épreuve')
                    ->modalWidth('2xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
