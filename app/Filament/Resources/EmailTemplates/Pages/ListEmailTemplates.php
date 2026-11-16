<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    public function getTitle(): string
    {
        return 'Modèles d’Emails';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau Modèle')
                ->icon('heroicon-m-plus')
                ->modalHeading('Créer un Modèle d’Email')
                ->modalWidth('3xl')
                ->createAnother(false),
        ];
    }
}
