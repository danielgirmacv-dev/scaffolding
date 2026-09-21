<?php

namespace App\Filament\Widgets;

use App\Models\Material;
use App\Models\Rental;
use App\Models\Site;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActiveSitesOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeSitesCount = Site::where('status', 'active')->where('is_central_store', false)->count();
        $centralStore = Site::where('is_central_store', true)->first();
        $activeRentals = Rental::whereIn('status', ['active', 'approved'])->count();

        // Calculate current and previous month rental revenue
        $currentPeriod = Carbon::now()->format('Y-m');
        $lastPeriod = Carbon::now()->subMonth()->format('Y-m');

        $currentRentalTotal = (float) Rental::where('billing_period', $currentPeriod)->sum('grand_total_cost');
        $lastRentalTotal = (float) Rental::where('billing_period', $lastPeriod)->sum('grand_total_cost');

        $rentalDelta = $lastRentalTotal > 0
            ? (($currentRentalTotal - $lastRentalTotal) / $lastRentalTotal) * 100
            : 0;

        $totalMaterials = Material::count();

        return [
            Stat::make('Active Project Sites', "{$activeSitesCount} Sites")
                ->description($centralStore ? "35 active sites (+ Depot: {$centralStore->name})" : 'All construction sites under EEIG')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->chart([30, 32, 33, 34, 35])
                ->color('primary')
                ->extraAttributes(['class' => 'stat-card-indigo']),

            Stat::make('Active Rental Agreements', "{$activeRentals} Contracts")
                ->description('Active equipment deployments on site')
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->chart([4, 6, 8, 9, 10])
                ->color('info')
                ->extraAttributes(['class' => 'stat-card-cyan']),

            Stat::make('Monthly Rental Revenue', 'ETB '.number_format($currentRentalTotal, 2))
                ->description(
                    $lastRentalTotal > 0
                        ? ($rentalDelta >= 0 ? '+'.number_format($rentalDelta, 1).'% vs last month' : number_format($rentalDelta, 1).'% vs last month (ETB '.number_format($lastRentalTotal, 0).')')
                        : 'Current billing period ('.Carbon::now()->format('M Y').')'
                )
                ->descriptionIcon($rentalDelta >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([190, 202, 210, 217.5, 210.5])
                ->color($rentalDelta >= 0 ? 'success' : 'warning')
                ->extraAttributes(['class' => 'stat-card-amber']),

            Stat::make('Catalog Materials', "{$totalMaterials} Items")
                ->description('Standardized scaffolding components')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->chart([25, 30, 34, 38, $totalMaterials])
                ->color('success')
                ->extraAttributes(['class' => 'stat-card-emerald']),
        ];
    }
}
