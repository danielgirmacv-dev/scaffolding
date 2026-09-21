<?php

namespace App\Http\Controllers;

use App\Models\Rental;

class RentalController extends Controller
{
    public function index()
    {
        $currentPeriod = now()->format('Y-m');
        $period = request('period', $currentPeriod);

        $rentals = Rental::with(['site', 'material'])
            ->where('billing_period', $period)
            ->orderBy('site_id')
            ->orderBy('material_id')
            ->paginate(30);

        $grandTotal = Rental::where('billing_period', $period)->sum('grand_total_cost');
        $subtotalSum = Rental::where('billing_period', $period)->sum('subtotal_cost');
        $vatSum = Rental::where('billing_period', $period)->sum('vat_amount');

        return view('rentals.index', compact('rentals', 'period', 'grandTotal', 'subtotalSum', 'vatSum', 'currentPeriod'));
    }
}
