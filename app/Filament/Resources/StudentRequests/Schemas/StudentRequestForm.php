<?php

namespace App\Filament\Resources\StudentRequests\Schemas;

use App\Models\BrazilCity;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\StudentRequest;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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

                        TextInput::make('subject')
                            ->label('Objet de la Demande')
                            ->placeholder('Ex: Demande de changement de ville vers São Paulo'),

                        Textarea::make('message')
                            ->label('Message détaillé de l’Étudiant')
                            ->rows(4)
                            ->columnSpanFull(),

                        Placeholder::make('student_attachment_link')
                            ->label('Pièce Jointe envoyée par l’Étudiant')
                            ->visible(fn (?StudentRequest $record): bool => (bool) $record?->attachment_path)
                            ->content(function (?StudentRequest $record): HtmlString {
                                $url = asset('storage/' . ltrim((string) $record?->attachment_path, '/'));
                                $name = e($record?->attachment_name ?: basename((string) $record?->attachment_path));
                                return new HtmlString('<a href="' . e($url) . '" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-weight:700;text-decoration:none;">📎 Télécharger / Voir : ' . $name . '</a>');
                            }),
                    ]),

                Section::make('Détails de Mobilité & Villes')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('requested_study_mode')
                                ->label('Modalité demandée')
                                ->options([
                                    'in_person_brazil'    => 'Présentiel Brésil (VITEM IV)',
                                    'visitor_visa_brazil' => 'Présentiel Brésil (Visa Visiteur)',
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
                            ->label('Réponse / Retour de l’Administration à l’Étudiant')
                            ->placeholder('Ex: Accordé suite à confirmation des places disponibles à Brasília.')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('admin_attachment_path')
                            ->label('Document de réponse à joindre pour l’Étudiant (Optionnel — PDF, JPG, PNG)')
                            ->disk('public')
                            ->directory('student_requests_replies')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(15360)
                            ->helperText('Ce fichier apparaîtra directement sous votre réponse dans l’espace « Mes Demandes » de l’étudiant.'),
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
