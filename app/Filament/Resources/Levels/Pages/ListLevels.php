<?php

namespace App\Filament\Resources\Levels\Pages;

use App\Filament\Resources\Levels\LevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLevels extends ListRecords
{
    protected static string $resource = LevelResource::class;

    public function getTitle(): string
    {
        return 'Niveaux CECRL de Portugais';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouveau Niveau CECRL'),
        ];
    }
}
