<?php

namespace App\Filament\Resources\MaterialTransactionResource\Pages;

use App\Filament\Resources\MaterialTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaterialTransactions extends ListRecords
{
    protected static string $resource = MaterialTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
