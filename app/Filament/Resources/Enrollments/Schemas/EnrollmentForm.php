<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use App\Models\BrazilCity;
use App\Models\LevelOffer;
use App\Models\OfferPaymentPlan;
use App\Services\CurrencyService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class EnrollmentForm
{
    /**
     * @return array<Step>
     */
    public static function getSteps(): array
    {
        return [
            Step::make('Identité & Statut')
                ->description('Étudiant et date d’inscription')
                ->icon('heroicon-m-user')
                ->schema([
                    Grid::make(2)->schema([
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

                        Select::make('status')
                            ->label('Statut de l’inscription')
                            ->options([
                                'active' => 'Inscrit / Actif',
                                'pending_payment' => 'Attente de Paiement',
                                'validated' => 'Dossier Validé',
                                'completed' => 'Formation Terminée',
                                'not_validated' => 'Non Validé',
                                'excluded_unpaid' => 'Exclu (Impayé)',
                                'failed_absence' => 'Échec Assiduité',
                                'withdrawn' => 'Désistement',
                            ])
                            ->default('pending_payment')
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        DateTimePicker::make('enrolled_at')
                            ->label('Date d’inscription officielle')
                            ->required()
                            ->default(now()),

                        TextInput::make('status_reason')
                            ->label('Remarque / Motif du statut')
                            ->placeholder('Ex: Dossier consulaire en cours, 1er acompte reçu...'),
                    ]),
                ]),

            Step::make('Programme & Mobilité')
                ->description('Niveau, formule et ville d’accueil')
                ->icon('heroicon-m-academic-cap')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('level_id')
                            ->label('Niveau de Langue (CECRL)')
                            ->relationship('level', 'name')
                            ->required(),

                        Select::make('offer_id')
                            ->label('Formule d’Études & Tarification')
                            ->options(function () {
                                return LevelOffer::with('level')->get()->mapWithKeys(function ($o) {
                                    $code = $o->level?->code ?? '';
                                    $mode = match ($o->study_mode) {
                                        'in_person_brazil'    => 'Présentiel Brésil (Visa VITEM IV)',
                                        'visitor_visa_brazil' => 'Présentiel Brésil (Visa Visiteur)',
                                        default               => 'En ligne (Pays d’origine)',
                                    };
                                    $dualPrice = CurrencyService::formatDual((float) $o->base_price, $o->currency ?? 'EUR');
                                    return [$o->id => "Niveau {$code} — {$mode} ({$dualPrice})"];
                                });
                            })
                            ->required(),
                    ]),

                    Grid::make(3)->schema([
                        Select::make('brazil_city_id')
                            ->label('Ville d’Accueil au Brésil')
                            ->options(BrazilCity::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Sélectionner une ville (ex: Brasília)'),

                        Select::make('class_group_id')
                            ->label('Classe / Promotion')
                            ->relationship('classGroup', 'name')
                            ->placeholder('Assigner plus tard'),

                        Select::make('rhythm')
                            ->label('Rythme d’apprentissage')
                            ->options([
                                'day' => 'Cours de Jour (Intensif)',
                                'evening' => 'Cours du Soir (Régulier)',
                            ]),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('target_university')
                            ->label('Université Partenaire Ciblée')
                            ->placeholder('Ex: Universidade de Brasília (UnB)'),

                        TextInput::make('country_residence')
                            ->label('Pays de Résidence Actuel')
                            ->placeholder('Ex: Sénégal, Côte d\'Ivoire...'),
                    ]),

                    Grid::make(3)->schema([
                        Select::make('visa_type')
                            ->label('Type de Visa')
                            ->options([
                                'vitem_iv'     => 'Visa VITEM IV (Étudiant)',
                                'visitor_visa' => 'Visa Visiteur (VIVIS)',
                            ])
                            ->placeholder('Aucun (En ligne)'),

                        Select::make('study_purpose')
                            ->label('Objet d’Étude')
                            ->options([
                                'etudes_superieures' => 'Études Supérieures / Université',
                                'business_affaires'  => 'Business & Affaires',
                                'travail'            => 'Travail / Carrière Professionnelle',
                                'conversation'       => 'Conversation & Culture',
                                'celpe_bras'         => 'Examen Officiel CELPE-Bras',
                                'tourisme_immersion' => 'Voyage & Immersion',
                                'autre'              => 'Autre projet',
                            ])
                            ->searchable()
                            ->placeholder('Sélectionner l’objet'),

                        TextInput::make('previous_level_certificate_path')
                            ->label('Justificatif Niveau Précédent (Fichier)')
                            ->placeholder('Aucun fichier téléversé')
                            ->readOnly(),
                    ]),

                    Grid::make(2)->schema([
                        DateTimePicker::make('started_at')
                            ->label('Date de début des cours'),

                        DateTimePicker::make('ended_at')
                            ->label('Date de fin de session'),
                    ]),
                ]),

            Step::make('Paiement & Échéances')
                ->description('Plan de règlement échelonné')
                ->icon('heroicon-m-credit-card')
                ->schema([
                    Select::make('payment_plan_id')
                        ->label('Plan de règlement échelonné')
                        ->options(function () {
                            return OfferPaymentPlan::all()->mapWithKeys(function ($p) {
                                $dualAmount = CurrencyService::formatDual((float) $p->total_amount, 'EUR');
                                return [$p->id => "{$p->label} — {$dualAmount} ({$p->installments_count} échéance(s))"];
                            });
                        })
                        ->placeholder('Sélectionner un échéancier'),
                ]),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getSteps());
    }
}
