<?php

namespace App\Filament\Resources\ExamResults\Schemas;

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamResult;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Saisie de la Note d’Examen')
                    ->description('Rattachez la note à une épreuve et à un étudiant.')
                    ->schema([
                        Select::make('exam_id')
                            ->label('Épreuve / Examen')
                            ->options(function () {
                                return Exam::with('level')->get()->mapWithKeys(function ($e) {
                                    $lvl = $e->level?->name ?? 'Tous niveaux';
                                    return [$e->id => "{$e->title} ({$lvl}) — Barème /{$e->max_score}"];
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('enrollment_id')
                            ->label('Étudiant / Candidat')
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
                            TextInput::make('score')
                                ->label('Note Obtenue')
                                ->numeric()
                                ->placeholder('Ex: 16.5')
                                ->required(fn ($get) => ! $get('is_absent')),

                            Toggle::make('is_absent')
                                ->label('Étudiant Absent à l’épreuve')
                                ->live(),
                        ]),

                        Textarea::make('feedback')
                            ->label('Appréciation / Commentaire pédagogique')
                            ->placeholder('Ex: Excellente maîtrise de la grammaire et de l’expression orale.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Traçabilité Automatique')
                    ->schema([
                        Placeholder::make('trace')
                            ->label('Enseignant Évaluateur')
                            ->content(function (?ExamResult $record): string {
                                if (! $record) {
                                    return 'Votre compte enseignant/correcteur et la date exacte sont enregistrés automatiquement en arrière-plan.';
                                }
                                $author = $record->recordedBy?->profile
                                    ? trim("{$record->recordedBy->profile->first_name} {$record->recordedBy->profile->last_name}")
                                    : ($record->recordedBy?->email ?? 'Système');
                                $date = $record->recorded_at?->format('d/m/Y à H:i') ?? '';
                                return "Note enregistrée par {$author} le {$date}.";
                            }),
                    ]),
            ]);
    }
}
