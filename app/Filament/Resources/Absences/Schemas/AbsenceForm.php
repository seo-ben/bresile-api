<?php

namespace App\Filament\Resources\Absences\Schemas;

use App\Models\Absence;
use App\Models\Enrollment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AbsenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Enregistrement de l’Absence')
                    ->description('Sélectionnez l’étudiant et précisez la durée d’absence au cours.')
                    ->schema([
                        Select::make('enrollment_id')
                            ->label('Étudiant / Inscription')
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
                            ->required(),

                        Grid::make(2)->schema([
                            DatePicker::make('absence_date')
                                ->label('Date de la Séance Manquée')
                                ->default(now())
                                ->required(),

                            TextInput::make('minutes_absent')
                                ->label('Durée d’Absence (Minutes)')
                                ->numeric()
                                ->default(120)
                                ->helperText('Ex: 120 minutes = 2 heures de cours')
                                ->required(),
                        ]),

                        Toggle::make('is_justified')
                            ->label('Absence justifiée (Justificatif médical ou motif recevable)'),

                        TextInput::make('justification_note')
                            ->label('Motif / Commentaire de justification')
                            ->placeholder('Ex: Certificat médical transmis par l’étudiant'),
                    ]),

                Section::make('Traçabilité Automatique')
                    ->schema([
                        Placeholder::make('trace')
                            ->label('Agent Enregistreur')
                            ->content(function (?Absence $record): string {
                                if (! $record) {
                                    return 'Votre compte enseignant/administrateur est enregistré automatiquement en arrière-plan.';
                                }
                                $author = $record->recordedBy?->profile
                                    ? trim("{$record->recordedBy->profile->first_name} {$record->recordedBy->profile->last_name}")
                                    : ($record->recordedBy?->email ?? 'Système');
                                return "Absence saisie par {$author}.";
                            }),
                    ]),
            ]);
    }
}
