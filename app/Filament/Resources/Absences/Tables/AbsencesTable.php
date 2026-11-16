<?php

namespace App\Filament\Resources\Absences\Tables;

use App\Models\Absence;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AbsencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enrollment.student')
                    ->label('Étudiant / Candidat')
                    ->state(function (Absence $record): string {
                        $profile = $record->enrollment?->student?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->enrollment?->student?->email ?? 'Étudiant';
                    })
                    ->description(fn (Absence $record): string => $record->enrollment?->student?->email ?? '')
                    ->weight('bold'),

                TextColumn::make('enrollment.level.name')
                    ->label('Niveau')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('absence_date')
                    ->label('Date d’Absence')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('minutes_absent')
                    ->label('Volume Manqué')
                    ->formatStateUsing(fn ($state) => "{$state} min (" . round($state / 60, 1) . "h)")
                    ->sortable(),

                IconColumn::make('is_justified')
                    ->label('Justifiée')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('justification_note')
                    ->label('Motif')
                    ->placeholder('Aucun motif renseigné')
                    ->limit(35),
            ])
            ->defaultSort('absence_date', 'desc')
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Modifier l’Absence')
                    ->modalWidth('2xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
