<?php

namespace App\Filament\Resources\AppSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Information du Paramètre')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('setting_key')
                                ->label('Clé de Configuration')
                                ->required()
                                ->disabled(fn (string $operation): bool => $operation === 'edit'),

                            TextInput::make('description')
                                ->label('Description du Paramètre')
                                ->default(null),
                        ]),
                    ]),

                // 1. Cas spécial : site.branding (Identité, Logo, Slogan FR/EN/PT, Contact)
                Section::make('Identité du Site, Logo & Coordonnées')
                    ->description('Modifiez simplement les champs ci-dessous : la sauvegarde met automatiquement à jour la configuration en base de données.')
                    ->visible(fn ($get): bool => $get('setting_key') === 'site.branding')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('branding_site_name')
                                ->label('Nom du Site / École')
                                ->placeholder('Ex: Brasil·É')
                                ->required(fn ($get): bool => $get('setting_key') === 'site.branding'),

                            FileUpload::make('branding_logo_path')
                                ->label('Logo du Site (PNG / JPG / SVG / WEBP)')
                                ->disk('public')
                                ->directory('site_branding')
                                ->visibility('public')
                                ->image()
                                ->fetchFileInformation(false)
                                ->previewable(false)
                                ->openable()
                                ->downloadable(),
                        ]),

                        TextInput::make('branding_tagline_fr')
                            ->label('Texte sous le nom du site (Français)')
                            ->placeholder('ÉCOLE DE PORTUGAIS & MOBILITÉ AU BRÉSIL'),

                        Grid::make(2)->schema([
                            TextInput::make('branding_tagline_en')
                                ->label('Texte sous le nom du site (Anglais)')
                                ->placeholder('PORTUGUESE SCHOOL & STUDENT MOBILITY IN BRAZIL'),

                            TextInput::make('branding_tagline_pt')
                                ->label('Texte sous le nom du site (Portugais BR)')
                                ->placeholder('ESCOLA DE PORTUGUÊS & MOBILIDADE NO BRASIL'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('branding_contact_email')
                                ->label('Adresse E-mail de l’Application (Footer & Reçus)')
                                ->email()
                                ->placeholder('contact@brasil-e.com'),

                            TextInput::make('branding_contact_phone')
                                ->label('Numéro de Téléphone / WhatsApp (Footer & Reçus)')
                                ->placeholder('+55 61 99999-0000'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('branding_contact_address')
                                ->label('Adresse Officielle (Reçus)')
                                ->placeholder('Brasília, Distrito Federal — Brésil'),

                            TextInput::make('branding_website_url')
                                ->label('Site Web (Reçus)')
                                ->placeholder('www.brasil-e.com'),
                        ]),
                    ]),

                // 2. Cas spécial : system.default_currency
                Section::make('Devise par Défaut du Système')
                    ->visible(fn ($get): bool => $get('setting_key') === 'system.default_currency')
                    ->schema([
                        Select::make('currency_value')
                            ->label('Devise active globale')
                            ->options([
                                'EUR' => 'EUR (€) — Euro',
                                'XOF' => 'XOF (FCFA) — Franc CFA',
                                'BRL' => 'BRL (R$) — Real Brésilien',
                            ])
                            ->required(fn ($get): bool => $get('setting_key') === 'system.default_currency'),
                    ]),

                // 3. Cas spécial : bank_transfer.confirmation_delay_hours
                Section::make('Délai de Confirmation de Virement')
                    ->visible(fn ($get): bool => $get('setting_key') === 'bank_transfer.confirmation_delay_hours')
                    ->schema([
                        TextInput::make('hours_value')
                            ->label('Délai maximum de validation (en heures)')
                            ->numeric()
                            ->suffix('heures')
                            ->required(fn ($get): bool => $get('setting_key') === 'bank_transfer.confirmation_delay_hours'),
                    ]),

                // 4. Cas spécial : registration.require_terms_acceptance
                Section::make('Conditions d’Inscription')
                    ->visible(fn ($get): bool => $get('setting_key') === 'registration.require_terms_acceptance')
                    ->schema([
                        Toggle::make('bool_value')
                            ->label('Exiger l’acceptation des conditions générales à l’inscription'),
                    ]),

                // 5. Autres clés génériques
                Section::make('Valeur du Paramètre')
                    ->visible(fn ($get): bool => ! in_array($get('setting_key'), [
                        'site.branding',
                        'system.default_currency',
                        'bank_transfer.confirmation_delay_hours',
                        'registration.require_terms_acceptance',
                    ], true))
                    ->schema([
                        Textarea::make('value_json')
                            ->label('Valeur')
                            ->required(fn ($get): bool => ! in_array($get('setting_key'), [
                                'site.branding',
                                'system.default_currency',
                                'bank_transfer.confirmation_delay_hours',
                                'registration.require_terms_acceptance',
                            ], true))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
