<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Utilisateur')
                    ->state(function (User $record): string {
                        $profile = $record->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->email;
                    })
                    ->description(fn (User $record): string => $record->email)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('email', 'like', "%{$search}%")
                            ->orWhereHas('profile', function ($qp) use ($search) {
                                $qp->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%");
                            });
                    })
                    ->weight('bold'),

                TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrateur',
                        'teacher' => 'Professeur',
                        'student' => 'Étudiant',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'teacher' => 'info',
                        'student' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('profile.phone')
                    ->label('Téléphone')
                    ->icon('heroicon-m-phone')
                    ->default('—'),

                TextColumn::make('status')
                    ->label('Statut Compte')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'active' => 'Actif',
                        'pending' => 'En attente',
                        'suspended' => 'Suspendu',
                        default => ucfirst($state ?? 'Actif'),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('last_login_at')
                    ->label('Dernière Connexion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Jamais connecté')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction(ViewAction::class)
            ->filters([
                SelectFilter::make('role')
                    ->label('Filtrer par Rôle')
                    ->options([
                        'student' => 'Étudiants',
                        'teacher' => 'Professeurs',
                        'admin' => 'Administrateurs',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Détails')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->modalHeading(fn (User $record) => "Détail de l’Inscription — " . ($record->profile ? trim("{$record->profile->first_name} {$record->profile->last_name}") : $record->email))
                    ->modalDescription('Fiche récapitulative complète de l’étudiant et de son dossier de mobilité au Brésil.')
                    ->modalWidth('4xl')
                    ->mutateRecordDataUsing(function (array $data, User $record): array {
                        $enrollment = $record->enrollments()->latest('id')->first();
                        $data['formation_level_id'] = $enrollment?->level_id;
                        $data['formation_study_mode'] = $enrollment?->offer?->study_mode ?? 'in_person_brazil';
                        $data['formation_target_university'] = $enrollment?->target_university;
                        $data['formation_brazil_city_id'] = $enrollment?->brazil_city_id;
                        $data['formation_status'] = $enrollment?->status ?? 'pending_payment';
                        $data['formation_rhythm'] = $enrollment?->rhythm ?? 'day';
                        return $data;
                    }),

                EditAction::make()
                    ->label('Modifier')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading(fn (User $record) => "Modifier le Profil — " . ($record->profile ? trim("{$record->profile->first_name} {$record->profile->last_name}") : $record->email))
                    ->modalDescription('Mettez à jour les informations personnelles, la formation visée et les accès.')
                    ->modalSubmitActionLabel('Enregistrer les Modifications')
                    ->modalCancelActionLabel('Annuler')
                    ->modalWidth('4xl')
                    ->mutateRecordDataUsing(function (array $data, User $record): array {
                        $enrollment = $record->enrollments()->latest('id')->first();
                        $data['formation_level_id'] = $enrollment?->level_id;
                        $data['formation_study_mode'] = $enrollment?->offer?->study_mode ?? 'in_person_brazil';
                        $data['formation_target_university'] = $enrollment?->target_university;
                        $data['formation_brazil_city_id'] = $enrollment?->brazil_city_id;
                        $data['formation_status'] = $enrollment?->status ?? 'pending_payment';
                        $data['formation_rhythm'] = $enrollment?->rhythm ?? 'day';
                        return $data;
                    })
                    ->using(function (User $record, array $data): void {
                        $userData = \Illuminate\Support\Arr::only($data, ['email', 'password', 'role', 'status']);
                        if (empty($userData['password'])) {
                            unset($userData['password']);
                        }
                        $record->update($userData);

                        if (isset($data['profile'])) {
                            $record->profile()->updateOrCreate(
                                ['user_id' => $record->id],
                                $data['profile']
                            );
                        }

                        $levelId = $data['formation_level_id'] ?? null;
                        $studyMode = $data['formation_study_mode'] ?? 'in_person_brazil';
                        $targetUni = $data['formation_target_university'] ?? null;
                        $cityId = $data['formation_brazil_city_id'] ?? null;
                        $status = $data['formation_status'] ?? 'pending_payment';
                        $rhythm = $data['formation_rhythm'] ?? 'day';

                        if ($levelId) {
                            $level = \App\Models\Level::find($levelId);
                            $offer = \App\Models\LevelOffer::where('level_id', $levelId)
                                ->where('study_mode', $studyMode)
                                ->first() ?? \App\Models\LevelOffer::where('level_id', $levelId)->first();
                            $plan = $offer ? \App\Models\OfferPaymentPlan::where('offer_id', $offer->id)->first() : null;

                            $enrollment = $record->enrollments()->latest('id')->first();
                            if ($enrollment) {
                                $enrollment->update([
                                    'level_id' => $levelId,
                                    'offer_id' => $offer?->id ?? $enrollment->offer_id,
                                    'payment_plan_id' => $plan?->id ?? $enrollment->payment_plan_id,
                                    'brazil_city_id' => $cityId,
                                    'target_university' => $targetUni,
                                    'status' => $status,
                                    'rhythm' => $rhythm,
                                ]);
                            } else {
                                $enrollment = \App\Models\Enrollment::create([
                                    'student_id' => $record->id,
                                    'level_id' => $levelId,
                                    'offer_id' => $offer?->id,
                                    'payment_plan_id' => $plan?->id,
                                    'brazil_city_id' => $cityId,
                                    'target_university' => $targetUni,
                                    'country_residence' => $record->profile?->country_code,
                                    'rhythm' => $rhythm,
                                    'status' => $status,
                                    'status_reason' => 'Dossier créé/attribué par l’administrateur',
                                    'enrolled_at' => now(),
                                ]);
                            }

                            if ($studyMode === 'in_person_brazil') {
                                \App\Models\VisaApplication::firstOrCreate(
                                    ['enrollment_id' => $enrollment->id],
                                    [
                                        'student_id' => $record->id,
                                        'status' => 'draft',
                                        'visa_outcome' => 'pending',
                                    ]
                                );
                            }
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
