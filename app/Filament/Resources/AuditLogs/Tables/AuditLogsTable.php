<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Models\AuditLog;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('actor')
                    ->label('Utilisateur / Auteur')
                    ->state(function (AuditLog $record): string {
                        $profile = $record->actor?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->actor?->email ?? 'Système';
                    })
                    ->description(fn (AuditLog $record): string => $record->actor?->email ?? '')
                    ->weight('bold'),

                TextColumn::make('action')
                    ->label('Action Réalisée')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'Création',
                        'updated' => 'Mise à jour',
                        'deleted' => 'Suppression',
                        'login' => 'Connexion',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('entity_type')
                    ->label('Module / Entité')
                    ->badge()
                    ->formatStateUsing(fn ($state) => class_basename($state ?? 'Système'))
                    ->color('primary'),

                TextColumn::make('ip_address')
                    ->label('Adresse IP')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Date & Heure')
                    ->dateTime('d/m/Y à H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordAction(ViewAction::class)
            ->recordActions([
                ViewAction::make()
                    ->label('Détails')
                    ->icon('heroicon-m-eye')
                    ->modalHeading('Détails de l’Événement d’Audit')
                    ->modalWidth('3xl'),
            ]);
    }
}
