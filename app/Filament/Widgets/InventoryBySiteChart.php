<?php

namespace App\Filament\Widgets;

use App\Actions\Inventory\GetSiteBalanceAsOf;
use App\Models\Site;
use Filament\Widgets\ChartWidget;

/**
 * Widget 2 — Per-Site Material Breakdown
 *
 * Shows the current stock balance of ALL materials at a selected project site.
 * This is a 90-degree rotation of Widget 1: materials on the Y-axis, quantity
 * on the X-axis (horizontal bar chart).
 */
class InventoryBySiteChart extends ChartWidget
{
    protected static ?string $heading = 'Material Breakdown by Site';

    protected static ?string $description = 'Select a site to see its complete material inventory breakdown. Materials with zero balance are still shown.';

    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '520px';

    protected function getFilters(): ?array
    {
        return Site::query()
            ->where('status', 'active')
            ->withCount(['transactions as approved_count' => fn ($q) => $q->where('status', 'approved')])
            ->orderByDesc('approved_count')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Site $s): array => [
                (string) $s->id => ($s->approved_count > 0 ? '● ' : '○ ')."[{$s->code}] {$s->name}",
            ])
            ->all();
    }

    protected function getData(): array
    {
        $filters = $this->getFilters() ?? [];
        $siteId = $this->filter !== null && isset($filters[$this->filter])
            ? (int) $this->filter
            : (int) array_key_first($filters);

        if ($siteId === 0) {
            return ['datasets' => [], 'labels' => []];
        }

        $site = Site::find($siteId);

        /** @var GetSiteBalanceAsOf $balanceAction */
        $balanceAction = app(GetSiteBalanceAsOf::class);
        $rows = $balanceAction->forSite($siteId);

        $labels = [];
        $data = [];
        $colors = [];
        $borders = [];

        foreach ($rows as $row) {
            $labels[] = $row->name;
            $data[] = round($row->balance, 2);

            if ($row->balance > 0) {
                $colors[] = '#10b981'; // Emerald
                $borders[] = '#059669';
            } else {
                $colors[] = 'rgba(203, 213, 225, 0.4)';
                $borders[] = 'rgba(148, 163, 184, 0.25)';
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => ($site?->code ?? 'Site').' — Current Stock Balance',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $borders,
                    'borderWidth' => 1,
                    'borderRadius' => 4,
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
            'indexAxis' => 'y',
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
                'y' => [
                    'ticks' => [
                        'font' => ['size' => 11, 'weight' => '500'],
                    ],
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }
}
