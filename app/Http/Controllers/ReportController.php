<?php

namespace App\Http\Controllers;

use App\Actions\Inventory\GetSiteBalanceAsOf;
use App\Models\StockAnomaly;
use App\Services\Reports\ConsolidationReportService;

class ReportController extends Controller
{
    public function __construct(
        protected ConsolidationReportService $reportService,
        protected GetSiteBalanceAsOf $balanceAction
    ) {}

    public function companyBalance()
    {
        $asOfDate = request('as_of', now()->toDateString());
        $data = $this->reportService->getCompanyBalanceReport($asOfDate);

        return view('reports.company-balance', array_merge($data, ['asOfDate' => $asOfDate]));
    }

    public function costSummary()
    {
        $period = request('period', now()->format('Y-m'));
        $rows = $this->reportService->getCostSummaryReport($period);

        $grandTotal = $rows->sum('grand_total_cost');
        $subtotalSum = $rows->sum('subtotal_cost');
        $vatSum = $rows->sum('vat_amount');

        return view('reports.cost-summary', compact('rows', 'period', 'grandTotal', 'subtotalSum', 'vatSum'));
    }

    public function anomalies()
    {
        $anomalies = StockAnomaly::with(['site', 'material'])
            ->orderBy('status')
            ->orderByDesc('id')
            ->paginate(30);

        return view('reports.anomalies', compact('anomalies'));
    }
}
