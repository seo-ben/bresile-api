<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    public function getTitle(): string
    {
        return 'Paiements & Encaissements';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Enregistrer un Paiement')
                ->icon('heroicon-m-plus')
                ->modalHeading('Nouveau Règlement Étudiant')
                ->modalDescription('Saisissez les informations de paiement pour validation comptable.')
                ->modalWidth('2xl')
                ->createAnother(false),
        ];
    }
}
