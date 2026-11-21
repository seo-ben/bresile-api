<?php

namespace App\Filament\Pages;

use App\Services\SiteSettingsService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteIdentitySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \UnitEnum|string|null $navigationGroup = 'Configuration & CMS';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Identité & Contact du Site';
    protected static ?string $title = 'Identité du Site, Logo & Coordonnées';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-swatch';
    protected string $view = 'filament.pages.site-identity-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSettingsService::get();
        $this->form->fill([
            'site_name'       => $settings['site_name'],
            'logo_path'       => $settings['logo_path'],
            'tagline_fr'      => $settings['tagline_fr'],
            'tagline_en'      => $settings['tagline_en'],
            'tagline_pt'      => $settings['tagline_pt'],
            'contact_email'   => $settings['contact_email'],
            'contact_phone'       => $settings['contact_phone'],
            'contact_address'     => $settings['contact_address'],
            'website_url'         => $settings['website_url'],
            'cert_director_name'  => $settings['cert_director_name'],
            'cert_director_title' => $settings['cert_director_title'],
            'cert_weekly_hours'   => $settings['cert_weekly_hours'],
            'cert_legal_footer'   => $settings['cert_legal_footer'],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Logo & Nom du Site')
                    ->description('Ces éléments s’affichent dans la barre de navigation, l’espace étudiant, le panneau admin et sur tous les reçus de paiement officiels.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('site_name')
                                ->label('Nom du Site / École')
                                ->placeholder('Ex: Brasil·É')
                                ->required()
                                ->maxLength(80),

                            FileUpload::make('logo_path')
                                ->label('Logo du Site (PNG / JPG / SVG / WEBP)')
                                ->disk('public')
                                ->directory('site_branding')
                                ->visibility('public')
                                ->image()
                                ->fetchFileInformation(false)
                                ->previewable(false)
                                ->openable()
                                ->downloadable()
                                ->helperText('Téléversez votre logo officiel. Il remplacera automatiquement le logo partout sur le site et sur les reçus.'),
                        ]),
                    ]),

                Section::make('Texte sous le Nom du Site (Slogan Multilingue)')
                    ->description('S’adapte automatiquement selon la langue choisie par l’utilisateur (FR / EN / PT-BR).')
                    ->schema([
                        TextInput::make('tagline_fr')
                            ->label('Sous-titre en Français (FR)')
                            ->placeholder('ÉCOLE DE PORTUGAIS & MOBILITÉ AU BRÉSIL')
                            ->required()
                            ->maxLength(160),

                        Grid::make(2)->schema([
                            TextInput::make('tagline_en')
                                ->label('Sous-titre en Anglais (EN)')
                                ->placeholder('PORTUGUESE SCHOOL & STUDENT MOBILITY IN BRAZIL')
                                ->maxLength(160),

                            TextInput::make('tagline_pt')
                                ->label('Sous-titre en Portugais (PT-BR)')
                                ->placeholder('ESCOLA DE PORTUGUÊS & MOBILIDADE NO BRASIL')
                                ->maxLength(160),
                        ]),
                    ]),

                Section::make('Coordonnées Officielles (Footer & Reçus de Paiement)')
                    ->description('Affichées dans le pied de page à la place des réseaux sociaux et en en-tête des reçus téléchargeables.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('contact_email')
                                ->label('Adresse E-mail de l’Application')
                                ->email()
                                ->placeholder('contact@brasil-e.com')
                                ->required(),

                            TextInput::make('contact_phone')
                                ->label('Numéro de Téléphone / WhatsApp de l’Application')
                                ->placeholder('+55 61 99999-0000')
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('contact_address')
                                ->label('Adresse Officielle (affichée sur les reçus)')
                                ->placeholder('Brasília, Distrito Federal — Brésil'),

                            TextInput::make('website_url')
                                ->label('Site Web (affiché sur les reçus)')
                                ->placeholder('www.brasil-e.com'),
                        ]),
                    ]),

                Section::make('Personnalisation de l’Attestation Officielle d’Inscription (QR Code)')
                    ->description('Ces informations apparaissent automatiquement sur les attestations sécurisées générées après validation du paiement.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('cert_director_name')
                                ->label('Nom du Signataire / Directeur')
                                ->placeholder('Direction Pédagogique Brasil·É'),

                            TextInput::make('cert_director_title')
                                ->label('Fonction du Signataire')
                                ->placeholder('Direction des Admissions & Mobilité Internationale'),
                        ]),

                        TextInput::make('cert_weekly_hours')
                            ->label('Charge horaire officielle mentionnée sur l’attestation')
                            ->placeholder('20 heures / semaine (Minimum consulaire VITEM IV respecté)'),

                        TextInput::make('cert_legal_footer')
                            ->label('Mention légale de pied d’attestation')
                            ->placeholder('Document officiel sécurisé et vérifiable par QR Code.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSettingsService::save([
            'site_name'       => $state['site_name'] ?? 'Brasil·É',
            'logo_path'       => $state['logo_path'] ?: null,
            'tagline_fr'      => $state['tagline_fr'] ?? '',
            'tagline_en'      => $state['tagline_en'] ?? '',
            'tagline_pt'      => $state['tagline_pt'] ?? '',
            'contact_email'   => $state['contact_email'] ?? '',
            'contact_phone'   => $state['contact_phone'] ?? '',
            'contact_address'     => $state['contact_address'] ?? '',
            'website_url'         => $state['website_url'] ?? '',
            'cert_director_name'  => $state['cert_director_name'] ?? '',
            'cert_director_title' => $state['cert_director_title'] ?? '',
            'cert_weekly_hours'   => $state['cert_weekly_hours'] ?? '',
            'cert_legal_footer'   => $state['cert_legal_footer'] ?? '',
        ]);

        Notification::make()
            ->title('Paramètres d’identité enregistrés')
            ->body('Le logo, le nom du site, le slogan multilingue et les coordonnées ont été mis à jour sur tout le système.')
            ->success()
            ->send();
    }
}
