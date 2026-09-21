<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use App\Models\Site;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSite extends EditRecord
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, Site $record): void {
                    if ($record->is_central_store) {
                        Notification::make()
                            ->danger()
                            ->title('Cannot Delete Central Store')
                            ->body('The Central Store is the core inventory hub and cannot be deleted.')
                            ->persistent()
                            ->send();

                        $action->halt();
                    }

                    if ($record->transactions()->exists() || $record->rentals()->exists() || $record->users()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('Cannot Delete Site')
                            ->body("Site '{$record->name}' has existing transactions, rentals, or assigned personnel. Consider changing its status instead.")
                            ->persistent()
                            ->send();

                        $action->halt();
                    }
                }),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make()
                ->before(function (Actions\ForceDeleteAction $action, Site $record): void {
                    if ($record->transactions()->exists() || $record->rentals()->exists() || $record->users()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('Cannot Permanently Delete Site')
                            ->body("Site '{$record->name}' has existing transaction history and cannot be deleted.")
                            ->persistent()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
