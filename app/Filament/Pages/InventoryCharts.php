<?php

namespace App\Filament\Pages;

use App\Support\FilamentRoleAccess;
use Filament\Pages\Page;

class InventoryCharts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Inventory Charts';

    protected static ?string $title = 'Inventory Visual Dashboard';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.inventory-charts';

    public static function canAccess(): bool
    {
        return FilamentRoleAccess::canAccessPanel();
    }
}
