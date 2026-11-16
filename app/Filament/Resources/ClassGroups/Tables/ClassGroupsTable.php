<?php

namespace App\Filament\Resources\ClassGroups\Tables;

use App\Models\ClassGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClassGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Promotion / Classe')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('level.name')
                    ->label('Niveau CECRL')
                    ->badge()
                    ->color('emerald')
                    ->sortable(),

                TextColumn::make('study_mode')
                    ->label('Modalité')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'in_person_brazil' => 'Présentiel Brésil',
                        'remote_home_country' => 'En Ligne',
                        default => ucfirst($state ?? ''),
                    })
                    ->color('sky'),

                TextColumn::make('city.name')
                    ->label('Ville Brésil')
                    ->default('En ligne')
                    ->icon('heroicon-m-map-pin'),

                TextColumn::make('teacher')
                    ->label('Professeur')
                    ->state(function (ClassGroup $record): string {
                        $profile = $record->teacher?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->teacher?->email ?? 'Non assigné';
                    })
                    ->default('Non assigné'),

                TextColumn::make('rhythm')
                    ->label('Rythme')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'day' => 'Jour (Intensif)',
                        'evening' => 'Soir (Régulier)',
                        default => $state ?? '',
                    })
                    ->color('gray'),

                TextColumn::make('starts_on')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('ends_on')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->defaultSort('starts_on', 'desc')
            ->recordAction(EditAction::class)
            ->filters([
                SelectFilter::make('level_id')
                    ->label('Filtrer par Niveau')
                    ->relationship('level', 'name'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Gérer Promotion')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading(fn (\App\Models\ClassGroup $record) => "Modifier Promotion — {$record->name}")
                    ->modalDescription('Mettez à jour les paramètres de la classe et son calendrier.')
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
