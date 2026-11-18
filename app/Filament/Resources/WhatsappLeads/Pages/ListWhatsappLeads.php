<?php

namespace App\Filament\Resources\WhatsappLeads\Pages;

use App\Filament\Resources\WhatsappLeads\WhatsappLeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWhatsappLeads extends ListRecords
{
    protected static string $resource = WhatsappLeadResource::class;

    public function getTitle(): string
    {
        return 'Contacts WhatsApp reçus depuis le site';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un numéro')
                ->icon('heroicon-m-plus')
                ->modalHeading('Ajouter un Contact WhatsApp')
                ->modalWidth('2xl')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['full_whatsapp'] = trim(($data['dial_code'] ?? '') . ' ' . ($data['phone_number'] ?? ''));
                    return $data;
                })
                ->createAnother(false),
        ];
    }
}
