<?php

namespace App\Filament\Resources\ExamResults\Tables;

use App\Models\ExamResult;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enrollment.student')
                    ->label('Étudiant / Candidat')
                    ->state(function (ExamResult $record): string {
                        $profile = $record->enrollment?->student?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->enrollment?->student?->email ?? 'Étudiant';
                    })
                    ->description(fn (ExamResult $record): string => $record->enrollment?->student?->email ?? '')
                    ->weight('bold'),

                TextColumn::make('exam.title')
                    ->label('Épreuve / Examen')
                    ->description(fn (ExamResult $record): string => $record->exam?->level?->name ?? '')
                    ->searchable(),

                TextColumn::make('score')
                    ->label('Note')
                    ->formatStateUsing(function ($state, ExamResult $record): string {
                        if ($record->is_absent) {
                            return 'Absent';
                        }
                        $max = $record->exam?->max_score ?? 20;
                        return "{$state} / {$max}";
                    })
                    ->badge()
                    ->color(fn (ExamResult $record): string => $record->is_absent ? 'danger' : ($record->score >= 10 ? 'success' : 'warning'))
                    ->alignEnd(),

                TextColumn::make('recorded_at')
                    ->label('Date d’Évaluation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Modifier Note')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Modifier la Note d’Examen')
                    ->modalWidth('2xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
