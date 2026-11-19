<?php

namespace App\Filament\Resources\AppSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('setting_key')
                    ->label('Clé de Configuration')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->fontFamily('mono'),

                TextColumn::make('value_json')
                    ->label('Valeur')
                    ->formatStateUsing(function ($state, $record): string {
                        $key = $record->setting_key ?? '';
                        if ($key === 'site.branding') {
                            $d = is_array($state) ? $state : (json_decode((string) $state, true) ?: []);
                            $name = $d['site_name'] ?? 'Brasil·É';
                            $email = $d['contact_email'] ?? '';
                            $phone = $d['contact_phone'] ?? '';
                            return trim("{$name} • {$email} • {$phone}", ' •');
                        }
                        if ($key === 'system.default_currency') {
                            return match (strtoupper(trim((string) $state, "\"' "))) {
                                'XOF' => 'FCFA (XOF)',
                                'BRL' => 'Real Brésilien (R$)',
                                default => 'Euro (€)',
                            };
                        }
                        if ($key === 'bank_transfer.confirmation_delay_hours') {
                            return trim((string) $state, "\"' ") . ' heures';
                        }
                        if ($key === 'registration.require_terms_acceptance') {
                            return strtolower(trim((string) $state, "\"' ")) === 'true' ? 'Activé (Oui)' : 'Désactivé (Non)';
                        }
                        return (string) $state;
                    })
                    ->limit(60)
                    ->badge()
                    ->color('primary'),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('updated_at')
                    ->label('Dernière mise à jour')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('setting_key', 'asc')
            ->recordActions([
                EditAction::make()->label('Modifier'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ]);
    }
}
