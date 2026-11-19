<?php

namespace App\Filament\Resources\OfficialDocuments\Pages;

use App\Filament\Resources\OfficialDocuments\OfficialDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListOfficialDocuments extends ListRecords
{
    protected static string $resource = OfficialDocumentResource::class;

    public function getTitle(): string
    {
        return 'Documents Officiels & Attestations';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Envoyer un Document Officiel')
                ->icon('heroicon-m-document-arrow-up')
                ->modalHeading('Envoyer un Document Officiel à un Étudiant')
                ->modalSubmitActionLabel('Envoyer à l’étudiant')
                ->modalCancelActionLabel('Annuler')
                ->modalWidth('2xl')
                ->createAnother(false)
                ->using(function (array $data) {
                    return OfficialDocumentResource::createDocumentFromUpload(
                        visaApplicationId: (int) $data['visa_application_id'],
                        title: (string) $data['title'],
                        storagePath: (string) $data['uploaded_file_path']
                    );
                })
                ->successNotification(
                    Notification::make()
                        ->title('Document envoyé à l’étudiant')
                        ->body('Le fichier est désormais téléchargeable dans son espace étudiant.')
                        ->success()
                ),
        ];
    }
}
