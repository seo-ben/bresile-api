<?php

namespace App\Filament\Resources\StudentRequests\Schemas;

use App\Models\BrazilCity;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\StudentRequest;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Candidat & Objet de la Demande')
                    ->schema([
                        Select::make('student_id')
                            ->label('Étudiant / Demandeur')
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
                            Select::make('type')
                                ->label('Type de Requête')
                                ->options([
                                    'a1_brazil' => 'Inscription Anticipée A1 Présentiel Brésil',
                                    'offer_access' => 'Demande d’accès à une offre spéciale',
                                    'city_change' => 'Changement de Ville d’Accueil',
                                    'other' => 'Autre demande administrative',
                                ])
                                ->required(),

                            Select::make('enrollment_id')
                                ->label('Dossier d’Inscription Associé')
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
                                ->placeholder('Sélectionner le dossier'),
                        ]),
                    ]),

                Section::make('Détails de Mobilité & Villes')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('requested_study_mode')
                                ->label('Modalité demandée')
                                ->options([
                                    'in_person_brazil' => 'Présentiel Brésil (VITEM IV)',
                                    'remote_home_country' => 'En Ligne (Distance)',
                                ])
                                ->placeholder('Non applicable'),

                            Select::make('from_city_id')
                                ->label('Ville d’origine')
                                ->options(BrazilCity::pluck('name', 'id'))
                                ->placeholder('Ville actuelle'),

                            Select::make('to_city_id')
                                ->label('Nouvelle Ville souhaitée')
                                ->options(BrazilCity::pluck('name', 'id'))
                                ->placeholder('Nouvelle ville'),
                        ]),
                    ]),

                Section::make('Instruction & Décision Pédagogique')
                    ->schema([
                        Select::make('status')
                            ->label('Statut de la Requête')
                            ->options([
                                'open' => 'En cours d’examen',
                                'awaiting_student' => 'Attente retour étudiant',
                                'approved' => 'Demande Approuvée',
                                'rejected' => 'Demande Rejetée',
                                'cancelled' => 'Annulée',
                            ])
                            ->default('open')
                            ->required(),

                        Textarea::make('decision_note')
                            ->label('Commentaire & Motif de la Décision')
                            ->placeholder('Ex: Accordé suite à confirmation des places disponibles à Brasília.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Validation & Traçabilité Automatique')
                    ->description('L’agent responsable et l’horodatage de la décision sont enregistrés automatiquement.')
                    ->schema([
                        Placeholder::make('trace_info')
                            ->label('Instruction Pédagogique')
                            ->content(function (?StudentRequest $record): string {
                                if (! $record || ! in_array($record->status, ['approved', 'rejected', 'cancelled'])) {
                                    return 'Dès qu’une décision finale est prise (Approuvée ou Rejetée), votre identité d’administrateur et l’heure exacte sont enregistrées automatiquement en arrière-plan.';
                                }
                                $adminName = $record->resolvedBy?->profile
                                    ? trim("{$record->resolvedBy->profile->first_name} {$record->resolvedBy->profile->last_name}")
                                    : ($record->resolvedBy?->email ?? 'Système');
                                $date = $record->resolved_at?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i');
                                return "Demande traitée par {$adminName} le {$date}.";
                            }),
                    ]),
            ]);
    }
}
