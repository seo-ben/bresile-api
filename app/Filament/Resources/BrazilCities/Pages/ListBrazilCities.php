<?php

namespace App\Filament\Resources\BrazilCities\Pages;

use App\Filament\Resources\BrazilCities\BrazilCityResource;
use App\Models\BrazilCity;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use OpenSpout\Reader\Common\Creator\ReaderFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListBrazilCities extends ListRecords
{
    protected static string $resource = BrazilCityResource::class;

    public function getTitle(): string
    {
        return 'Villes d’Accueil au Brésil';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle Ville')
                ->icon('heroicon-m-plus'),

            Action::make('importCsvExcel')
                ->label('Importer CSV / Excel')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Importer des Villes au Brésil')
                ->modalDescription('Téléversez un fichier CSV (.csv) ou Excel (.xlsx) pour importer ou mettre à jour la liste des villes d’accueil.')
                ->modalSubmitActionLabel('Lancer l’importation')
                ->modalIcon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('Fichier (CSV ou Excel .xlsx)')
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'application/octet-stream',
                        ])
                        ->helperText('Glissez-déposez votre fichier .csv ou .xlsx ici.'),

                    Toggle::make('update_existing')
                        ->label('Mettre à jour les villes si elles existent déjà (recherche par nom)')
                        ->default(true),

                    Placeholder::make('help_format')
                        ->label('Structure du fichier attendu')
                        ->content(new HtmlString('
                            <div style="font-size: 12px; color: #334155; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                <strong>name,state_code,is_available,position</strong><br>
                                Belo Horizonte,MG,1,6<br>
                                Fortaleza,CE,1,7<br>
                                Recife,PE,1,8
                            </div>
                            <p style="font-size: 11px; color: #64748b; margin-top: 6px;">
                                Les en-têtes peuvent être en anglais (<em>name, state_code, is_available, position</em>) ou en français (<em>ville, etat, disponible, ordre</em>).
                            </p>
                        ')),
                ])
                ->action(function (array $data): void {
                    $relativeFilePath = $data['file'];
                    $fullPath = Storage::disk('local')->path($relativeFilePath);
                    $updateExisting = (bool) ($data['update_existing'] ?? true);

                    if (!file_exists($fullPath)) {
                        Notification::make()
                            ->title('Erreur')
                            ->body('Le fichier téléversé est introuvable.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $reader = ReaderFactory::createFromFile($fullPath);
                        $reader->open($fullPath);

                        $headers = null;
                        $createdCount = 0;
                        $updatedCount = 0;
                        $skippedCount = 0;

                        foreach ($reader->getSheetIterator() as $sheet) {
                            foreach ($sheet->getRowIterator() as $row) {
                                $cells = $row->toArray();

                                // Gestion des fichiers CSV séparés par point-virgule
                                if (count($cells) === 1 && is_string($cells[0]) && str_contains($cells[0], ';')) {
                                    $cells = str_getcsv($cells[0], ';', '"', '\\');
                                }

                                $cells = array_map(fn ($c) => is_string($c) ? trim($c) : $c, $cells);

                                // Ignorer les lignes entièrement vides
                                if (empty(array_filter($cells, fn ($c) => !is_null($c) && $c !== ''))) {
                                    continue;
                                }

                                // Première ligne non vide = en-têtes
                                if ($headers === null) {
                                    $headers = array_map(function ($h) {
                                        return strtolower((string) $h);
                                    }, $cells);
                                    continue;
                                }

                                // Associer les cellules aux en-têtes
                                $rowAssoc = [];
                                foreach ($headers as $index => $headerName) {
                                    $rowAssoc[$headerName] = $cells[$index] ?? null;
                                }

                                // Résolution tolérante des colonnes (FR ou EN)
                                $name = $rowAssoc['name'] ?? $rowAssoc['ville'] ?? $rowAssoc['nom'] ?? null;
                                $state = $rowAssoc['state_code'] ?? $rowAssoc['state'] ?? $rowAssoc['etat'] ?? $rowAssoc['sigle'] ?? null;
                                $available = $rowAssoc['is_available'] ?? $rowAssoc['disponible'] ?? $rowAssoc['actif'] ?? 1;
                                $position = $rowAssoc['position'] ?? $rowAssoc['ordre'] ?? null;

                                if (empty($name)) {
                                    $skippedCount++;
                                    continue;
                                }

                                $existingCity = BrazilCity::where('name', $name)->first();

                                if ($existingCity && !$updateExisting) {
                                    $skippedCount++;
                                    continue;
                                }

                                $city = $existingCity ?: new BrazilCity();
                                $isNew = !$city->exists;

                                $city->name = (string) $name;
                                $city->state_code = !empty($state) ? strtoupper(substr((string) $state, 0, 2)) : ($city->state_code ?: 'BR');
                                
                                // Conversion booléenne tolérante
                                $availableStr = strtolower((string) $available);
                                $city->is_available = in_array($availableStr, ['1', 'true', 'oui', 'yes', 'vrai', 'actif'], true) || $available === true;

                                if (!is_null($position) && is_numeric($position)) {
                                    $city->position = (int) $position;
                                } elseif ($isNew) {
                                    $city->position = (BrazilCity::max('position') ?? 0) + 1;
                                }

                                $city->save();

                                if ($isNew) {
                                    $createdCount++;
                                } else {
                                    $updatedCount++;
                                }
                            }
                        }

                        $reader->close();
                        Storage::disk('local')->delete($relativeFilePath);

                        Notification::make()
                            ->title('Importation terminée avec succès')
                            ->body("{$createdCount} nouvelle(s) ville(s) ajoutée(s), {$updatedCount} mise(s) à jour" . ($skippedCount > 0 ? ", {$skippedCount} ignorée(s)." : "."))
                            ->success()
                            ->send();

                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Échec de l’importation')
                            ->body("Une erreur est survenue lors de la lecture du fichier : " . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('downloadTemplate')
                ->label('Télécharger Modèle CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    $csvHeaders = [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                        'Content-Disposition' => 'attachment; filename="modele_villes_bresil.csv"',
                    ];

                    return response()->streamDownload(function () {
                        $handle = fopen('php://output', 'w');
                        // UTF-8 BOM pour compatibilité Excel
                        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                        
                        fputcsv($handle, ['name', 'state_code', 'is_available', 'position']);
                        fputcsv($handle, ['Belo Horizonte', 'MG', '1', '6']);
                        fputcsv($handle, ['Fortaleza', 'CE', '1', '7']);
                        fputcsv($handle, ['Recife', 'PE', '1', '8']);
                        fputcsv($handle, ['Porto Alegre', 'RS', '1', '9']);
                        fputcsv($handle, ['Manaus', 'AM', '1', '10']);
                        fputcsv($handle, ['Florianópolis', 'SC', '1', '11']);
                        fclose($handle);
                    }, 'modele_villes_bresil.csv', $csvHeaders);
                }),
        ];
    }
}
