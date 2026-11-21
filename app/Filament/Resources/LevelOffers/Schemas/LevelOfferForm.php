<?php

namespace App\Filament\Resources\LevelOffers\Schemas;

use App\Models\Level;
use App\Services\CurrencyService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LevelOfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Offre de Formation & Tarification')
                    ->description('Définition du niveau de langue, de la modalité et du tarif applicable.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('level_id')
                                ->label('Niveau de Langue (CECRL)')
                                ->relationship('level', 'name')
                                ->required(),

                            Select::make('study_mode')
                                ->label('Modalité d’Études')
                                ->options([
                                    'in_person_brazil'    => 'Présentiel Brésil (Visa VITEM IV)',
                                    'visitor_visa_brazil' => 'Présentiel Brésil (Visa Visiteur)',
                                    'remote_home_country' => 'En Ligne (Pays d’origine)',
                                ])
                                ->required(),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('service_fee')
                                ->label('Frais de Service (Plateforme & Démarches)')
                                ->numeric()
                                ->default(150)
                                ->live(onBlur: true)
                                ->helperText(fn ($get) => CurrencyService::getConversionHelper((float) $get('service_fee'), $get('currency')))
                                ->required(),

                            TextInput::make('base_price')
                                ->label('Frais de Scolarité (Tarif Comptant)')
                                ->numeric()
                                ->live(onBlur: true)
                                ->helperText(fn ($get) => CurrencyService::getConversionHelper((float) $get('base_price'), $get('currency')))
                                ->required(),

                            Select::make('currency')
                                ->label('Devise de Facturation')
                                ->options([
                                    'EUR' => 'EUR (€) — Euro',
                                    'XOF' => 'XOF (FCFA) — Franc CFA',
                                    'BRL' => 'BRL (R$) — Real Brésilien',
                                ])
                                ->default('EUR')
                                ->live()
                                ->required(),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('installment_1_amount')
                                ->label('Montant Tranche 1 (Mois 1)')
                                ->numeric()
                                ->placeholder('Ex: 200')
                                ->live(onBlur: true)
                                ->helperText(fn ($get) => $get('installment_1_amount') ? CurrencyService::getConversionHelper((float) $get('installment_1_amount'), $get('currency')) : 'Défini par l’administrateur')
                                ->required(),

                            TextInput::make('installment_2_amount')
                                ->label('Montant Tranche 2 (Mois 2)')
                                ->numeric()
                                ->placeholder('Ex: 150')
                                ->live(onBlur: true)
                                ->helperText(fn ($get) => $get('installment_2_amount') ? CurrencyService::getConversionHelper((float) $get('installment_2_amount'), $get('currency')) : 'Défini par l’administrateur')
                                ->required(),

                            TextInput::make('installment_3_amount')
                                ->label('Montant Tranche 3 (Mois 3)')
                                ->numeric()
                                ->placeholder('Ex: 150')
                                ->live(onBlur: true)
                                ->helperText(fn ($get) => $get('installment_3_amount') ? CurrencyService::getConversionHelper((float) $get('installment_3_amount'), $get('currency')) : 'Défini par l’administrateur')
                                ->required(),
                        ]),

                        Section::make('Créneaux Horaires & Tarification Modulée (Cours du Soir)')
                            ->description('Activez les cours du soir (en ligne ou présentiel) avec un tarif et des tranches modulés selon l’horaire.')
                            ->schema([
                                Grid::make(3)->schema([
                                    Toggle::make('evening_enabled')
                                        ->label('Proposer les Cours du soir (tarif modulé)')
                                        ->default(false)
                                        ->live(),

                                    TextInput::make('day_schedule_label')
                                        ->label('Horaire — Cours du jour')
                                        ->placeholder('Ex: 09h00 – 13h00')
                                        ->maxLength(120),

                                    TextInput::make('evening_schedule_label')
                                        ->label('Horaire — Cours du soir')
                                        ->placeholder('Ex: 18h30 – 21h30')
                                        ->maxLength(120),
                                ]),

                                Grid::make(4)->schema([
                                    TextInput::make('evening_base_price')
                                        ->label('Scolarité Cours du soir (Comptant)')
                                        ->numeric()
                                        ->placeholder('Ex: 410')
                                        ->live(onBlur: true)
                                        ->helperText(fn ($get) => $get('evening_base_price') ? CurrencyService::getConversionHelper((float) $get('evening_base_price'), $get('currency')) : 'Tarif modulé pour le créneau du soir')
                                        ->visible(fn ($get) => (bool) $get('evening_enabled')),

                                    TextInput::make('evening_installment_1_amount')
                                        ->label('Soir — Tranche 1')
                                        ->numeric()
                                        ->placeholder('Ex: 150')
                                        ->live(onBlur: true)
                                        ->helperText(fn ($get) => $get('evening_installment_1_amount') ? CurrencyService::getConversionHelper((float) $get('evening_installment_1_amount'), $get('currency')) : '')
                                        ->visible(fn ($get) => (bool) $get('evening_enabled')),

                                    TextInput::make('evening_installment_2_amount')
                                        ->label('Soir — Tranche 2')
                                        ->numeric()
                                        ->placeholder('Ex: 130')
                                        ->live(onBlur: true)
                                        ->helperText(fn ($get) => $get('evening_installment_2_amount') ? CurrencyService::getConversionHelper((float) $get('evening_installment_2_amount'), $get('currency')) : '')
                                        ->visible(fn ($get) => (bool) $get('evening_enabled')),

                                    TextInput::make('evening_installment_3_amount')
                                        ->label('Soir — Tranche 3')
                                        ->numeric()
                                        ->placeholder('Ex: 130')
                                        ->live(onBlur: true)
                                        ->helperText(fn ($get) => $get('evening_installment_3_amount') ? CurrencyService::getConversionHelper((float) $get('evening_installment_3_amount'), $get('currency')) : '')
                                        ->visible(fn ($get) => (bool) $get('evening_enabled')),
                                ]),
                            ]),

                        Grid::make(2)->schema([
                            Select::make('prerequisite_level_id')
                                ->label('Niveau Prérequis Requis')
                                ->options(Level::pluck('name', 'id'))
                                ->placeholder('Aucun prérequis'),

                            Select::make('prerequisite_study_mode')
                                ->label('Modalité Prérequise')
                                ->options([
                                    'in_person_brazil' => 'Présentiel Brésil',
                                    'remote_home_country' => 'En Ligne',
                                ])
                                ->placeholder('Aucune modalité imposée'),
                        ]),

                        Textarea::make('conditions_text')
                            ->label('Conditions d’Accès & Notes Pédagogiques')
                            ->placeholder('Ex: Nécessite validation du test de niveau initial.')
                            ->columnSpanFull(),

                        Grid::make(3)->schema([
                            Toggle::make('is_available')
                                ->label('Offre ouverte aux inscriptions')
                                ->default(true)
                                ->required(),

                            Toggle::make('allow_requests')
                                ->label('Autoriser les dérogations spéciales')
                                ->default(true)
                                ->required(),

                            Toggle::make('is_active')
                                ->label('Offre active dans le catalogue')
                                ->default(true)
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
