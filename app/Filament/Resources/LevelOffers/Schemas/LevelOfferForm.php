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
