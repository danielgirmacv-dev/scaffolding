<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActiveSitesOverview;
use App\Filament\Widgets\CentralStoreUtilizationRate;
use App\Filament\Widgets\MonthlyRentalRevenue;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'EEIG Scaffolding & Formwork Dashboard';

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }

    public function getWidgets(): array
    {
        return [
            ActiveSitesOverview::class,
            CentralStoreUtilizationRate::class,
            MonthlyRentalRevenue::class,
        ];
    }
}
