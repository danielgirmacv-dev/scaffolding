<?php

namespace App\Filament\Resources\MaterialResource\Pages;

use App\Filament\Resources\MaterialResource;
use App\Models\Material;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMaterial extends EditRecord
{
    protected static string $resource = MaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, Material $record): void {
                    if ($record->transactions()->exists() || $record->rentals()->exists() || $record->rentalItems()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('Cannot Delete Material')
                            ->body("Material '{$record->name}' has existing ledger transactions or rental records and cannot be deleted. Consider deactivating it instead.")
                            ->persistent()
                            ->send();

                        $action->halt();
                    }
                }),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make()
                ->before(function (Actions\ForceDeleteAction $action, Material $record): void {
                    if ($record->transactions()->exists() || $record->rentals()->exists() || $record->rentalItems()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('Cannot Permanently Delete Material')
                            ->body("Material '{$record->name}' has existing ledger transactions or rental records and cannot be deleted.")
                            ->persistent()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
