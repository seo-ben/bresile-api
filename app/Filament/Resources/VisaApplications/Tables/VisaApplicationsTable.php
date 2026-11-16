<?php

namespace App\Filament\Resources\VisaApplications\Tables;

use App\Models\FileModel;
use App\Models\VisaApplication;
use App\Services\VisaAndRefundService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisaApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student')
                    ->label('Étudiant / Demandeur')
                    ->state(function (VisaApplication $record): string {
                        $profile = $record->student?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->student?->email ?? 'Candidat';
                    })
                    ->description(fn (VisaApplication $record): string => $record->student?->email ?? '')
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

                TextColumn::make('enrollment.level.name')
                    ->label('Formation & Destination')
                    ->state(fn (VisaApplication $record): string => $record->enrollment?->level?->name ?? 'Niveau non défini')
                    ->description(fn (VisaApplication $record): string => $record->enrollment?->city?->name ? "Ville : {$record->enrollment->city->name}" : 'Brésil')
                    ->icon('heroicon-m-map-pin')
                    ->iconColor('primary'),

                TextColumn::make('status')
                    ->label('Étape Démarche')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Dossier à constituer',
                        'documents_issued' => 'Attestation émise',
                        'submitted' => 'Déposé au Consulat',
                        'in_review' => 'En cours d’instruction',
                        'more_info_needed' => 'Complément requis',
                        'closed' => 'Dossier Clôturé',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'documents_issued' => 'info',
                        'submitted' => 'warning',
                        'in_review' => 'primary',
                        'closed' => 'gray',
                        default => 'slate',
                    }),

                TextColumn::make('visa_outcome')
                    ->label('Décision Consulaire')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'granted' => 'Visa Accordé',
                        'refused' => 'Refus (Garantie 85%)',
                        'pending' => 'En Instruction',
                        default => 'En attente',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'granted' => 'success',
                        'refused' => 'danger',
                        'pending' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('outcome_reported_at')
                    ->label('Date Décision')
                    ->dateTime('d/m/Y')
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date Ouverture')
                    ->dateTime('d/m/Y')
                    ->color('gray')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordAction(EditAction::class)
            ->recordActions([
                Action::make('issue_document')
                    ->label('Délivrer Attestation')
                    ->icon('heroicon-m-document-check')
                    ->color('primary')
                    ->form([
                        TextInput::make('title')
                            ->label('Intitulé de l’attestation officielle')
                            ->default('Certificat d’Inscription Officiel pour Visa VITEM IV')
                            ->required(),
                    ])
                    ->action(function (VisaApplication $record, array $data) {
                        $file = FileModel::create([
                            'uploaded_by'     => (int) auth()->id(),
                            'storage_disk'    => 'private',
                            'storage_path'    => 'visa_documents/' . $record->id . '_attestation.pdf',
                            'original_name'   => $data['title'] . '.pdf',
                            'mime_type'       => 'application/pdf',
                            'size_bytes'      => 1024,
                        ]);

                        app(VisaAndRefundService::class)->issueOfficialDocument(
                            visaApplicationId: $record->id,
                            title: $data['title'],
                            fileId: $file->id,
                            issuedByAdminId: (int) auth()->id()
                        );

                        Notification::make()
                            ->title('Attestation délivrée avec succès')
                            ->body('Le document consulaire officiel a été généré et rattaché au dossier.')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label('Gérer Dossier')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading(fn (VisaApplication $record) => "Dossier Visa — " . ($record->student?->profile ? trim("{$record->student->profile->first_name} {$record->student->profile->last_name}") : ($record->student?->email ?? 'Demandeur')))
                    ->modalDescription('Suivi de la procédure consulaire VITEM IV et décision officielle.')
                    ->modalSubmitActionLabel('Enregistrer les Modifications')
                    ->modalCancelActionLabel('Annuler')
                    ->modalWidth('2xl'),
            ])
            ->filters([
                SelectFilter::make('visa_outcome')
                    ->label('Filtrer par Décision')
                    ->options([
                        'pending' => 'En Instruction',
                        'granted' => 'Visa Accordé',
                        'refused' => 'Visa Refusé',
                    ]),
                SelectFilter::make('status')
                    ->label('Filtrer par Étape')
                    ->options([
                        'draft' => 'Dossier à constituer',
                        'documents_issued' => 'Attestation émise',
                        'submitted' => 'Déposé au Consulat',
                        'closed' => 'Clôturé',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ]);
    }
}
