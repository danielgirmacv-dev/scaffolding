<?php

namespace App\Http\Controllers;

use App\Actions\Inventory\GetSiteBalanceAsOf;
use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Rental;
use App\Models\Site;
use App\Models\StockAnomaly;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(GetSiteBalanceAsOf $balanceAction)
    {
        $totalSites = Site::projectSites()->count();
        $centralStore = Site::centralStore()->first();
        $totalMaterials = Material::active()->count();
        $totalTransactions = MaterialTransaction::where('status', 'approved')->count();
        $openAnomalies = StockAnomaly::where('status', 'open')->count();
        $currentPeriod = now()->format('Y-m');

        $monthlyRentalTotal = Rental::where('billing_period', $currentPeriod)->sum('grand_total_cost');
        $lastMonthRentalTotal = Rental::where('billing_period', now()->subMonth()->format('Y-m'))->sum('grand_total_cost');

        $recentTransactions = MaterialTransaction::with(['site', 'material', 'creator', 'fromSite', 'toSite'])
            ->where('status', 'approved')
            ->latest()
            ->take(8)
            ->get();

        $projectSites = Site::projectSites()->active()->get();

        // Aggregate material counts and rental totals in two queries instead of 2N
        $materialCountsBySite = DB::table('material_transactions')
            ->where('status', 'approved')
            ->whereIn('site_id', $projectSites->pluck('id'))
            ->groupBy('site_id')
            ->select('site_id', DB::raw('COUNT(DISTINCT material_id) as total_items'))
            ->pluck('total_items', 'site_id');

        $rentalCostsBySite = Rental::where('billing_period', $currentPeriod)
            ->whereIn('site_id', $projectSites->pluck('id'))
            ->groupBy('site_id')
            ->selectRaw('site_id, SUM(grand_total_cost) as rental_cost')
            ->pluck('rental_cost', 'site_id');

        $siteSummaries = $projectSites->map(function (Site $site) use ($materialCountsBySite, $rentalCostsBySite) {
            return [
                'site' => $site,
                'total_items' => (int) ($materialCountsBySite[$site->id] ?? 0),
                'rental_cost' => (float) ($rentalCostsBySite[$site->id] ?? 0),
            ];
        });

        $topMaterials = DB::table('material_transactions as mt')
            ->join('materials as m', 'm.id', '=', 'mt.material_id')
            ->where('mt.status', 'approved')
            ->select('m.id', 'm.name', 'm.item_code', 'm.unit_of_measure',
                DB::raw("SUM(CASE WHEN mt.direction IN ('in','transfer_in') THEN mt.quantity ELSE 0 END) as total_in"),
                DB::raw("SUM(CASE WHEN mt.direction IN ('out','transfer_out','damaged','lost') THEN mt.quantity ELSE 0 END) as total_out")
            )
            ->groupBy('m.id', 'm.name', 'm.item_code', 'm.unit_of_measure')
            ->orderByDesc('total_in')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalSites', 'centralStore', 'totalMaterials', 'totalTransactions',
            'openAnomalies', 'monthlyRentalTotal', 'lastMonthRentalTotal',
            'recentTransactions', 'siteSummaries', 'topMaterials', 'currentPeriod'
        ));
    }
}
