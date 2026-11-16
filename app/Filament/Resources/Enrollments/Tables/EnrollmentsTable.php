<?php

namespace App\Filament\Resources\Enrollments\Tables;

use App\Filament\Resources\Enrollments\Schemas\EnrollmentForm;
use App\Models\Enrollment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Wizard;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student')
                    ->label('Étudiant / Candidat')
                    ->state(function (Enrollment $record): string {
                        $profile = $record->student?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->student?->email ?? 'Candidat';
                    })
                    ->description(fn (Enrollment $record): string => $record->student?->email ?? '')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%")
                              ->orWhereHas('profile', function ($qp) use ($search) {
                                  $qp->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%");
                              });
                        });
                    })
                    ->weight('bold')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('student_id', $direction);
                    }),

                TextColumn::make('level.name')
                    ->label('Formation & Modalité')
                    ->state(fn (Enrollment $record): string => $record->level?->name ?? 'Niveau non défini')
                    ->description(function (Enrollment $record): string {
                        return match ($record->offer?->study_mode) {
                            'in_person_brazil' => 'Présentiel Brésil (Visa VITEM IV)',
                            'remote_home_country' => 'En Ligne (Pays d’origine)',
                            default => 'Formation standard',
                        };
                    })
                    ->weight('medium'),

                TextColumn::make('city.name')
                    ->label('Affectation')
                    ->state(fn (Enrollment $record): string => $record->city?->name ? "{$record->city->name} ({$record->city->state_code})" : ($record->offer?->study_mode === 'remote_home_country' ? 'En ligne' : 'Non assignée'))
                    ->description(fn (Enrollment $record): string => $record->classGroup?->name ? "Classe : {$record->classGroup->name}" : ($record->country_residence ? "Résidence : {$record->country_residence}" : 'Sans classe assignée'))
                    ->icon('heroicon-m-map-pin')
                    ->iconColor(fn (Enrollment $record): string => $record->city ? 'primary' : 'gray'),

                TextColumn::make('target_university')
                    ->label('Univ. Cible')
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'active' => 'Inscrit / Actif',
                        'pending_payment' => 'Attente Paiement',
                        'completed' => 'Terminé',
                        'validated' => 'Dossier Validé',
                        'not_validated' => 'Non Validé',
                        'excluded_unpaid' => 'Exclu (Impayé)',
                        'failed_absence' => 'Échec Assiduité',
                        'withdrawn' => 'Désistement',
                        default => ucfirst($state ?? 'Inconnu'),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'active', 'validated' => 'success',
                        'pending_payment' => 'warning',
                        'completed' => 'info',
                        'excluded_unpaid', 'failed_absence', 'withdrawn' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('visaApplication.visa_outcome')
                    ->label('Visa VITEM IV')
                    ->badge()
                    ->formatStateUsing(function (?string $state, Enrollment $record): string {
                        if ($record->offer?->study_mode !== 'in_person_brazil') {
                            return 'Non requis';
                        }
                        return match ($state) {
                            'granted' => 'Accordé',
                            'pending' => 'En Instruction',
                            'refused' => 'Refusé (85%)',
                            default => 'À constituer',
                        };
                    })
                    ->color(function (?string $state, Enrollment $record): string {
                        if ($record->offer?->study_mode !== 'in_person_brazil') {
                            return 'gray';
                        }
                        return match ($state) {
                            'granted' => 'success',
                            'pending' => 'warning',
                            'refused' => 'danger',
                            default => 'slate',
                        };
                    }),

                TextColumn::make('enrolled_at')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->color('gray')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Dossier d’Inscription Étudiant')
                    ->modalDescription('Consultez et modifiez les informations de l’inscription.')
                    ->modalWidth('4xl')
                    ->modalSubmitActionLabel('Enregistrer les Modifications')
                    ->steps(EnrollmentForm::getSteps())
                    ->skippableSteps()
                    ->modifyWizardUsing(fn (Wizard $wizard) => $wizard
                        ->nextAction(fn ($action) => $action->label('Suivant')->icon('heroicon-m-arrow-right'))
                        ->previousAction(fn ($action) => $action->label('Précédent')->icon('heroicon-m-arrow-left'))
                    ),
            ])
            ->filters([
                SelectFilter::make('level_id')
                    ->label('Filtrer par Niveau')
                    ->relationship('level', 'name'),

                SelectFilter::make('brazil_city_id')
                    ->label('Filtrer par Ville')
                    ->relationship('city', 'name'),

                SelectFilter::make('status')
                    ->label('Filtrer par Statut')
                    ->options([
                        'active' => 'Actif',
                        'pending_payment' => 'Attente Paiement',
                        'validated' => 'Validé',
                        'completed' => 'Terminé',
                        'withdrawn' => 'Désistement',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ]);
    }
}
