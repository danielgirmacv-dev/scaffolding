<?php

namespace App\Actions\Inventory;

use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Site;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetSiteBalanceAsOf
{
    /**
     * Compute running stock balance for a single site and material up to a specific date.
     *
     * @param  string|null  $asOfDate  Format YYYY-MM-DD (defaults to today)
     */
    public function execute(int $siteId, int $materialId, ?string $asOfDate = null): float
    {
        $query = MaterialTransaction::query()
            ->where('site_id', $siteId)
            ->where('material_id', $materialId)
            ->where('status', 'approved');

        if ($asOfDate !== null) {
            $query->where('transaction_date', '<=', $asOfDate);
        }

        $result = $query->selectRaw("
            SUM(
                CASE 
                    WHEN direction IN ('in', 'transfer_in', 'adjustment') 
                         AND COALESCE(production_stage, 'none') != 'on_process' 
                         AND COALESCE(maintenance_status, 'none') != 'in_maintenance'
                         THEN quantity
                    WHEN direction IN ('out', 'transfer_out', 'damaged', 'lost') THEN -quantity
                    ELSE 0
                END
            ) as balance
        ")->value('balance');

        return (float) ($result ?? 0.0);
    }

    /**
     * Get stock balances for all materials at a specific site.
     *
     * @return Collection<int, object{material_id: int, item_code: string|null, name: string, unit_of_measure: string, category: string, balance: float}>
     */
    public function forSite(int $siteId, ?string $asOfDate = null): Collection
    {
        $bindings = [$siteId];
        $dateCondition = '';

        if ($asOfDate !== null) {
            $dateCondition = 'AND mt.transaction_date <= ?';
            $bindings[] = $asOfDate;
        }

        $rows = DB::select("
            SELECT 
                m.id as material_id,
                m.item_code,
                m.name,
                m.unit_of_measure,
                m.category,
                COALESCE(SUM(
                    CASE 
                        WHEN mt.direction IN ('in', 'transfer_in', 'adjustment') 
                             AND COALESCE(mt.production_stage, 'none') != 'on_process' 
                             AND COALESCE(mt.maintenance_status, 'none') != 'in_maintenance'
                             THEN mt.quantity
                        WHEN mt.direction IN ('out', 'transfer_out', 'damaged', 'lost') THEN -mt.quantity
                        ELSE 0
                    END
                ), 0) as balance
            FROM materials m
            LEFT JOIN material_transactions mt ON mt.material_id = m.id 
                AND mt.site_id = ? 
                AND mt.status = 'approved'
                {$dateCondition}
            WHERE m.deleted_at IS NULL
            GROUP BY m.id, m.item_code, m.name, m.unit_of_measure, m.category
            ORDER BY m.name ASC
        ", $bindings);

        return collect($rows)->map(function ($row) {
            $row->balance = (float) $row->balance;

            return $row;
        });
    }

    /**
     * Generate the complete company-wide material balance matrix across all active sites.
     * Replaces the legacy "Company Balance" Excel sheet.
     *
     * @return array{sites: Collection, materials: Collection, matrix: array<int, array<int, float>>, totals: array<int, float>}
     */
    public function companyWideMatrix(?string $asOfDate = null): array
    {
        $sites = Site::query()->orderBy('is_central_store', 'desc')->orderBy('name', 'asc')->get();
        $materials = Material::query()->orderBy('category', 'asc')->orderBy('name', 'asc')->get();

        $dateCondition = '';
        $bindings = [];

        if ($asOfDate !== null) {
            $dateCondition = 'AND transaction_date <= ?';
            $bindings[] = $asOfDate;
        }

        $balances = DB::select("
            SELECT 
                site_id,
                material_id,
                COALESCE(SUM(
                    CASE 
                        WHEN direction IN ('in', 'transfer_in', 'adjustment') 
                             AND COALESCE(production_stage, 'none') != 'on_process' 
                             AND COALESCE(maintenance_status, 'none') != 'in_maintenance'
                             THEN quantity
                        WHEN direction IN ('out', 'transfer_out', 'damaged', 'lost') THEN -quantity
                        ELSE 0
                    END
                ), 0) as balance
            FROM material_transactions
            WHERE status = 'approved' {$dateCondition}
            GROUP BY site_id, material_id
        ", $bindings);

        $matrix = [];
        $materialTotals = [];

        foreach ($balances as $b) {
            $matrix[$b->material_id][$b->site_id] = (float) $b->balance;
            $materialTotals[$b->material_id] = ($materialTotals[$b->material_id] ?? 0) + (float) $b->balance;
        }

        return [
            'sites' => $sites,
            'materials' => $materials,
            'matrix' => $matrix,
            'totals' => $materialTotals,
        ];
    }
}
