<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\BankAccount;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\CurrencyService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Bénéficiaire & Inscription Associée')
                    ->description('Rattachement du règlement financier à un étudiant et son inscription.')
                    ->schema([
                        Select::make('student_id')
                            ->label('Étudiant / Payeur')
                            ->relationship('student', 'email')
                            ->getOptionLabelFromRecordUsing(function ($record): string {
                                $profile = $record->profile;
                                if ($profile && ($profile->first_name || $profile->last_name)) {
                                    return trim("{$profile->first_name} {$profile->last_name}") . " ({$record->email})";
                                }
                                return $record->email;
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('enrollment_id')
                            ->label('Dossier d’Inscription Associé')
                            ->options(function () {
                                return Enrollment::with(['student.profile', 'level', 'city'])
                                    ->get()
                                    ->mapWithKeys(function ($e) {
                                        $name = $e->student?->profile ? trim("{$e->student->profile->first_name} {$e->student->profile->last_name}") : ($e->student?->email ?? "Candidat");
                                        $level = $e->level?->name ?? 'Niveau non défini';
                                        $city = $e->city?->name ?? 'Brésil';
                                        return [$e->id => "{$name} — {$level} ({$city})"];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Sélectionner le dossier rattaché'),
                    ]),

                Section::make('Montant & Méthode de Règlement')
                    ->description('Détails financiers de la transaction avec conversion directe en temps réel.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('amount')
                                ->label('Montant Réglé')
                                ->numeric()
                                ->live(onBlur: true)
                                ->helperText(fn ($get) => CurrencyService::getConversionHelper((float) $get('amount'), $get('currency')))
                                ->required(),

                            Select::make('currency')
                                ->label('Devise de Paiement')
                                ->options([
                                    'EUR' => 'EUR (€) — Euro',
                                    'XOF' => 'XOF (FCFA) — Franc CFA',
                                ])
                                ->default(fn () => CurrencyService::getActiveCurrency())
                                ->live()
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Statut Comptable')
                                ->options([
                                    'confirmed' => 'Confirmé / Encaissé (Validation automatique)',
                                    'awaiting_confirmation' => 'Attente Confirmation Virement',
                                    'initiated' => 'Initié par l’étudiant',
                                    'refunded' => 'Remboursé (85%)',
                                    'failed' => 'Échec / Rejeté',
                                    'cancelled' => 'Annulé',
                                ])
                                ->default('awaiting_confirmation')
                                ->required(),

                            Select::make('purpose')
                                ->label('Motif du Paiement')
                                ->options([
                                    'level_fee' => 'Frais de Formation / Inscription',
                                    'final_settlement' => 'Solde Définitif',
                                ])
                                ->default('level_fee')
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('method')
                                ->label('Mode de Règlement')
                                ->options([
                                    'bank_transfer' => 'Virement Bancaire International',
                                    'card_visa' => 'Carte Bancaire (Visa / Mastercard)',
                                ])
                                ->default('bank_transfer')
                                ->required(),

                            Select::make('bank_account_id')
                                ->label('Compte Bancaire Récepteur (École)')
                                ->options(function () {
                                    return BankAccount::all()->mapWithKeys(function ($b) {
                                        return [$b->id => "{$b->bank_name} — {$b->account_holder} ({$b->currency})"];
                                    });
                                })
                                ->searchable()
                                ->preload()
                                ->placeholder('Sélectionner le compte de réception'),
                        ]),

                        TextInput::make('student_payment_reference')
                            ->label('Référence Virement (Ordre / Motif)')
                            ->placeholder('Ex: VIR-2026-AMADOU'),
                    ]),

                Section::make('Validation & Traçabilité Automatique')
                    ->description('L’agent validateur et l’horodatage sont enregistrés automatiquement en arrière-plan.')
                    ->schema([
                        Placeholder::make('trace_info')
                            ->label('Statut de Validation Comptable')
                            ->content(function (?Payment $record): string {
                                if (! $record || $record->status !== 'confirmed') {
                                    return 'Dès le passage au statut "Confirmé / Encaissé", votre identité et l’heure exacte seront enregistrées automatiquement en arrière-plan.';
                                }
                                $validatorName = $record->confirmedBy?->profile
                                    ? trim("{$record->confirmedBy->profile->first_name} {$record->confirmedBy->profile->last_name}")
                                    : ($record->confirmedBy?->email ?? 'Système');
                                $date = $record->confirmed_at?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i');
                                return "Encaissé et validé par {$validatorName} le {$date}.";
                            }),

                        TextInput::make('failure_reason')
                            ->label('Motif d’échec ou anomalie comptable (si rejeté)')
                            ->placeholder('Ex: Justificatif bancaire illisible, montant incomplet...')
                            ->visible(fn ($get) => in_array($get('status'), ['failed', 'cancelled'])),
                    ]),
            ]);
    }
}
