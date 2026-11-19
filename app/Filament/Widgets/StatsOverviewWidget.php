<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\VisaApplication;
use App\Services\CurrencyService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $totalEnrollments = Enrollment::count();
        $pendingPayments = Payment::where('status', 'awaiting_confirmation')->count();

        // Calculate total confirmed in EUR (converting each payment from its stored currency to EUR)
        $confirmedPayments = Payment::where('status', 'confirmed')->get(['amount', 'currency']);
        $totalEur = 0.0;
        foreach ($confirmedPayments as $p) {
            $totalEur += CurrencyService::convert((float) $p->amount, $p->currency ?: CurrencyService::EUR, CurrencyService::EUR);
        }

        $mainDisplay = CurrencyService::formatInActiveCurrency($totalEur, CurrencyService::EUR);
        $subDisplay = CurrencyService::formatSubEquivalents($totalEur, CurrencyService::EUR);

        $pendingVisas = VisaApplication::where('visa_outcome', 'pending')->count();
        $pendingRefunds = RefundRequest::where('status', 'submitted')->count();

        return [
            Stat::make('Inscriptions Actives', (string) $activeEnrollments)
                ->description("Sur {$totalEnrollments} dossiers enregistrés")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart([3, 5, 8, 12, 17, 21, $totalEnrollments])
                ->color('success'),

            Stat::make('Frais Encaissés', $mainDisplay)
                ->description("{$subDisplay} • " . ($pendingPayments > 0 ? "{$pendingPayments} à confirmer" : 'Tous virements validés'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([1200, 2400, 4800, 6500, 8100, 9250])
                ->color('primary'),

            Stat::make('Dossiers Visa VITEM IV', (string) $pendingVisas)
                ->description('Dossiers consulaires en cours d’instruction')
                ->descriptionIcon('heroicon-m-globe-americas')
                ->chart([2, 2, 3, 2, 2, 2])
                ->color('info'),

            Stat::make('Garantie Refus (85%)', (string) $pendingRefunds)
                ->description('Demandes de remboursement à traiter')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->chart([1, 2, 1, 0, 0, $pendingRefunds])
                ->color($pendingRefunds > 0 ? 'danger' : 'gray'),
        ];
    }
}
