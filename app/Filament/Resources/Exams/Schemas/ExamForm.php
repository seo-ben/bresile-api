<?php

namespace App\Filament\Resources\Exams\Schemas;

use App\Models\ClassGroup;
use App\Models\Level;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Configuration de l’Épreuve')
                    ->description('Détails de l’examen et affectation académique.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Intitulé de l’Épreuve')
                            ->placeholder('Ex: Évaluation Finale Écrite — A1')
                            ->required(),

                        Grid::make(2)->schema([
                            Select::make('level_id')
                                ->label('Niveau CECRL')
                                ->options(Level::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('class_group_id')
                                ->label('Classe / Promotion')
                                ->options(ClassGroup::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->placeholder('Toutes les promotions de ce niveau'),
                        ]),

                        Grid::make(3)->schema([
                            DatePicker::make('exam_date')
                                ->label('Date de l’Examen')
                                ->default(now())
                                ->required(),

                            TextInput::make('max_score')
                                ->label('Note Maximale (Barème)')
                                ->numeric()
                                ->default(20.0)
                                ->required(),

                            TextInput::make('weight')
                                ->label('Coefficient')
                                ->numeric()
                                ->default(1.0)
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
