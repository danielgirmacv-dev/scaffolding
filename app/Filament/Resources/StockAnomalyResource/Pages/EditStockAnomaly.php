<?php

namespace App\Filament\Resources\StockAnomalyResource\Pages;

use App\Filament\Resources\StockAnomalyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockAnomaly extends EditRecord
{
    protected static string $resource = StockAnomalyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'resolved' && empty($this->record->resolved_by)) {
            $data['resolved_by'] = auth()->id();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
