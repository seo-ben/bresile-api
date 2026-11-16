<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use App\Services\CurrencyService;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student')
                    ->label('Étudiant / Payeur')
                    ->state(function (Payment $record): string {
                        $profile = $record->student?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->student?->email ?? 'Candidat';
                    })
                    ->description(fn (Payment $record): string => $record->student?->email ?? '')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%")
                              ->orWhereHas('profile', function ($qp) use ($search) {
                                  $qp->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%");
                              });
                        });
                    })
                    ->weight('bold'),

                TextColumn::make('enrollment.level.name')
                    ->label('Formation')
                    ->badge()
                    ->color('emerald')
                    ->description(fn (Payment $record) => $record->enrollment?->city?->name ? 'Destination: ' . $record->enrollment->city->name : ''),

                TextColumn::make('amount')
                    ->label('Montant Réglé')
                    ->state(function (Payment $record): string {
                        $activeCurrency = CurrencyService::getActiveCurrency();
                        $baseCurrency = $record->currency ?? 'EUR';
                        $baseAmount = (float) $record->amount;
                        if ($activeCurrency === CurrencyService::XOF) {
                            $converted = CurrencyService::convert($baseAmount, $baseCurrency, CurrencyService::XOF);
                            return number_format($converted, 0, ',', ' ') . ' FCFA';
                        }
                        $converted = CurrencyService::convert($baseAmount, $baseCurrency, CurrencyService::EUR);
                        return number_format($converted, 2, ',', ' ') . ' €';
                    })
                    ->description(function (Payment $record): string {
                        $activeCurrency = CurrencyService::getActiveCurrency();
                        $baseCurrency = $record->currency ?? 'EUR';
                        $baseAmount = (float) $record->amount;
                        if ($activeCurrency === CurrencyService::XOF) {
                            $converted = CurrencyService::convert($baseAmount, $baseCurrency, CurrencyService::EUR);
                            return '≈ ' . number_format($converted, 2, ',', ' ') . ' €';
                        }
                        $converted = CurrencyService::convert($baseAmount, $baseCurrency, CurrencyService::XOF);
                        return '≈ ' . number_format($converted, 0, ',', ' ') . ' FCFA';
                    })
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('method')
                    ->label('Mode de Règlement')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'bank_transfer' => 'Virement Bancaire',
                        'pix' => 'Pix (Brésil)',
                        'credit_card' => 'Carte Bancaire',
                        'cash' => 'Espèces',
                        default => ucfirst($state ?? 'Virement'),
                    })
                    ->color('info'),

                TextColumn::make('bankAccount.bank_name')
                    ->label('Compte Récepteur')
                    ->default('Compte Général École')
                    ->icon('heroicon-m-building-library'),

                TextColumn::make('status')
                    ->label('Statut Comptable')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => 'Confirmé & Reçu',
                        'awaiting_confirmation' => 'À Valider (Virement)',
                        'failed' => 'Rejeté / Échoué',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'awaiting_confirmation' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('gateway_reference')
                    ->label('Référence Transaction')
                    ->fontFamily('mono')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('confirmed_at')
                    ->label('Date Confirmation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Date Initiation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordAction(EditAction::class)
            ->filters([
                SelectFilter::make('status')
                    ->label('Filtrer par Statut')
                    ->options([
                        'awaiting_confirmation' => 'À Valider (En attente)',
                        'confirmed' => 'Confirmé',
                        'failed' => 'Échoué',
                    ]),
                SelectFilter::make('method')
                    ->label('Filtrer par Méthode')
                    ->options([
                        'bank_transfer' => 'Virement Bancaire',
                        'pix' => 'Pix',
                        'credit_card' => 'Carte',
                    ]),
            ])
            ->recordActions([
                Action::make('confirm_transfer')
                    ->label('Valider Virement')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record) => $record->status === 'awaiting_confirmation')
                    ->requiresConfirmation()
                    ->modalHeading('Validation comptable du virement')
                    ->modalDescription('Confirmez-vous la bonne réception des fonds ? Cela mettra à jour le solde étudiant et émettra le reçu officiel.')
                    ->action(function (Payment $record) {
                        app(PaymentService::class)->confirmPayment($record, (int) auth()->id());
                        Notification::make()
                            ->title('Paiement validé avec succès')
                            ->body('Le virement a été confirmé et le reçu est disponible.')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label('Gérer Règlement')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading(fn (Payment $record) => "Détails du Paiement — " . ($record->student?->profile ? trim("{$record->student->profile->first_name} {$record->student->profile->last_name}") : ($record->student?->email ?? 'Règlement')))
                    ->modalDescription('Consultez et validez le paiement de l’étudiant.')
                    ->modalSubmitActionLabel('Enregistrer les Modifications')
                    ->modalCancelActionLabel('Annuler')
                    ->modalWidth('2xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ]);
    }
}
