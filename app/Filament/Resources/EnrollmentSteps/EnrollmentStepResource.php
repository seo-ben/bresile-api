<?php

namespace App\Filament\Resources\EnrollmentSteps;

use App\Filament\Resources\EnrollmentSteps\Pages\ListEnrollmentSteps;
use App\Models\EnrollmentStep;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentStepResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'Configuration & CMS';
    protected static ?int $navigationSort = 4;

    protected static ?string $model = EnrollmentStep::class;
    protected static ?string $slug = 'enrollment-steps';
    protected static ?string $navigationLabel = 'Schéma des Étapes';
    protected static ?string $pluralModelLabel = 'Schéma des Étapes d’Inscription';
    protected static ?string $modelLabel = 'Étape du Schéma';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Bloc d’Étape du Schéma Architectural')
                    ->description('Définissez le numéro, le titre, l’icône et les embranchements. Le schéma sur la page d’accueil se redessine et se traduit automatiquement (FR / EN / PT).')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('position')
                                ->label('Ordre / Numéro')
                                ->numeric()
                                ->default(fn () => (int) EnrollmentStep::max('position') + 1)
                                ->required(),

                            Select::make('icon')
                                ->label('Icône')
                                ->options([
                                    'file-signature' => '📝 Formulaire / Inscription',
                                    'compass'        => '🧭 Parcours / Orientation',
                                    'credit-card'    => '💳 Paiement / Carte',
                                    'passport'       => '🛂 Visa / Consulat',
                                    'plane-departure'=> '✈️ Voyage / Départ',
                                    'graduation-cap' => '🎓 Diplôme / Université',
                                    'shield-halved'  => '🛡️ Garantie 85 %',
                                    'building-columns'=> '🏛️ Campus / École',
                                ])
                                ->default('file-signature')
                                ->required(),

                            Select::make('accent_color')
                                ->label('Couleur d’accent')
                                ->options([
                                    'navy'    => 'Bleu Marine (Institutionnel)',
                                    'emerald' => 'Vert Émeraude (Brésil)',
                                    'sky'     => 'Bleu Ciel',
                                    'amber'   => 'Orange / Or',
                                ])
                                ->default('emerald')
                                ->required(),
                        ]),

                        TextInput::make('title')
                            ->label('Titre de l’étape (en Français)')
                            ->placeholder('Ex: Inscription & Dossier en Ligne')
                            ->required()
                            ->maxLength(160),

                        TextInput::make('subtitle')
                            ->label('Description courte (en Français)')
                            ->placeholder('Ex: Création de votre espace étudiant et choix du niveau CECRL.')
                            ->maxLength(255),

                        Repeater::make('branches_json')
                            ->label('Embranchements / Sous-options affichés sous le bloc (Optionnel)')
                            ->addActionLabel('Ajouter une branche')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('label')
                                        ->label('Libellé de la branche')
                                        ->placeholder('Ex: Visa Accordé → Arrivée au Brésil')
                                        ->required()
                                        ->columnSpan(2),

                                    Select::make('tone')
                                        ->label('Couleur pastille')
                                        ->options([
                                            'emerald' => 'Vert (Succès / Brésil)',
                                            'sky'     => 'Bleu (Information)',
                                            'amber'   => 'Orange (Option)',
                                            'rose'    => 'Rouge / Garantie 85%',
                                        ])
                                        ->default('emerald')
                                        ->required(),
                                ]),
                            ])
                            ->collapsible()
                            ->defaultItems(0),

                        Toggle::make('is_active')
                            ->label('Afficher cette étape dans le schéma public')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        EnrollmentStep::ensureTableExists();

        return $table
            ->reorderable('position')
            ->defaultSort('position', 'asc')
            ->columns([
                TextColumn::make('position')
                    ->label('N°')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => str_pad((string) $state, 2, '0', STR_PAD_LEFT))
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Titre de l’Étape')
                    ->weight('bold')
                    ->description(fn (EnrollmentStep $record): string => $record->subtitle ?? '')
                    ->searchable(),

                TextColumn::make('branches_json')
                    ->label('Branches')
                    ->state(fn (EnrollmentStep $record): string => is_array($record->branches_json) && count($record->branches_json) > 0 ? count($record->branches_json) . ' branche(s)' : 'Directe')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->modalHeading('Modifier l’Étape du Schéma')
                    ->modalSubmitActionLabel('Enregistrer')
                    ->modalWidth('3xl'),
                DeleteAction::make()->label('Supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnrollmentSteps::route('/'),
        ];
    }
}
