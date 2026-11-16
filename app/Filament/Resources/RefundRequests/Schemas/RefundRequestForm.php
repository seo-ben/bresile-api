<?php

namespace App\Filament\Resources\RefundRequests\Schemas;

use App\Models\Enrollment;
use App\Models\RefundRequest;
use App\Models\VisaApplication;
use App\Services\CurrencyService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefundRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Bénéficiaire & Dossier Rattaché')
                    ->description('Sélectionnez l’étudiant et son dossier consulaire justifiant le remboursement.')
                    ->schema([
                        Select::make('student_id')
                            ->label('Étudiant / Candidat')
                            ->relationship('student', 'email')
                            ->getOptionLabelFromRecordUsing(function ($record): string {
                                $profile = $record->profile;
                                if ($profile && ($profile->first_name || $profile->last_name)) {
                                    return trim("{$profile->first_name} {$profile->last_name}") . " ({$record->email})";
                                }
                                return $record->email;
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Grid::make(2)->schema([
                            Select::make('visa_application_id')
                                ->label('Dossier Visa Consulaire Associé')
                                ->options(function () {
                                    return VisaApplication::with(['student.profile', 'enrollment.level'])
                                        ->get()
                                        ->mapWithKeys(function ($v) {
                                            $name = $v->student?->profile ? trim("{$v->student->profile->first_name} {$v->student->profile->last_name}") : ($v->student?->email ?? "Candidat");
                                            $outcome = match ($v->visa_outcome) {
                                                'refused' => 'Refus Consulaire (Garantie 85%)',
                                                'granted' => 'Visa Accordé',
                                                default => 'En Instruction',
                                            };
                                            return [$v->id => "{$name} — {$outcome}"];
                                        });
                                    })
                                ->searchable()
                                ->preload()
                                ->placeholder('Sélectionner le dossier visa'),

                            Select::make('enrollment_id')
                                ->label('Inscription Scolaire Associée')
                                ->options(function () {
                                    return Enrollment::with(['student.profile', 'level', 'city'])
                                        ->get()
                                        ->mapWithKeys(function ($e) {
                                            $name = $e->student?->profile ? trim("{$e->student->profile->first_name} {$e->student->profile->last_name}") : ($e->student?->email ?? "Candidat");
                                            $level = $e->level?->name ?? 'Niveau non défini';
                                            return [$e->id => "{$name} — {$level}"];
                                        });
                                })
                                ->searchable()
                                ->preload()
                                ->placeholder('Sélectionner l’inscription'),
                        ]),
                    ]),

                Section::make('Calcul & Modalités Financières')
                    ->description('Application de la garantie contractuelle de remboursement (85% en cas de refus consulaire).')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('reason')
                                ->label('Motif de la Demande')
                                ->options([
                                    'visa_refusal' => 'Refus de visa étudiant (Garantie 85%)',
                                    'academic_failure' => 'Échec académique / Désistement',
                                    'other' => 'Autre motif exceptionnel',
                                ])
                                ->default('visa_refusal')
                                ->required(),

                            Select::make('status')
                                ->label('Statut du Remboursement')
                                ->options([
                                    'submitted' => 'Demande Soumise (En attente)',
                                    'in_review' => 'En cours d’instruction',
                                    'approved' => 'Approuvé (À virer)',
                                    'paid' => 'Remboursement Effectué',
                                    'rejected' => 'Demande Rejetée',
                                    'draft' => 'Brouillon',
                                ])
                                ->default('submitted')
                                ->required(),

                            Select::make('currency')
                                ->label('Devise')
                                ->options([
                                    'EUR' => 'EUR (€) — Euro',
                                    'XOF' => 'XOF (FCFA) — Franc CFA',
                                ])
                                ->default(fn () => CurrencyService::getActiveCurrency())
                                ->live()
                                ->required(),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('total_paid_amount')
                                ->label('Total Encaissé par l’École')
                                ->numeric()
                                ->required(),

                            TextInput::make('refund_percent')
                                ->label('Taux de Remboursement (%)')
                                ->numeric()
                                ->default(85)
                                ->required(),

                            TextInput::make('refund_amount')
                                ->label('Montant à Restituer')
                                ->numeric()
                                ->live(onBlur: true)
                                ->helperText(fn ($get) => CurrencyService::getConversionHelper((float) $get('refund_amount'), $get('currency')))
                                ->required(),
                        ]),

                        TextInput::make('payout_reference')
                            ->label('Référence du Virement de Retour')
                            ->placeholder('Ex: VIR-RETOUR-2026-881'),

                        Textarea::make('notes')
                            ->label('Remarques & Justificatifs consulaires vérifiés')
                            ->placeholder('Ex: Lettre de refus consulaire vérifiée, coordonnées bancaires de l’étudiant transmises.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Validation & Traçabilité Automatique')
                    ->description('L’agent instructeur et les dates de validation/paiement sont enregistrés automatiquement.')
                    ->schema([
                        Placeholder::make('trace_info')
                            ->label('Suivi Comptable & Validation')
                            ->content(function (?RefundRequest $record): string {
                                if (! $record || ! in_array($record->status, ['approved', 'paid', 'rejected'])) {
                                    return 'Dès l’approbation ou la validation du paiement, votre identité d’administrateur et l’heure exacte sont automatiquement enregistrées en arrière-plan.';
                                }
                                $reviewerName = $record->reviewer?->profile
                                    ? trim("{$record->reviewer->profile->first_name} {$record->reviewer->profile->last_name}")
                                    : ($record->reviewer?->email ?? 'Système');
                                $revDate = $record->reviewed_at?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i');
                                $paidInfo = $record->paid_at ? " — Paiement effectif le {$record->paid_at->format('d/m/Y à H:i')}" : '';
                                return "Dossier traité par {$reviewerName} le {$revDate}{$paidInfo}.";
                            }),
                    ]),
            ]);
    }
}
