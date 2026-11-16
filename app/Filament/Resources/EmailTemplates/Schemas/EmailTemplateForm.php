<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\SenderEmail;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Modèle d’E-mail Transactionnel')
                    ->description('Personnalisation des notifications automatiques envoyées aux étudiants.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('code')
                                ->label('Identifiant Technique (Code)')
                                ->placeholder('Ex: visa_approval_notice')
                                ->required(),

                            TextInput::make('locale')
                                ->label('Langue (Locale)')
                                ->default('fr')
                                ->required(),
                        ]),

                        TextInput::make('subject')
                            ->label('Objet du Message')
                            ->placeholder('Ex: Confirmation de votre inscription — Brasil-É')
                            ->required(),

                        Select::make('sender_email_id')
                            ->label('Adresse E-mail Expéditrice')
                            ->options(SenderEmail::pluck('email_address', 'id'))
                            ->placeholder('Adresse e-mail par défaut du système')
                            ->searchable()
                            ->preload(),

                        RichEditor::make('body_html')
                            ->label('Corps du Message (HTML / Modèle)')
                            ->required()
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Modèle Actif (Envoyé lors des événements système)')
                            ->default(true),
                    ]),
            ]);
    }
}
