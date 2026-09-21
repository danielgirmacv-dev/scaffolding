<?php

namespace App\Filament\Resources\StockAnomalyResource\Pages;

use App\Filament\Resources\StockAnomalyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStockAnomaly extends ViewRecord
{
    protected static string $resource = StockAnomalyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
