<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Rental;
use App\Models\Site;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MonthlyRentalRevenue extends ChartWidget
{
    protected static ?string $heading = 'Monthly Rental Revenue — Billing Trend by Site';

    protected static ?string $description = 'Monthly ETB rental billing breakdown across active project sites. Stacked bars reflect the total company revenue per period.';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '380px';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        // Rolling 5-month window
        $months = collect(range(4, 0))->map(function (int $monthsAgo): string {
            return Carbon::now()->subMonths($monthsAgo)->format('Y-m');
        });

        $labels = $months->map(function (string $ym): string {
            return Carbon::createFromFormat('Y-m', $ym)->format('M Y');
        })->toArray();

        // Query rental records and invoices for the past 5 months
        $rentals = Rental::with('site')
            ->whereIn('billing_period', $months)
            ->get();

        $invoices = Invoice::with('rental.site')
            ->where(function ($query) use ($months): void {
                foreach ($months as $ym) {
                    $query->orWhere('period_start', 'like', "{$ym}%")
                        ->orWhere('created_at', 'like', "{$ym}%");
                }
            })
            ->get();

        // Identify sites with billing activity
        $activeSiteIds = $rentals->pluck('site_id')
            ->merge($invoices->pluck('rental.site_id')->filter())
            ->unique()
            ->filter();

        $activeSites = Site::where('is_central_store', false)
            ->whereIn('id', $activeSiteIds)
            ->orderBy('code')
            ->get();

        if ($activeSites->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => $labels,
            ];
        }

        $palette = [
            ['#6366f1', '#4338ca'], // Indigo
            ['#10b981', '#059669'], // Emerald
            ['#f59e0b', '#d97706'], // Amber
            ['#06b6d4', '#0891b2'], // Cyan
            ['#8b5cf6', '#6d28d9'], // Violet
            ['#ec4899', '#db2777'], // Pink
        ];

        $datasets = [];

        foreach ($activeSites as $index => $site) {
            $siteData = [];
            foreach ($months as $ym) {
                $siteRental = (float) $rentals->where('site_id', $site->id)
                    ->where('billing_period', $ym)
                    ->sum('grand_total_cost');

                $siteInvoice = (float) $invoices->filter(function (Invoice $inv) use ($site, $ym): bool {
                    return $inv->rental?->site_id === $site->id
                        && Carbon::parse($inv->period_start ?? $inv->created_at)->format('Y-m') === $ym;
                })->sum('total_amount');

                $siteMonthTotal = $siteRental > 0 ? $siteRental : $siteInvoice;
                $siteData[] = round($siteMonthTotal, 2);
            }

            [$bg, $border] = $palette[$index % count($palette)];

            $datasets[] = [
                'label' => "[{$site->code}] {$site->name}",
                'data' => $siteData,
                'backgroundColor' => $bg,
                'borderColor' => $border,
                'borderWidth' => 1,
                'borderRadius' => 5,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
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
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(15, 23, 42, 0.92)',
                    'titleFont' => ['size' => 13, 'weight' => 'bold'],
                    'bodyFont' => ['size' => 12],
                    'padding' => 10,
                    'cornerRadius' => 8,
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'grid' => ['display' => false],
                    'ticks' => ['font' => ['size' => 12, 'weight' => '600']],
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Total Monthly Rental Revenue (ETB)',
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
