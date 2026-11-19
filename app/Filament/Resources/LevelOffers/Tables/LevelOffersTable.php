<?php

namespace App\Filament\Resources\LevelOffers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LevelOffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('level.name')
                    ->label('Niveau de Langue')
                    ->weight('bold')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('study_mode')
                    ->label('Modalité d’Études')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_person_brazil'    => '🇧🇷 Présentiel Brésil (VITEM IV)',
                        'visitor_visa_brazil' => '🇧🇷 Présentiel Brésil (Visa Visiteur)',
                        'remote_home_country' => '💻 En Ligne Direct',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in_person_brazil'    => 'success',
                        'visitor_visa_brazil' => 'warning',
                        'remote_home_country' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('service_fee')
                    ->label('Frais de Service')
                    ->state(fn ($record): string => \App\Services\CurrencyService::formatInActiveCurrency((float) ($record->service_fee ?? 0), $record->currency ?? 'EUR'))
                    ->description(fn ($record): string => \App\Services\CurrencyService::formatSubEquivalents((float) ($record->service_fee ?? 0), $record->currency ?? 'EUR'))
                    ->weight('bold')
                    ->color('info')
                    ->sortable(),
                TextColumn::make('base_price')
                    ->label('Frais de Scolarité')
                    ->state(fn ($record): string => \App\Services\CurrencyService::formatInActiveCurrency((float) $record->base_price, $record->currency ?? 'EUR'))
                    ->description(fn ($record): string => \App\Services\CurrencyService::formatSubEquivalents((float) $record->base_price, $record->currency ?? 'EUR'))
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('installments_summary')
                    ->label('3 Tranches (Admin)')
                    ->state(function ($record): string {
                        if ($record->installment_1_amount || $record->installment_2_amount || $record->installment_3_amount) {
                            $curr = $record->currency ?? 'EUR';
                            $t1 = \App\Services\CurrencyService::formatInActiveCurrency((float) $record->installment_1_amount, $curr);
                            $t2 = \App\Services\CurrencyService::formatInActiveCurrency((float) $record->installment_2_amount, $curr);
                            $t3 = \App\Services\CurrencyService::formatInActiveCurrency((float) $record->installment_3_amount, $curr);
                            return "{$t1} / {$t2} / {$t3}";
                        }
                        return 'Non configurées';
                    })
                    ->color(fn ($record): string => ($record->installment_1_amount || $record->installment_2_amount || $record->installment_3_amount) ? 'primary' : 'gray')
                    ->badge(),
                TextColumn::make('currency')
                    ->label('Devise')
                    ->badge(),
                IconColumn::make('is_available')
                    ->label('Disponible')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Date de création')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Modifier Offre')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Modifier l’Offre de Formation')
                    ->modalSubmitActionLabel('Enregistrer les Modifications')
                    ->modalCancelActionLabel('Annuler')
                    ->modalWidth('2xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
