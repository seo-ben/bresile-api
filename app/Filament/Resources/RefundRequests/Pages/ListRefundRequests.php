<?php

namespace App\Filament\Resources\RefundRequests\Pages;

use App\Filament\Resources\RefundRequests\RefundRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefundRequests extends ListRecords
{
    protected static string $resource = RefundRequestResource::class;

    public function getTitle(): string
    {
        return 'Garantie Refus Consulaire (Remboursements 85%)';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau Dossier Remboursement')
                ->icon('heroicon-m-plus')
                ->modalHeading('Ouvrir une Demande de Remboursement')
                ->modalDescription('Initiez le dossier de restitution des fonds selon la garantie consulaire 85%.')
                ->modalWidth('3xl')
                ->createAnother(false),
        ];
    }
}
