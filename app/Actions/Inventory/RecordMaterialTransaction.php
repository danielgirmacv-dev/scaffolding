<?php

namespace App\Actions\Inventory;

use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Site;
use App\Models\StockAnomaly;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecordMaterialTransaction
{
    public function __construct(
        protected GetSiteBalanceAsOf $balanceAction
    ) {}

    /**
     * Record a new material transaction or inter-site transfer atomically.
     *
     * @param array{
     *     site_id: int,
     *     material_id: int,
     *     direction: string,
     *     quantity: float|int|string,
     *     m2_coverage?: float|int|string|null,
     *     maintenance_status?: string|null,
     *     production_stage?: string|null,
     *     from_site_id?: int|null,
     *     to_site_id?: int|null,
     *     ref_no?: string|null,
     *     transaction_date: string,
     *     notes?: string|null,
     *     status?: string,
     *     created_by?: int|null,
     *     approved_by?: int|null,
     *     ip_address?: string|null,
     *     user_agent?: string|null,
     *     source_sheet?: string|null,
     *     source_row?: int|null,
     *     allow_negative_as_anomaly?: bool
     * } $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): MaterialTransaction
    {
        $siteId = (int) $data['site_id'];
        $materialId = (int) $data['material_id'];
        $quantity = abs((float) $data['quantity']);
        $m2Coverage = isset($data['m2_coverage']) && $data['m2_coverage'] !== null && $data['m2_coverage'] !== ''
            ? abs((float) $data['m2_coverage'])
            : null;
        $direction = strtolower((string) $data['direction']);
        $maintenanceStatus = $data['maintenance_status'] ?? ($direction === 'damaged' ? 'in_maintenance' : 'none');
        $productionStage = $data['production_stage'] ?? 'none';
        $transactionDate = Carbon::parse($data['transaction_date'])->toDateString();
        $allowAnomaly = (bool) ($data['allow_negative_as_anomaly'] ?? false);

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Transaction quantity must be greater than zero.',
            ]);
        }

        $material = Material::findOrFail($materialId);
        $site = Site::findOrFail($siteId);

        return DB::transaction(function () use ($data, $site, $material, $quantity, $m2Coverage, $direction, $maintenanceStatus, $productionStage, $transactionDate, $allowAnomaly) {
            // Check for negative balance constraint on deductions
            $isDeduction = in_array($direction, ['out', 'transfer_out', 'damaged', 'lost'], true);
            $currentBalance = $this->balanceAction->execute($site->id, $material->id, $transactionDate);
            $projectedBalance = $currentBalance - $quantity;

            if ($isDeduction && $projectedBalance < 0) {
                $deficit = abs($projectedBalance);
                $message = "Cannot record {$direction} of {$quantity} {$material->unit_of_measure} for '{$material->name}' at {$site->name} on {$transactionDate}. Available balance is {$currentBalance} {$material->unit_of_measure} (Deficit: {$deficit} {$material->unit_of_measure}).";

                if (! $allowAnomaly) {
                    throw ValidationException::withMessages([
                        'quantity' => $message,
                    ]);
                }

                // If importing legacy data, allow record creation but surface as an audited StockAnomaly
                $anomaly = StockAnomaly::create([
                    'source_sheet' => $data['source_sheet'] ?? $site->code,
                    'source_row' => $data['source_row'] ?? null,
                    'site_id' => $site->id,
                    'material_id' => $material->id,
                    'calculated_negative_balance' => $projectedBalance,
                    'error_type' => 'NEGATIVE_RUNNING_BALANCE',
                    'message' => $message,
                    'raw_payload' => $data,
                    'status' => 'open',
                ]);
            }

            $txNo = 'TXN-'.date('Ymd').'-'.strtoupper(Str::random(6));

            $transaction = MaterialTransaction::create([
                'transaction_no' => $txNo,
                'site_id' => $site->id,
                'material_id' => $material->id,
                'direction' => $direction,
                'quantity' => $quantity,
                'm2_coverage' => $m2Coverage,
                'maintenance_status' => $maintenanceStatus,
                'production_stage' => $productionStage,
                'from_site_id' => $data['from_site_id'] ?? ($direction === 'transfer_in' ? ($data['from_site_id'] ?? null) : null),
                'to_site_id' => $data['to_site_id'] ?? ($direction === 'transfer_out' ? ($data['to_site_id'] ?? null) : null),
                'ref_no' => $data['ref_no'] ?? null,
                'transaction_date' => $transactionDate,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'approved',
                'created_by' => $data['created_by'] ?? null,
                'approved_by' => $data['approved_by'] ?? null,
                'approved_at' => ($data['status'] ?? 'approved') === 'approved' ? now() : null,
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'user_agent' => $data['user_agent'] ?? request()->userAgent(),
                'source_sheet' => $data['source_sheet'] ?? null,
                'source_row' => $data['source_row'] ?? null,
            ]);

            if (isset($anomaly)) {
                $anomaly->update(['material_transaction_id' => $transaction->id]);
            }

            // Handle paired transfer_in creation if this is an inter-site transfer
            if (in_array($direction, ['transfer_out', 'out'], true) && ! empty($data['to_site_id'])) {
                $destSiteId = (int) $data['to_site_id'];
                $pairedTxNo = 'TXN-'.date('Ymd').'-'.strtoupper(Str::random(6));

                $pairedTransaction = MaterialTransaction::create([
                    'transaction_no' => $pairedTxNo,
                    'site_id' => $destSiteId,
                    'material_id' => $material->id,
                    'direction' => 'transfer_in',
                    'quantity' => $quantity,
                    'm2_coverage' => $m2Coverage,
                    'maintenance_status' => 'none',
                    'production_stage' => 'none',
                    'from_site_id' => $site->id,
                    'to_site_id' => $destSiteId,
                    'linked_transaction_id' => $transaction->id,
                    'ref_no' => $data['ref_no'] ?? null,
                    'transaction_date' => $transactionDate,
                    'notes' => 'Automatic paired transfer from '.$site->name.(! empty($data['notes']) ? ': '.$data['notes'] : ''),
                    'status' => $data['status'] ?? 'approved',
                    'created_by' => $data['created_by'] ?? null,
                    'approved_by' => $data['approved_by'] ?? null,
                    'approved_at' => ($data['status'] ?? 'approved') === 'approved' ? now() : null,
                    'ip_address' => $data['ip_address'] ?? request()->ip(),
                    'user_agent' => $data['user_agent'] ?? request()->userAgent(),
                    'source_sheet' => $data['source_sheet'] ?? null,
                    'source_row' => $data['source_row'] ?? null,
                ]);

                $transaction->update(['linked_transaction_id' => $pairedTransaction->id]);
            }

            return $transaction;
        });
    }
}
