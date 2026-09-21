<?php

namespace App\Filament\Widgets;

use App\Models\Rental;
use App\Models\Site;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * Widget 4 — Monthly Rental Revenue by Site
 *
 * Shows the TOTAL MONTHLY RENT AMOUNT per project site for a selected billing
 * period. Bars are sorted descending by revenue so the highest-earning sites
 * appear first. Filter defaults to current month (YYYY-MM).
 */
class RentalRevenueBySiteChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Rental Revenue — By Site';

    protected static ?string $description = 'ETB rental billing per project site for the selected month. Sorted highest to lowest.';

    protected static ?int $sort = 40;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '420px';

    protected function getFilters(): ?array
    {
        // Provide a rolling 12-month window of selectable billing periods
        $options = [];
        for ($i = 11; $i >= 0; $i--) {
            $period = Carbon::now()->subMonths($i)->format('Y-m');
            $label = Carbon::now()->subMonths($i)->format('F Y');
            $options[$period] = $label;
        }

        return $options;
    }

    protected function getData(): array
    {
        $filters = $this->getFilters() ?? [];

        $billingPeriod = ($this->filter !== null && isset($filters[$this->filter]))
            ? $this->filter
            : Carbon::now()->format('Y-m');

        // Sum grand_total_cost per site for the selected billing period
        $rows = DB::select("
            SELECT
                s.id      AS site_id,
                s.code    AS site_code,
                s.name    AS site_name,
                COALESCE(SUM(r.grand_total_cost), 0) AS total_revenue
            FROM sites s
            LEFT JOIN rentals r
                ON r.site_id = s.id
               AND r.billing_period = ?
            WHERE s.deleted_at IS NULL
              AND s.is_central_store = 0
              AND s.status = 'active'
            GROUP BY s.id, s.code, s.name
            HAVING total_revenue > 0
            ORDER BY total_revenue DESC
        ", [$billingPeriod]);

        if (empty($rows)) {
            return ['datasets' => [], 'labels' => []];
        }

        $labels = [];
        $data = [];
        $colors = [];
        $borders = [];

        $maxRevenue = max(array_column($rows, 'total_revenue'));

        foreach ($rows as $row) {
            $revenue = (float) $row->total_revenue;
            $labels[] = $row->site_code;
            $data[] = round($revenue, 2);

            $colors[] = '#f59e0b'; // Amber
            $borders[] = '#d97706';
        }

        $periodLabel = Carbon::createFromFormat('Y-m', $billingPeriod)->format('F Y');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => "Monthly Rental Revenue — {$periodLabel} (ETB)",
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $borders,
                    'borderWidth' => 1,
                    'borderRadius' => 6,
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
                    'callbacks' => [
                        'label' => "function(ctx){ return 'ETB ' + ctx.parsed.y.toLocaleString('en-US', {minimumFractionDigits:2}); }",
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => ['font' => ['size' => 11, 'weight' => '500']],
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (ETB)',
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
