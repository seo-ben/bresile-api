<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use App\Models\AuditLog;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informations de l’Événement')
                    ->schema([
                        Placeholder::make('actor_info')
                            ->label('Auteur de l’action')
                            ->content(function (?AuditLog $record): string {
                                if (! $record) {
                                    return 'Système';
                                }
                                $profile = $record->actor?->profile;
                                if ($profile && ($profile->first_name || $profile->last_name)) {
                                    return trim("{$profile->first_name} {$profile->last_name}") . " ({$record->actor->email})";
                                }
                                return $record->actor?->email ?? 'Système automatique';
                            }),

                        Grid::make(3)->schema([
                            TextInput::make('action')
                                ->label('Action'),

                            TextInput::make('entity_type')
                                ->label('Entité modifiée')
                                ->formatStateUsing(fn ($state) => class_basename($state ?? '')),

                            TextInput::make('ip_address')
                                ->label('Adresse IP'),
                        ]),

                        Textarea::make('old_values_json')
                            ->label('Anciennes Valeurs (Avant modification)')
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                            ->rows(5)
                            ->columnSpanFull(),

                        Textarea::make('new_values_json')
                            ->label('Nouvelles Valeurs (Après modification)')
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
