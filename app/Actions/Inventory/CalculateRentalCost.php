<?php

namespace App\Actions\Inventory;

use App\Models\Material;
use App\Models\Rental;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CalculateRentalCost
{
    public function __construct(
        protected GetSiteBalanceAsOf $balanceAction
    ) {}

    /**
     * Compute and record rental cost for a specific material and site over a date range.
     *
     * @param  string  $startDate  Format YYYY-MM-DD
     * @param  string  $endDate  Format YYYY-MM-DD
     */
    public function execute(
        int $siteId,
        int $materialId,
        string $startDate,
        string $endDate,
        ?float $customQuantity = null,
        float $vatPercent = 15.00
    ): Rental {
        $site = Site::findOrFail($siteId);
        $material = Material::findOrFail($materialId);

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysUsed = max(1, $start->diffInDays($end) + 1);
        $billingPeriod = $start->format('Y-m');

        // Quantity on rent: either provided or balance on site as of end date
        $quantity = $customQuantity !== null
            ? $customQuantity
            : max(0.0, $this->balanceAction->execute($site->id, $material->id, $end->toDateString()));

        // Snapshot current rates
        $marketRate = (float) $material->market_rate_per_day;
        $discountPercent = (float) $material->eeig_discount_percent;
        $depreciationRate = (float) $material->depreciation_rate_per_day;

        $discountFactor = 1 - ($discountPercent / 100);
        $effectiveDailyRate = max(0.0, round(($marketRate * $discountFactor) - $depreciationRate, 4));

        $subtotal = round($quantity * $effectiveDailyRate * $daysUsed, 2);
        $vatAmount = round($subtotal * ($vatPercent / 100), 2);
        $grandTotal = $subtotal + $vatAmount;

        $rentalNo = 'RNT-'.$start->format('Ym').'-'.strtoupper(Str::random(6));

        return Rental::updateOrCreate(
            [
                'site_id' => $site->id,
                'material_id' => $material->id,
                'billing_period' => $billingPeriod,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
            [
                'rental_no' => $rentalNo,
                'days_used' => $daysUsed,
                'quantity_on_rent' => $quantity,
                'market_rate_snapshot' => $marketRate,
                'discount_percent_snapshot' => $discountPercent,
                'depreciation_rate_snapshot' => $depreciationRate,
                'effective_daily_rate' => $effectiveDailyRate,
                'subtotal_cost' => $subtotal,
                'vat_percent' => $vatPercent,
                'vat_amount' => $vatAmount,
                'grand_total_cost' => $grandTotal,
                'status' => 'approved',
                'created_by' => auth()->id(),
            ]
        );
    }

    /**
     * Compute rental costs for all active project sites for a given month.
     *
     * @param  string  $billingPeriod  Format YYYY-MM
     * @return Collection<int, Rental>
     */
    public function computeForPeriod(string $billingPeriod, float $vatPercent = 15.00): Collection
    {
        $startDate = Carbon::createFromFormat('Y-m', $billingPeriod)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('Y-m', $billingPeriod)->endOfMonth()->toDateString();

        $projectSites = Site::projectSites()->active()->get();
        $materials = Material::active()->get();
        $results = collect();

        DB::transaction(function () use ($projectSites, $materials, $startDate, $endDate, $vatPercent, &$results) {
            foreach ($projectSites as $site) {
                foreach ($materials as $material) {
                    $balance = $this->balanceAction->execute($site->id, $material->id, $endDate);

                    if ($balance > 0) {
                        $rental = $this->execute(
                            siteId: $site->id,
                            materialId: $material->id,
                            startDate: $startDate,
                            endDate: $endDate,
                            customQuantity: $balance,
                            vatPercent: $vatPercent
                        );
                        $results->push($rental);
                    }
                }
            }
        });

        return $results;
    }
}
