<?php

namespace App\Http\Controllers;

use App\Actions\Inventory\GetSiteBalanceAsOf;
use App\Models\Site;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::withTrashed(false)->withCount(['transactions', 'rentals', 'users'])
            ->orderByDesc('is_central_store')
            ->orderBy('name')
            ->get();

        return view('sites.index', compact('sites'));
    }

    public function show(Site $site)
    {
        $site->load(['users', 'anomalies' => fn ($q) => $q->where('status', 'open')]);
        $currentPeriod = now()->format('Y-m');

        $monthlyRental = $site->rentals()->where('billing_period', $currentPeriod)->sum('grand_total_cost');

        $balanceData = app(GetSiteBalanceAsOf::class)->forSite($site->id);

        return view('sites.show', compact('site', 'balanceData', 'monthlyRental', 'currentPeriod'));
    }
}
