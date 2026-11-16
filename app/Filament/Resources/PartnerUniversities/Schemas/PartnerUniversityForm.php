<?php

namespace App\Filament\Resources\PartnerUniversities\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PartnerUniversityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sigle')
                    ->label('Sigle / Acronyme')
                    ->placeholder('Ex: UNICAMP, UnB, USP')
                    ->required()
                    ->maxLength(50),

                TextInput::make('name')
                    ->label('Nom officiel de l\'université')
                    ->placeholder('Ex: Universidade Estadual de Campinas')
                    ->required()
                    ->maxLength(255),

                TextInput::make('full_name')
                    ->label('Désignation en français')
                    ->placeholder('Ex: Université d’État de Campinas')
                    ->required()
                    ->maxLength(255),

                TextInput::make('city')
                    ->label('Ville')
                    ->placeholder('Ex: Campinas')
                    ->required()
                    ->maxLength(150),

                TextInput::make('state')
                    ->label('État / Région')
                    ->placeholder('Ex: São Paulo (SP)')
                    ->maxLength(150),

                TextInput::make('rank_badge')
                    ->label('Badge Distinction / Rang')
                    ->placeholder('Ex: Top 3 Recherche, Top 5 Fédéral, N°1 Amérique Latine')
                    ->required()
                    ->maxLength(150),

                Textarea::make('highlight')
                    ->label('Point fort / Atout clé')
                    ->placeholder('Ex: 15% de l’ensemble des brevets tech et recherche du Brésil')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                TextInput::make('badge_gradient')
                    ->label('Dégradé / Couleur du badge monogramme (CSS)')
                    ->placeholder('linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%)')
                    ->default('linear-gradient(135deg, #091e42 0%, #1877f2 100%)')
                    ->maxLength(255),

                TextInput::make('website_url')
                    ->label('Lien officiel du site web')
                    ->placeholder('https://www.unicamp.br')
                    ->url()
                    ->maxLength(255),

                TextInput::make('position')
                    ->label('Ordre d’affichage')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Actif et visible sur le site')
                    ->default(true),
            ]);
    }
}

