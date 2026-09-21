<?php

namespace App\Services\Reports;

use App\Actions\Inventory\GetSiteBalanceAsOf;
use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Rental;
use App\Models\Site;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConsolidationReportService
{
    public function __construct(
        protected GetSiteBalanceAsOf $balanceAction
    ) {}

    /**
     * Replaces "Company Balance" sheet: Cross-tab matrix of stock across all sites.
     *
     * @return array{sites: Collection<int, Site>, materials: Collection<int, Material>, matrix: array<int, array<int, float>>, totals: array<int, float>}
     */
    public function getCompanyBalanceReport(?string $asOfDate = null): array
    {
        return $this->balanceAction->companyWideMatrix($asOfDate);
    }

    /**
     * Replaces "COST summary" sheet: High-level rollup per site for a billing period.
     *
     * @param  string  $billingPeriod  Format YYYY-MM
     * @return Collection<int, object{site_id: int, site_code: string, site_name: string, client: string|null, total_items_rented: int, subtotal_cost: float, vat_amount: float, grand_total_cost: float}>
     */
    public function getCostSummaryReport(string $billingPeriod): Collection
    {
        $rows = DB::select("
            SELECT 
                s.id as site_id,
                s.code as site_code,
                s.name as site_name,
                s.client,
                COUNT(r.id) as total_items_rented,
                COALESCE(SUM(r.subtotal_cost), 0) as subtotal_cost,
                COALESCE(SUM(r.vat_amount), 0) as vat_amount,
                COALESCE(SUM(r.grand_total_cost), 0) as grand_total_cost
            FROM sites s
            LEFT JOIN rentals r ON r.site_id = s.id AND r.billing_period = ?
            WHERE s.is_central_store = 0 
              AND s.code NOT IN ('CENTRAL STORE', 'CS', 'C/STORE', 'SCAFF', 'SCAFF-CHAKA', 'CHAKA', 'PRODUCTION')
              AND s.deleted_at IS NULL
            GROUP BY s.id, s.code, s.name, s.client
            ORDER BY s.name ASC
        ", [$billingPeriod]);

        return collect($rows)->map(function ($row) {
            $row->subtotal_cost = (float) $row->subtotal_cost;
            $row->vat_amount = (float) $row->vat_amount;
            $row->grand_total_cost = (float) $row->grand_total_cost;

            return $row;
        });
    }

    /**
     * Replaces "RENTAL COST AN." sheet: Itemized cost analysis per material per site.
     *
     * @return Collection<int, Rental>
     */
    public function getRentalCostAnalysisReport(string $billingPeriod, ?int $siteId = null): Collection
    {
        $query = Rental::with(['site', 'material'])
            ->where('billing_period', $billingPeriod);

        if ($siteId !== null) {
            $query->where('site_id', $siteId);
        }

        return $query->orderBy('site_id')->orderBy('material_id')->get();
    }

    /**
     * Inter-site transfer audit trail with full user and device accountability.
     *
     * @return Collection<int, MaterialTransaction>
     */
    public function getTransferAuditTrail(
        ?int $fromSiteId = null,
        ?int $toSiteId = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection {
        $query = MaterialTransaction::with(['material', 'fromSite', 'toSite', 'creator', 'approver'])
            ->whereIn('direction', ['transfer_out', 'transfer_in']);

        if ($fromSiteId !== null) {
            $query->where('from_site_id', $fromSiteId);
        }

        if ($toSiteId !== null) {
            $query->where('to_site_id', $toSiteId);
        }

        if ($startDate !== null) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where('transaction_date', '<=', $endDate);
        }

        return $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->get();
    }
}
