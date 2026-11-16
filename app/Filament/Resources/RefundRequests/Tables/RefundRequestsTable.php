<?php

namespace App\Filament\Resources\RefundRequests\Tables;

use App\Models\RefundRequest;
use App\Services\CurrencyService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RefundRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student')
                    ->label('Étudiant / Bénéficiaire')
                    ->state(function (RefundRequest $record): string {
                        $profile = $record->student?->profile;
                        if ($profile && ($profile->first_name || $profile->last_name)) {
                            return trim("{$profile->first_name} {$profile->last_name}");
                        }
                        return $record->student?->email ?? 'Candidat';
                    })
                    ->description(fn (RefundRequest $record): string => $record->student?->email ?? '')
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

                TextColumn::make('reason')
                    ->label('Motif Légal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'visa_refusal' => 'Garantie Refus Consulaire (85%)',
                        'student_withdrawal' => 'Désistement Volontaire',
                        'school_cancellation' => 'Annulation École (100%)',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'visa_refusal' => 'warning',
                        'school_cancellation' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('total_paid_amount')
                    ->label('Frais Versés')
                    ->state(function (RefundRequest $record): string {
                        $activeCurrency = CurrencyService::getActiveCurrency();
                        $baseCurrency = $record->currency ?? 'EUR';
                        $baseAmount = (float) $record->total_paid_amount;
                        if ($activeCurrency === CurrencyService::XOF) {
                            $converted = CurrencyService::convert($baseAmount, $baseCurrency, CurrencyService::XOF);
                            return number_format($converted, 0, ',', ' ') . ' FCFA';
                        }
                        $converted = CurrencyService::convert($baseAmount, $baseCurrency, CurrencyService::EUR);
                        return number_format($converted, 2, ',', ' ') . ' €';
                    })
                    ->sortable(),

                TextColumn::make('refund_percent')
                    ->label('Taux Garanti')
                    ->suffix('%')
                    ->badge()
                    ->color('info'),

                TextColumn::make('refund_amount')
                    ->label('Montant Restitué')
                    ->state(function (RefundRequest $record): string {
                        $activeCurrency = CurrencyService::getActiveCurrency();
                        $baseCurrency = $record->currency ?? 'EUR';
                        $baseAmount = (float) $record->refund_amount;
                        if ($activeCurrency === CurrencyService::XOF) {
                            $converted = CurrencyService::convert($baseAmount, $baseCurrency, CurrencyService::XOF);
                            return number_format($converted, 0, ',', ' ') . ' FCFA';
                        }
                        $converted = CurrencyService::convert($baseAmount, $baseCurrency, CurrencyService::EUR);
                        return number_format($converted, 2, ',', ' ') . ' €';
                    })
                    ->description(function (RefundRequest $record): string {
                        $activeCurrency = CurrencyService::getActiveCurrency();
                        $baseCurrency = $record->currency ?? 'EUR';
                        $baseAmount = (float) $record->refund_amount;
                        if ($activeCurrency === CurrencyService::XOF) {
                            $converted = CurrencyService::convert($baseAmount, $baseCurrency, CurrencyService::EUR);
                            return '≈ ' . number_format($converted, 2, ',', ' ') . ' €';
                        }
                        $converted = CurrencyService::convert($baseAmount, $baseCurrency, CurrencyService::XOF);
                        return '≈ ' . number_format($converted, 0, ',', ' ') . ' FCFA';
                    })
                    ->sortable()
                    ->weight('bold')
                    ->color('danger'),

                TextColumn::make('status')
                    ->label('Statut Trésorerie')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Remboursement Effectué',
                        'submitted' => 'Demande Soumise (À Payer)',
                        'in_review' => 'En Vérification',
                        'rejected' => 'Rejeté',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'submitted' => 'danger',
                        'in_review' => 'warning',
                        'rejected' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('payout_reference')
                    ->label('Réf. Virement Bancaire')
                    ->fontFamily('mono')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('paid_at')
                    ->label('Date Restitution')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordAction(EditAction::class)
            ->filters([
                SelectFilter::make('status')
                    ->label('Filtrer par Statut')
                    ->options([
                        'submitted' => 'À Payer (En attente)',
                        'paid' => 'Remboursé',
                        'in_review' => 'En Examen',
                    ]),
            ])
            ->recordActions([
                Action::make('mark_as_paid')
                    ->label('Confirmer Virement Restitution')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->visible(fn (RefundRequest $record) => in_array($record->status, ['submitted', 'in_review', 'draft']))
                    ->form([
                        TextInput::make('payout_reference')
                            ->label('Référence bancaire du virement émis')
                            ->placeholder('Ex: VIR-REFUND-2026-9812')
                            ->required(),
                    ])
                    ->action(function (RefundRequest $record, array $data) {
                        $record->update([
                            'status'           => 'paid',
                            'payout_reference' => $data['payout_reference'],
                            'paid_at'          => now(),
                            'reviewed_by'      => auth()->id(),
                            'reviewed_at'      => now(),
                        ]);

                        Notification::make()
                            ->title('Remboursement validé avec succès')
                            ->body('Le virement des 85% garantis a été enregistré et le dossier clôturé.')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label('Gérer Dossier')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading(fn (RefundRequest $record) => "Dossier de Remboursement — " . ($record->student?->profile ? trim("{$record->student->profile->first_name} {$record->student->profile->last_name}") : ($record->student?->email ?? 'Bénéficiaire')))
                    ->modalDescription('Vérification et validation de la restitution consulaire 85%.')
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
