<?php

namespace App\Filament\Resources\MaterialTransactionResource\Pages;

use App\Actions\Inventory\RecordMaterialTransaction;
use App\Filament\Resources\MaterialTransactionResource;
use App\Support\FilamentRoleAccess;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMaterialTransaction extends CreateRecord
{
    protected static string $resource = MaterialTransactionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = FilamentRoleAccess::user();
        $status = FilamentRoleAccess::defaultTransactionStatus();

        return app(RecordMaterialTransaction::class)->execute([
            'site_id' => (int) $data['site_id'],
            'material_id' => (int) $data['material_id'],
            'direction' => $data['direction'],
            'quantity' => $data['quantity'],
            'm2_coverage' => $data['m2_coverage'] ?? null,
            'maintenance_status' => $data['maintenance_status'] ?? null,
            'production_stage' => $data['production_stage'] ?? null,
            'from_site_id' => $data['from_site_id'] ?? null,
            'to_site_id' => $data['to_site_id'] ?? null,
            'ref_no' => $data['ref_no'] ?? null,
            'transaction_date' => $data['transaction_date'],
            'notes' => $data['notes'] ?? null,
            'status' => $status,
            'created_by' => $user?->id,
            'approved_by' => $status === 'approved' ? $user?->id : null,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
