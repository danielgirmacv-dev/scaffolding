<?php

namespace App\Filament\Pages;

use App\Services\Reports\ConsolidationReportService;
use App\Support\FilamentRoleAccess;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class CostSummaryReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Financial / Reports';

    protected static ?string $navigationLabel = 'COST Summary';

    protected static ?string $title = 'COST Summary Rollup';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.cost-summary-report';

    #[Url]
    public string $period = '';

    public static function canAccess(): bool
    {
        return FilamentRoleAccess::canViewRentals();
    }

    public function mount(): void
    {
        if (empty($this->period)) {
            $this->period = now()->format('Y-m');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $reportService = app(ConsolidationReportService::class);
        $rows = $reportService->getCostSummaryReport($this->period);

        $totalSubtotal = $rows->sum('subtotal_cost');
        $totalVat = $rows->sum('vat_amount');
        $grandTotal = $rows->sum('grand_total_cost');
        $totalItems = $rows->sum('total_items_rented');

        return [
            'rows' => $rows,
            'period' => $this->period,
            'totalSubtotal' => $totalSubtotal,
            'totalVat' => $totalVat,
            'grandTotal' => $grandTotal,
            'totalItems' => $totalItems,
        ];
    }
}
