<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Support\FilamentRoleAccess;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => FilamentRoleAccess::isAdmin()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->record->fill($data);
        $this->record->calculateAmounts();

        return $this->record->toArray();
    }
}
