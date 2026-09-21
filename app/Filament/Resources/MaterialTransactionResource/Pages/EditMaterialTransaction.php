<?php

namespace App\Filament\Resources\MaterialTransactionResource\Pages;

use App\Filament\Resources\MaterialTransactionResource;
use App\Models\MaterialTransaction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMaterialTransaction extends EditRecord
{
    protected static string $resource = MaterialTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        if ($record instanceof MaterialTransaction && $record->linked_transaction_id) {
            $paired = MaterialTransaction::find($record->linked_transaction_id);
            if ($paired) {
                $paired->update([
                    'material_id' => $record->material_id,
                    'quantity' => $record->quantity,
                    'm2_coverage' => $record->m2_coverage,
                    'transaction_date' => $record->transaction_date,
                    'ref_no' => $record->ref_no,
                    'from_site_id' => $record->site_id,
                    'to_site_id' => $record->to_site_id,
                ]);
            }
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
