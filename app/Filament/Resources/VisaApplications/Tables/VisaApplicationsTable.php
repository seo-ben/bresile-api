<?php

namespace App\Filament\Resources\VisaApplications\Tables;

use App\Filament\Resources\OfficialDocuments\OfficialDocumentResource;
use App\Models\FileModel;
use App\Models\VisaApplication;
use App\Services\VisaAndRefundService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

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

                TextColumn::make('documents_count')
                    ->label('Documents Envoyés')
                    ->counts('documents')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ((int) $state) > 0 ? "{$state} envoyé(s)" : 'Aucun')
                    ->color(fn ($state): string => ((int) $state) > 0 ? 'success' : 'gray'),

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
                    ->label('Envoyer Document')
                    ->icon('heroicon-m-document-arrow-up')
                    ->color('primary')
                    ->modalHeading('Envoyer un Document Officiel à l’Étudiant')
                    ->modalSubmitActionLabel('Envoyer à l’étudiant')
                    ->modalWidth('2xl')
                    ->form([
                        Placeholder::make('sent_documents_history')
                            ->label('Historique des documents déjà envoyés à cet étudiant')
                            ->content(function (VisaApplication $record): HtmlString {
                                $docs = $record->documents()->with('file')->orderByDesc('issued_at')->orderByDesc('id')->get();
                                if ($docs->isEmpty()) {
                                    return new HtmlString('<div style="padding:10px 12px;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;font-size:13px;color:#64748b;">Aucun document officiel n’a encore été envoyé pour ce dossier.</div>');
                                }

                                $rows = '';
                                foreach ($docs as $doc) {
                                    $title = e($doc->title);
                                    $date = $doc->issued_at ? $doc->issued_at->format('d/m/Y H:i') : '—';
                                    $url = $doc->file?->storage_path ? asset('storage/' . ltrim($doc->file->storage_path, '/')) : null;
                                    $linkHtml = $url
                                        ? '<a href="' . e($url) . '" target="_blank" rel="noopener" style="color:#059669;font-weight:700;text-decoration:underline;white-space:nowrap;">Ouvrir / Télécharger</a>'
                                        : '<span style="color:#94a3b8;">Fichier indisponible</span>';

                                    $rows .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:8px 10px;border-radius:8px;background:#ffffff;border:1px solid #e2e8f0;">'
                                        . '<div><div style="font-weight:700;color:#0f172a;font-size:13px;">' . $title . '</div>'
                                        . '<div style="font-size:11px;color:#64748b;">Envoyé le ' . $date . '</div></div>'
                                        . $linkHtml
                                        . '</div>';
                                }

                                return new HtmlString('<div style="display:flex;flex-direction:column;gap:6px;padding:8px;border-radius:10px;background:#f1f5f9;border:1px solid #cbd5e1;max-height:190px;overflow-y:auto;">' . $rows . '</div>');
                            }),
                        Select::make('document_type')
                            ->label('Type de document officiel')
                            ->options([
                                'attestation' => 'Attestation d’Inscription Officielle',
                                'contrat'     => 'Contrat & Garantie 85% de Remboursement',
                                'fiche'       => 'Fiche de Candidature Officielle',
                                'guide'       => 'Guide d’Installation & Visa au Brésil',
                                'autre'       => 'Autre document officiel personnalisé',
                            ])
                            ->default('attestation')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $defaults = [
                                    'attestation' => 'Attestation d’Inscription Officielle',
                                    'contrat'     => 'Contrat & Garantie 85% de Remboursement',
                                    'fiche'       => 'Fiche de Candidature Officielle',
                                    'guide'       => 'Guide d’Installation & Visa au Brésil',
                                ];
                                if (isset($defaults[$state])) {
                                    $set('title', $defaults[$state]);
                                }
                            }),
                        TextInput::make('title')
                            ->label('Intitulé du document officiel')
                            ->default('Attestation d’Inscription Officielle')
                            ->required(),
                        FileUpload::make('uploaded_file_path')
                            ->label('Fichier Officiel (PDF, JPG, PNG — max 15 Mo)')
                            ->disk('public')
                            ->directory('official_documents')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(15360)
                            ->required(),
                    ])
                    ->action(function (VisaApplication $record, array $data) {
                        OfficialDocumentResource::createDocumentFromUpload(
                            visaApplicationId: $record->id,
                            title: (string) $data['title'],
                            storagePath: (string) $data['uploaded_file_path']
                        );

                        Notification::make()
                            ->title('Document envoyé avec succès')
                            ->body('Le document officiel est désormais téléchargeable dans le portail de l’étudiant.')
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
