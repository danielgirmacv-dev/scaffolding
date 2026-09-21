<?php

namespace App\Filament\Widgets;

use App\Actions\Inventory\GetSiteBalanceAsOf;
use Filament\Widgets\ChartWidget;

/**
 * Widget 3 — Total Company Inventory by Material
 *
 * Shows the TOTAL SUM row from the Company Balance sheet: the company-wide
 * aggregate quantity for each material type. No site filter — always shows
 * the full company picture.
 */
class CompanyInventoryTotalsChart extends ChartWidget
{
    protected static ?string $heading = 'Total Company Inventory — All Materials';

    protected static ?string $description = 'Company-wide aggregate stock per material type (equivalent to the TOTAL SUM row in the Company Balance Sheet).';

    protected static ?int $sort = 30;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '420px';

    protected function getData(): array
    {
        /** @var GetSiteBalanceAsOf $balanceAction */
        $balanceAction = app(GetSiteBalanceAsOf::class);
        $matrix = $balanceAction->companyWideMatrix();

        $materials = $matrix['materials'];
        $totals = $matrix['totals'];

        $labels = [];
        $data = [];
        $colors = [];

        $palette = [
            '#6366f1', // indigo
            '#10b981', // emerald
            '#f59e0b', // amber
            '#06b6d4', // cyan
            '#8b5cf6', // violet
            '#ec4899', // pink
            '#3b82f6', // blue
            '#14b8a6', // teal
            '#f97316', // orange
            '#84cc16', // lime
            '#a855f7', // purple
            '#0ea5e9', // light blue
            '#e11d48', // rose
            '#10b981', // green
            '#d97706', // amber dark
            '#64748b', // slate
        ];

        foreach ($materials as $index => $material) {
            $total = (float) ($totals[$material->id] ?? 0.0);
            $labels[] = $material->name;
            $data[] = round($total, 2);
            $colors[] = $palette[$index % count($palette)];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Company Stock (all sites)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderRadius' => 6,
                    'borderWidth' => 0,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'backgroundColor' => 'rgba(15, 23, 42, 0.92)',
                    'titleFont' => ['size' => 13, 'weight' => 'bold'],
                    'bodyFont' => ['size' => 12],
                    'padding' => 10,
                    'cornerRadius' => 8,
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'maxRotation' => 50,
                        'minRotation' => 25,
                        'font' => ['size' => 11],
                        'autoSkip' => false,
                    ],
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Total Quantity (Units / Sets / ML)',
                        'font' => ['weight' => 'bold', 'size' => 12],
                    ],
                    'grid' => [
                        'color' => 'rgba(156, 163, 175, 0.15)',
                    ],
                ],
            ],
        ];
    }
}
