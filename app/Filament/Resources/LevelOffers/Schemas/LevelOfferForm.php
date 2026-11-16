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
                                    'in_person_brazil' => 'Présentiel Brésil (Visa VITEM IV)',
                                    'remote_home_country' => 'En Ligne (Pays d’origine)',
                                ])
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('base_price')
                                ->label('Tarif de Base')
                                ->numeric()
                                ->live(onBlur: true)
                                ->helperText(fn ($get) => CurrencyService::getConversionHelper((float) $get('base_price'), $get('currency')))
                                ->required(),

                            Select::make('currency')
                                ->label('Devise de Facturation')
                                ->options([
                                    'EUR' => 'EUR (€) — Euro',
                                    'XOF' => 'XOF (FCFA) — Franc CFA',
                                ])
                                ->default('EUR')
                                ->live()
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
