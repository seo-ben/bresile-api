<?php

namespace App\Filament\Resources\ClassGroups\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClassGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identification & Programme de la Classe')
                    ->description('Définissez le niveau d’études, la modalité et la ville d’accueil de cette promotion.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Intitulé de la Promotion / Classe')
                            ->placeholder('Ex: Promo Brasília — A1 Intensif Mars 2026')
                            ->required(),

                        Grid::make(2)->schema([
                            Select::make('level_id')
                                ->label('Niveau de Langue (CECRL)')
                                ->relationship('level', 'name')
                                ->preload()
                                ->required(),

                            Select::make('study_mode')
                                ->label('Modalité d’Enseignement')
                                ->options([
                                    'in_person_brazil'    => 'Présentiel Brésil (Visa VITEM IV)',
                                    'visitor_visa_brazil' => 'Présentiel Brésil (Visa Visiteur)',
                                    'remote_home_country' => 'En Ligne (Pays d’origine)',
                                ])
                                ->default('in_person_brazil')
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('brazil_city_id')
                                ->label('Ville d’Accueil au Brésil')
                                ->relationship('city', 'name')
                                ->placeholder('Sélectionner une ville (ex: Brasília)')
                                ->preload(),

                            Select::make('rhythm')
                                ->label('Rythme d’Apprentissage')
                                ->options([
                                    'day' => 'Cours de Jour (Intensif)',
                                    'evening' => 'Cours du Soir (Régulier)',
                                ])
                                ->default('day')
                                ->required(),
                        ]),
                    ]),

                Section::make('Enseignant & Calendrier Pédagogique')
                    ->description('Affectation du professeur et dates des sessions de cours.')
                    ->schema([
                        Select::make('teacher_id')
                            ->label('Professeur / Enseignant Référent')
                            ->options(function () {
                                return User::whereIn('role', ['teacher', 'admin'])
                                    ->get()
                                    ->mapWithKeys(function ($u) {
                                        $name = $u->profile ? trim("{$u->profile->first_name} {$u->profile->last_name}") : $u->email;
                                        return [$u->id => "{$name} ({$u->email})"];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Sélectionner un professeur certifié'),

                        Grid::make(2)->schema([
                            DatePicker::make('starts_on')
                                ->label('Date de Début des Cours'),

                            DatePicker::make('ends_on')
                                ->label('Date de Fin de Session'),
                        ]),

                        Toggle::make('is_active')
                            ->label('Classe active (ouverte aux inscriptions)')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
