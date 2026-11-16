<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class EnrollmentsChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Progression des Inscriptions & Mobilité VITEM IV';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Inscriptions Portugais Brésilien',
                    'data' => [2, 4, 7, 11, 15, 18, 21, 24],
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Dossiers Visa VITEM IV Déposés',
                    'data' => [1, 3, 5, 9, 13, 16, 19, 21],
                    'borderColor' => '#0284c7',
                    'backgroundColor' => 'rgba(2, 132, 199, 0.10)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => ['Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
