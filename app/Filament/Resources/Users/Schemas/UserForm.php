<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identité & Profil Personnel')
                    ->description('Nom, prénom et coordonnées de contact de la personne.')
                    ->relationship('profile')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('first_name')
                                ->label('Prénom')
                                ->placeholder('Ex: Moussa')
                                ->required(),

                            TextInput::make('last_name')
                                ->label('Nom de Famille')
                                ->placeholder('Ex: Diallo')
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('phone')
                                ->label('Téléphone / WhatsApp')
                                ->tel()
                                ->placeholder('+229 97 00 00 00'),

                            TextInput::make('country_code')
                                ->label('Code Pays d’Origine (ISO 2)')
                                ->default('BJ')
                                ->placeholder('Ex: BJ, TG, CI, SN, BR')
                                ->maxLength(2),
                        ]),
                    ]),

                Section::make('Dossier d’Inscription & Mobilité au Brésil')
                    ->description('Détails académiques, choix de formation, campus et démarches consulaires.')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('formation_level_id')
                                ->label('Formation / Niveau visé')
                                ->options(fn () => \App\Models\Level::pluck('name', 'id')->toArray())
                                ->placeholder('-- Choisir la formation --')
                                ->searchable(),

                            Select::make('formation_study_mode')
                                ->label('Modalité d’études')
                                ->options([
                                    'in_person_brazil' => '🇧🇷 Présentiel au Brésil (Visa VITEM IV)',
                                    'online' => '💻 En Ligne Direct',
                                ])
                                ->default('in_person_brazil'),

                            TextInput::make('formation_target_university')
                                ->label('Université cible')
                                ->placeholder('Ex: USP São Paulo, UnB Brasília'),
                        ]),

                        Grid::make(3)->schema([
                            Select::make('formation_brazil_city_id')
                                ->label('Ville d’accueil au Brésil')
                                ->options(fn () => \App\Models\BrazilCity::pluck('name', 'id')->toArray())
                                ->placeholder('-- Choisir la ville --')
                                ->searchable(),

                            Select::make('formation_status')
                                ->label('Statut du dossier')
                                ->options([
                                    'pending_payment' => '⏳ En attente de règlement',
                                    'validated' => '✅ Dossier validé',
                                    'active' => '🟢 Inscrit / Actif',
                                    'completed' => '🎓 Formation achevée',
                                    'withdrawn' => '❌ Désistement',
                                ])
                                ->default('pending_payment'),

                            Select::make('formation_rhythm')
                                ->label('Rythme des cours')
                                ->options([
                                    'day' => '☀️ Cours de jour (Matin / Après-midi)',
                                    'evening' => '🌙 Cours du soir',
                                ])
                                ->default('day'),
                        ]),

                        Grid::make(2)->schema([
                            Placeholder::make('visa_status')
                                ->label('Statut Visa VITEM IV')
                                ->content(function (?User $record): string {
                                    $visa = $record?->visaApplications()->latest('id')->first();
                                    if (! $visa) {
                                        return 'Non requis (en ligne) ou non initié';
                                    }
                                    return match ($visa->status) {
                                        'draft' => '📝 Dossier en préparation (Brouillon)',
                                        'submitted' => '📤 Déposé au Consulat',
                                        'in_review' => '🔍 En cours d’instruction consulaire',
                                        'documents_issued' => '📋 Attestations officielles délivrées',
                                        'closed' => '🔒 Clôturé',
                                        default => ucfirst($visa->status),
                                    };
                                }),

                            Placeholder::make('enrolled_at')
                                ->label('Date d’inscription')
                                ->content(fn (?User $record): string => $record?->created_at ? $record->created_at->format('d/m/Y à H:i') : '—'),
                        ]),
                    ])
                    ->visible(fn (?User $record) => $record && ($record->role === 'student' || $record->enrollments()->exists())),

                Section::make('Compte d’Accès & Sécurité')
                    ->description('Identifiants de connexion et permissions sur la plateforme.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->label('Adresse Email')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true),

                            TextInput::make('password')
                                ->label('Mot de passe')
                                ->password()
                                ->dehydrated(fn ($state) => filled($state))
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->helperText('Laissez vide pour conserver le mot de passe actuel en modification.'),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('role')
                                ->label('Rôle sur la plateforme')
                                ->options([
                                    'student' => 'Étudiant (Candidat à la mobilité)',
                                    'teacher' => 'Professeur (Enseignant certifié)',
                                    'admin' => 'Administrateur (Gestion pédagogique)',
                                ])
                                ->default('student')
                                ->required(),

                            Select::make('status')
                                ->label('Statut du compte')
                                ->options([
                                    'active' => 'Actif (Peut se connecter)',
                                    'pending_verification' => 'En attente de confirmation email',
                                    'suspended' => 'Suspendu',
                                ])
                                ->default('active')
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
