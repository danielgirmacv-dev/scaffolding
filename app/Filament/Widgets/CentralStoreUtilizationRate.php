<?php

namespace App\Filament\Widgets;

use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Site;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class CentralStoreUtilizationRate extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $centralStore = Site::where('is_central_store', true)->first();

        if (! $centralStore) {
            return [
                Stat::make('Central Store Utilization', 'N/A')
                    ->description('No central store configured')
                    ->color('gray'),
            ];
        }

        // Total received into central store
        $totalReceived = (float) MaterialTransaction::where('site_id', $centralStore->id)
            ->whereIn('direction', ['in', 'adjustment'])
            ->sum('quantity');

        // Total dispatched to projects from central store
        $totalDispatched = (float) MaterialTransaction::where('site_id', $centralStore->id)
            ->whereIn('direction', ['out', 'transfer_out'])
            ->sum('quantity');

        $currentYardStock = max(0.0, $totalReceived - $totalDispatched);

        $utilizationPercent = $totalReceived > 0
            ? round(($totalDispatched / $totalReceived) * 100, 1)
            : 0.0;

        $totalMaterials = Material::where('is_active', true)->count();
        $circulatingMaterials = Material::whereHas('transactions', fn ($q) => $q->where('status', 'approved')->where('direction', 'transfer_in'))->count();

        $progressHtml = new HtmlString('
            <div class="mt-2 w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: '.min(100, $utilizationPercent).'%"></div>
            </div>
            <div class="mt-1 text-xs text-gray-500 font-medium">'.number_format($totalDispatched).' units mobilized / '.number_format($totalReceived).' total</div>
        ');

        return [
            Stat::make(
                'Central Depot Utilization',
                "{$utilizationPercent}%"
            )
                ->description($progressHtml)
                ->chart([2, 4, 6, 8.5, $utilizationPercent])
                ->color('primary')
                ->extraAttributes(['class' => 'stat-card-purple']),

            Stat::make('Dispatched to Project Sites', number_format($totalDispatched).' units')
                ->description('Working across active project sites')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([500, 1000, 1500, 1850, (int) $totalDispatched])
                ->color('info')
                ->extraAttributes(['class' => 'stat-card-blue']),

            Stat::make('Available in Central Depot', number_format($currentYardStock).' units')
                ->description('Available for site mobilization')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->chart([20000, 19500, 19000, 18400, (int) $currentYardStock])
                ->color('success')
                ->extraAttributes(['class' => 'stat-card-teal']),

            Stat::make('Catalog in Circulation', "{$circulatingMaterials} / {$totalMaterials} Types")
                ->description('Materials actively deployed on sites')
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->chart([2, 4, 6, 8, $circulatingMaterials])
                ->color('warning')
                ->extraAttributes(['class' => 'stat-card-rose']),
        ];
    }
}
