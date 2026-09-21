<?php

namespace App\Filament\Widgets;

use App\Actions\Inventory\GetSiteBalanceAsOf;
use App\Models\Material;
use App\Models\Site;
use Filament\Widgets\ChartWidget;

/**
 * Widget 1 — Per-Material Site Comparison
 *
 * Shows the current stock balance of a selected material across ALL active
 * project sites as a clustered bar chart. Sites with zero stock still appear
 * as empty bars so gaps are immediately visible.
 */
class InventoryByMaterialChart extends ChartWidget
{
    protected static ?string $heading = 'Stock Balance by Site — Per Material';

    protected static ?string $description = 'Select a material to see how much stock is held at each project site. Zero-balance sites are shown so coverage gaps are visible.';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '420px';

    /** Currently selected material_id; defaults to first active material. */
    public ?string $selectedMaterialId = null;

    protected function getFilters(): ?array
    {
        return Material::query()
            ->where('is_active', true)
            ->withCount(['transactions as approved_count' => fn ($q) => $q->where('status', 'approved')])
            ->orderByDesc('approved_count')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Material $m): array => [
                (string) $m->id => ($m->approved_count > 0 ? '● ' : '○ ')."[{$m->category}] {$m->name}",
            ])
            ->all();
    }

    protected function getData(): array
    {
        // Resolve selected material: default to first filter option (which has approved data)
        $filters = $this->getFilters() ?? [];
        $materialId = $this->filter !== null && isset($filters[$this->filter])
            ? (int) $this->filter
            : (int) array_key_first($filters);

        if ($materialId === 0) {
            return ['datasets' => [], 'labels' => []];
        }

        $material = Material::find($materialId);

        // Load active sites
        $sites = Site::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        /** @var GetSiteBalanceAsOf $balanceAction */
        $balanceAction = app(GetSiteBalanceAsOf::class);

        // Build a site_id → balance lookup from the company-wide matrix
        $matrix = $balanceAction->companyWideMatrix();
        $materialRow = $matrix['matrix'][$materialId] ?? [];

        $labels = [];
        $data = [];
        $colors = [];
        $borders = [];

        foreach ($sites as $site) {
            $balance = $materialRow[$site->id] ?? 0.0;
            $labels[] = $site->code;
            $data[] = round($balance, 2);

            if ($balance > 0) {
                $colors[] = '#6366f1'; // Solid vibrant Indigo
                $borders[] = '#4338ca';
            } else {
                $colors[] = 'rgba(203, 213, 225, 0.45)'; // Subtle soft gray for zero-balance gap
                $borders[] = 'rgba(148, 163, 184, 0.3)';
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => ($material?->name ?? 'Material').' (Stock by Site)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $borders,
                    'borderWidth' => 1,
                    'borderRadius' => 5,
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
                'legend' => ['display' => true, 'position' => 'top'],
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
                        'maxRotation' => 55,
                        'minRotation' => 30,
                        'font' => ['size' => 10],
                        'autoSkip' => false,
                    ],
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Quantity (Units / Sets / ML)',
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
