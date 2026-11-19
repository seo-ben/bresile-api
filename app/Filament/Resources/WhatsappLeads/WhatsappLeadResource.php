<?php

namespace App\Filament\Resources\WhatsappLeads;

use App\Filament\Resources\WhatsappLeads\Pages\ListWhatsappLeads;
use App\Models\WhatsappLead;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsappLeadResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'Inscriptions & Visas';
    protected static ?int $navigationSort = 6;

    protected static ?string $model = WhatsappLead::class;
    protected static ?string $recordTitleAttribute = 'full_whatsapp';

    protected static ?string $navigationLabel = 'Contacts WhatsApp';
    protected static ?string $pluralModelLabel = 'Contacts WhatsApp';
    protected static ?string $modelLabel = 'Contact WhatsApp';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-phone-arrow-down-left';

    public static function getGloballySearchableAttributes(): array
    {
        return ['full_whatsapp', 'phone_number', 'country_name', 'admin_note'];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            WhatsappLead::ensureTableExists();
            $count = WhatsappLead::where('status', 'new')->count();
            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Coordonnées WhatsApp du Prospect')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('dial_code')
                                ->label('Indicatif Pays')
                                ->required(),

                            TextInput::make('country_name')
                                ->label('Pays'),

                            TextInput::make('phone_number')
                                ->label('Numéro WhatsApp')
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Statut de Suivi')
                                ->options([
                                    'new'       => 'Nouveau (À contacter)',
                                    'contacted' => 'Contacté sur WhatsApp',
                                    'enrolled'  => 'Converti / Inscrit',
                                ])
                                ->default('new')
                                ->required(),

                            Select::make('locale')
                                ->label('Langue choisie sur le site')
                                ->options([
                                    'fr' => 'Français (FR)',
                                    'en' => 'Anglais (EN)',
                                    'pt' => 'Portugais du Brésil (PT-BR)',
                                ])
                                ->default('fr'),
                        ]),

                        Textarea::make('admin_note')
                            ->label('Note de Suivi Administrateur')
                            ->placeholder('Ex: Contacté sur WhatsApp, intéressé par le Niveau A1 à Brasília...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        WhatsappLead::ensureTableExists();

        return $table
            ->columns([
                TextColumn::make('full_whatsapp')
                    ->label('Numéro WhatsApp')
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Numéro copié !')
                    ->url(fn (WhatsappLead $record): string => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $record->full_whatsapp), shouldOpenInNewTab: true)
                    ->color('success')
                    ->icon('heroicon-m-chat-bubble-oval-left-ellipsis')
                    ->searchable(),

                TextColumn::make('country_name')
                    ->label('Pays / Indicatif')
                    ->formatStateUsing(fn (?string $state, WhatsappLead $record): string => $state ? "{$state} ({$record->dial_code})" : $record->dial_code)
                    ->searchable(),

                TextColumn::make('locale')
                    ->label('Langue')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => strtoupper($state ?: 'FR'))
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new'       => 'Nouveau',
                        'contacted' => 'Contacté',
                        'enrolled'  => 'Inscrit',
                        default     => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new'       => 'warning',
                        'contacted' => 'info',
                        'enrolled'  => 'success',
                        default     => 'gray',
                    }),

                TextColumn::make('admin_note')
                    ->label('Note Admin')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Reçu le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Traiter')
                    ->modalHeading('Suivi du Contact WhatsApp')
                    ->modalWidth('2xl'),
                DeleteAction::make()
                    ->label('Supprimer'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'new'       => 'Nouveau',
                        'contacted' => 'Contacté',
                        'enrolled'  => 'Inscrit',
                    ]),
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
            'index' => ListWhatsappLeads::route('/'),
        ];
    }
}
