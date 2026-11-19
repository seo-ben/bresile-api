<?php

namespace App\Filament\Resources\StudentRequests\Tables;

use App\Models\StudentRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student')
                    ->label('Étudiant / Demandeur')
                    ->state(function (StudentRequest $record): string {
                        $profile = $record->student?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->student?->email ?? 'Candidat';
                    })
                    ->description(fn (StudentRequest $record): string => $record->student?->email ?? '')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%")
                              ->orWhereHas('profile', function ($qp) use ($search) {
                                  $qp->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%");
                              });
                        });
                    })
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Nature de la Requête')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'a1_brazil' => 'Inscription A1 Brésil',
                        'city_change' => 'Changement de Ville',
                        'offer_access' => 'Offre Spéciale',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'a1_brazil' => 'primary',
                        'city_change' => 'sky',
                        'offer_access' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('subject')
                    ->label('Objet & Message')
                    ->default('—')
                    ->limit(40)
                    ->description(fn (StudentRequest $record): string => $record->message ? \Illuminate\Support\Str::limit($record->message, 65) : '')
                    ->searchable(),

                TextColumn::make('attachment_path')
                    ->label('Document Joint')
                    ->formatStateUsing(fn (?string $state, StudentRequest $record): string => $state ? ($record->attachment_name ?: 'Voir pièce jointe') : '—')
                    ->url(fn (StudentRequest $record): ?string => $record->attachment_path ? asset('storage/' . ltrim($record->attachment_path, '/')) : null, shouldOpenInNewTab: true)
                    ->icon(fn (?string $state): ?string => $state ? 'heroicon-m-paper-clip' : null)
                    ->color(fn (?string $state): string => $state ? 'primary' : 'gray'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'En Cours d’Examen',
                        'approved' => 'Accordée',
                        'rejected' => 'Refusée',
                        'awaiting_student' => 'Attente Étudiant',
                        'cancelled' => 'Annulée',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Date de Dépôt')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Traiter Demande')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Traitement de la Requête Étudiante')
                    ->modalDescription('Consultez et répondez à la demande de l’étudiant.')
                    ->modalSubmitActionLabel('Enregistrer la Décision')
                    ->modalCancelActionLabel('Annuler')
                    ->modalWidth('3xl'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'open' => 'En Cours',
                        'approved' => 'Accordée',
                        'rejected' => 'Refusée',
                    ]),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'a1_brazil' => 'A1 Brésil',
                        'city_change' => 'Changement Ville',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ]);
    }
}
