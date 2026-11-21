<?php

namespace App\Filament\Resources\CmsContents\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CmsContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Contenu de Page du Site Web')
                    ->description('Modifiez le titre et le contenu affiché sur le site public (traduction automatique en EN et PT si saisi en FR).')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('content_key')
                                ->label('Identifiant de page (Clé)')
                                ->placeholder('Ex: about_us')
                                ->required()
                                ->disabled(fn (string $operation): bool => $operation === 'edit'),

                            Select::make('locale')
                                ->label('Langue')
                                ->options([
                                    'fr' => 'Français (FR)',
                                    'en' => 'Anglais (EN)',
                                    'pt' => 'Portugais du Brésil (PT)',
                                ])
                                ->default('fr')
                                ->required(),

                            Toggle::make('is_published')
                                ->label('Publié sur le site')
                                ->default(true)
                                ->inline(false)
                                ->required(),
                        ]),

                        TextInput::make('title')
                            ->label('Titre principal de la page')
                            ->required()
                            ->maxLength(190),

                        RichEditor::make('body')
                            ->label('Contenu complet de la page')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'h2',
                                'h3',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
