<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use App\Services\SiteContext;
use App\Support\FilamentRoleAccess;
use Filament\Resources\Pages\ListRecords;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = FilamentRoleAccess::user();

        if ($user?->isSiteEngineer() && $user->site_id !== null) {
            SiteContext::setActiveSiteId($user->site_id);
        }
    }

    public function getTitle(): string
    {
        $site = SiteContext::getActiveSite();

        return $site
            ? "Site Inventory Balance: [{$site->code}] {$site->name}"
            : 'Site Inventory Balances';
    }
}
