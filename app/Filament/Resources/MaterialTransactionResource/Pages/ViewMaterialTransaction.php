<?php

namespace App\Filament\Resources\MaterialTransactionResource\Pages;

use App\Filament\Resources\MaterialTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMaterialTransaction extends ViewRecord
{
    protected static string $resource = MaterialTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
