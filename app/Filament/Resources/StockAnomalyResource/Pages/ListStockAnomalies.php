<?php

namespace App\Filament\Resources\StockAnomalyResource\Pages;

use App\Filament\Resources\StockAnomalyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockAnomalies extends ListRecords
{
    protected static string $resource = StockAnomalyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
