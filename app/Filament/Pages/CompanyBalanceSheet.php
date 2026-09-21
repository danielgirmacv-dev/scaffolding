<?php

namespace App\Filament\Pages;

use App\Services\Reports\ConsolidationReportService;
use App\Support\FilamentRoleAccess;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class CompanyBalanceSheet extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Company Balance Sheet';

    protected static ?string $title = 'Company Balance Sheet (Inventory Matrix)';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.company-balance-sheet';

    #[Url]
    public string $asOfDate = '';

    public string $category = 'all';

    public string $search = '';

    public static function canAccess(): bool
    {
        return FilamentRoleAccess::canAccessPanel();
    }

    public function mount(): void
    {
        if (empty($this->asOfDate)) {
            $this->asOfDate = now()->toDateString();
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $reportService = app(ConsolidationReportService::class);
        $data = $reportService->getCompanyBalanceReport($this->asOfDate);

        $materials = $data['materials'];

        if ($this->category !== 'all') {
            $materials = $materials->where('category', $this->category);
        }

        if (! empty($this->search)) {
            $term = strtolower(trim($this->search));
            $materials = $materials->filter(fn ($m) => str_contains(strtolower($m->name), $term)
                || str_contains(strtolower((string) $m->item_code), $term)
            );
        }

        return [
            'sites' => $data['sites'],
            'materials' => $materials,
            'matrix' => $data['matrix'],
            'totals' => $data['totals'],
            'categories' => ['all' => 'All Categories', 'Scaffolding' => 'Scaffolding', 'Formwork' => 'Formwork', 'Shoring & Props' => 'Shoring & Props', 'Accessories' => 'Accessories'],
        ];
    }
}
