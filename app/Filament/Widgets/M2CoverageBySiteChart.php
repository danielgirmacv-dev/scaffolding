<?php

namespace App\Filament\Widgets;

use App\Models\Material;
use App\Models\Site;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * Widget 5 — M² Coverage by Material and Site
 *
 * Shows total scaffolding area coverage (M²) for materials that are tracked
 * in square metres: CHS tubes, H-Frame, Pin Lock, and Props. Each material
 * is a separate dataset (grouped bars) and each site is on the X-axis.
 *
 * Only sites with at least one non-zero M² entry are shown to keep the chart
 * readable. A "Show all sites" filter toggle is available.
 */
class M2CoverageBySiteChart extends ChartWidget
{
    protected static ?string $heading = 'Scaffolding Area Coverage (M²) — By Site & Material';

    protected static ?string $description = 'Total M² coverage per site for materials tracked in square metres: CHS tubes, H-Frame, Pin Lock, and Props. Approved transactions only.';

    protected static ?int $sort = 50;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '420px';

    protected function getFilters(): ?array
    {
        return [
            'active_only' => 'Active sites with M² data only',
            'all' => 'All active project sites',
        ];
    }

    protected function getData(): array
    {
        $showAllSites = ($this->filter === 'all');

        // Identify M²-tracked materials by matching common name fragments.
        // This approach is resilient to minor naming differences in the database.
        $m2Materials = Material::query()
            ->where('is_active', true)
            ->where(function ($q): void {
                $q->where('name', 'like', '%CHS%')
                    ->orWhere('name', 'like', '%H-Frame%')
                    ->orWhere('name', 'like', '%H Frame%')
                    ->orWhere('name', 'like', '%Pin Lock%')
                    ->orWhere('name', 'like', '%Pinlock%')
                    ->orWhere('name', 'like', '%Prop%')
                    ->orWhere('unit_of_measure', 'm2')
                    ->orWhere('unit_of_measure', 'M2')
                    ->orWhere('unit_of_measure', 'm²');
            })
            ->orderBy('name')
            ->get();

        if ($m2Materials->isEmpty()) {
            return ['datasets' => [], 'labels' => []];
        }

        $materialIds = $m2Materials->pluck('id')->toArray();
        $placeholders = implode(',', array_fill(0, count($materialIds), '?'));

        // Aggregate m2_coverage per (site_id, material_id) from approved transactions
        $rows = DB::select("
            SELECT
                mt.site_id,
                mt.material_id,
                COALESCE(SUM(
                    CASE
                        WHEN mt.direction IN ('in', 'transfer_in', 'adjustment') THEN mt.m2_coverage
                        WHEN mt.direction IN ('out', 'transfer_out', 'damaged', 'lost') THEN -mt.m2_coverage
                        ELSE 0
                    END
                ), 0) AS total_m2
            FROM material_transactions mt
            WHERE mt.status = 'approved'
              AND mt.material_id IN ({$placeholders})
              AND mt.m2_coverage IS NOT NULL
              AND mt.m2_coverage > 0
            GROUP BY mt.site_id, mt.material_id
        ", $materialIds);

        // Build lookup: site_id → material_id → m2
        $lookup = [];
        $activeSiteIds = [];
        foreach ($rows as $row) {
            $lookup[$row->site_id][$row->material_id] = (float) $row->total_m2;
            if ((float) $row->total_m2 > 0) {
                $activeSiteIds[$row->site_id] = true;
            }
        }

        // Resolve sites to display
        $sitesQuery = Site::query()
            ->where('status', 'active')
            ->where('is_central_store', false)
            ->orderBy('name');

        if (! $showAllSites) {
            $sitesQuery->whereIn('id', array_keys($activeSiteIds));
        }

        $sites = $sitesQuery->get();

        if ($sites->isEmpty()) {
            return ['datasets' => [], 'labels' => []];
        }

        $labels = $sites->pluck('code')->toArray();

        $datasetColors = [
            ['#6366f1', '#4338ca'],
            ['#10b981', '#059669'],
            ['#f59e0b', '#d97706'],
            ['#06b6d4', '#0891b2'],
            ['#8b5cf6', '#6d28d9'],
            ['#ec4899', '#db2777'],
        ];

        $datasets = [];
        foreach ($m2Materials as $idx => $material) {
            $dataRow = [];
            $hasData = false;
            foreach ($sites as $site) {
                $val = round($lookup[$site->id][$material->id] ?? 0.0, 2);
                if ($val > 0) {
                    $hasData = true;
                }
                $dataRow[] = $val;
            }

            if (! $hasData && ! $showAllSites) {
                continue;
            }

            [$bg, $border] = $datasetColors[$idx % count($datasetColors)];

            $datasets[] = [
                'label' => $material->name,
                'data' => $dataRow,
                'backgroundColor' => $bg,
                'borderColor' => $border,
                'borderWidth' => 1,
                'borderRadius' => 4,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
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
                    'stacked' => false,
                    'ticks' => ['maxRotation' => 50, 'minRotation' => 20, 'font' => ['size' => 11, 'weight' => '500']],
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'stacked' => false,
                    'title' => [
                        'display' => true,
                        'text' => 'Area Coverage (M²)',
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
