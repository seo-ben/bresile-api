<?php

namespace App\Filament\Resources\VisaApplications\Schemas;

use App\Models\Enrollment;
use App\Models\VisaApplication;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisaApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Candidat & Dossier d’Inscription')
                    ->description('Sélectionnez l’étudiant et son inscription d’études au Brésil.')
                    ->schema([
                        Select::make('student_id')
                            ->label('Étudiant / Demandeur de Visa')
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

                        Select::make('enrollment_id')
                            ->label('Dossier d’Inscription Associé')
                            ->options(function () {
                                return Enrollment::with(['student.profile', 'level', 'city'])
                                    ->get()
                                    ->mapWithKeys(function ($e) {
                                        $name = $e->student?->profile ? trim("{$e->student->profile->first_name} {$e->student->profile->last_name}") : ($e->student?->email ?? "Candidat");
                                        $level = $e->level?->name ?? 'Niveau non défini';
                                        $city = $e->city?->name ?? 'Brésil';
                                        return [$e->id => "{$name} — {$level} ({$city})"];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Sélectionner l’inscription rattachée'),
                    ]),

                Section::make('Suivi de la Procédure Consulaire (VITEM IV)')
                    ->description('Mettez à jour le statut consulaire et la décision officielle.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Étape du Dossier')
                                ->options([
                                    'draft' => 'Constitution du dossier (Brouillon)',
                                    'documents_issued' => 'Attestation officielle émise',
                                    'submitted' => 'Déposé au Consulat du Brésil',
                                    'in_review' => 'En cours d’instruction consulaire',
                                    'more_info_needed' => 'Complément d’informations requis',
                                    'closed' => 'Dossier Clôturé',
                                ])
                                ->default('draft')
                                ->required(),

                            Select::make('visa_outcome')
                                ->label('Décision Consulaire Officielle')
                                ->options([
                                    'pending' => 'En attente de décision (En instruction)',
                                    'granted' => 'Visa VITEM IV Accordé',
                                    'refused' => 'Refus Consulaire (Garantie 85% éligible)',
                                ])
                                ->default('pending')
                                ->required(),
                        ]),

                        DateTimePicker::make('submitted_at')
                            ->label('Date de dépôt au consulat')
                            ->placeholder('jj/mm/aaaa --:--'),
                    ]),

                Section::make('Validation & Traçabilité Automatique')
                    ->description('L’agent responsable et l’horodatage de la décision sont enregistrés automatiquement.')
                    ->schema([
                        Placeholder::make('trace_info')
                            ->label('Suivi Consulaire & Validation')
                            ->content(function (?VisaApplication $record): string {
                                if (! $record || $record->visa_outcome === 'pending') {
                                    return 'Dès qu’une décision consulaire est sélectionnée (Accordé ou Refusé), votre identité d’administrateur et l’heure exacte sont automatiquement enregistrées en arrière-plan.';
                                }
                                $reviewerName = $record->reviewer?->profile
                                    ? trim("{$record->reviewer->profile->first_name} {$record->reviewer->profile->last_name}")
                                    : ($record->reviewer?->email ?? 'Système');
                                $date = $record->outcome_reported_at?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i');
                                $decision = $record->visa_outcome === 'granted' ? 'Visa Accordé' : 'Refus Consulaire';
                                return "Décision ({$decision}) enregistrée par {$reviewerName} le {$date}.";
                            }),
                    ]),
            ]);
    }
}
