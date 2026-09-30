<?php

namespace App\Filament\Pages;

use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Rental;
use App\Models\Site;
use App\Support\FilamentRoleAccess;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class MaterialDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Material Dashboard';

    protected static ?string $title = 'EEIG Material Dashboard';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.material-dashboard';

    #[Url]
    public string $activeCategory = 'all';

    public static function canAccess(): bool
    {
        return FilamentRoleAccess::canViewMaterials();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manage_materials')
                ->label('Manage Materials')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->url(route('filament.admin.resources.materials.index')),

            Action::make('add_transaction')
                ->label('Record Transaction')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->url(route('filament.admin.resources.material-transactions.create'))
                ->visible(fn (): bool => FilamentRoleAccess::canCreateTransactions()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $today = Carbon::today();

        /* ─── Totals ─── */
        $totalMaterials = Material::count();
        $scaffoldingCount = Material::where('category', 'Scaffolding')->count();
        $formworkCount = Material::where('category', 'Formwork')->count();
        $activeMaterials = Material::where('is_active', true)->count();
        $inactiveMaterials = Material::where('is_active', false)->count();

        /* ─── Transaction counts ─── */
        $totalTransactions = MaterialTransaction::count();
        $approvedTx = MaterialTransaction::where('status', 'approved')->count();
        $pendingTx = MaterialTransaction::where('status', 'pending_approval')->count();

        /* ─── Current month rental ─── */
        $currentPeriod = $today->format('Y-m');
        $lastPeriod = Carbon::now()->subMonth()->format('Y-m');
        $currentRevenue = (float) Rental::where('billing_period', $currentPeriod)->sum('grand_total_cost');
        $lastRevenue = (float) Rental::where('billing_period', $lastPeriod)->sum('grand_total_cost');
        $revenueDelta = $lastRevenue > 0 ? (($currentRevenue - $lastRevenue) / $lastRevenue) * 100 : 0;

        /* ─── Central store ─── */
        $centralStore = Site::where('is_central_store', true)->first();
        $depotStock = 0.0;
        $depotOut = 0.0;
        if ($centralStore) {
            $depotIn = (float) MaterialTransaction::where('site_id', $centralStore->id)->whereIn('direction', ['in', 'adjustment'])->sum('quantity');
            $depotOut = (float) MaterialTransaction::where('site_id', $centralStore->id)->whereIn('direction', ['out', 'transfer_out'])->sum('quantity');
            $depotStock = max(0.0, $depotIn - $depotOut);
        }

        /* ─── Category-filtered material list ─── */
        $materialsQuery = Material::query()->orderBy('category')->orderBy('name');
        if ($this->activeCategory !== 'all') {
            $materialsQuery->where('category', $this->activeCategory);
        }
        $materials = $materialsQuery->get();

        /* ─── Per-material enrichment: transaction volume + top-3 sites ─── */
        $materialIds = $materials->pluck('id');

        $txVolumes = MaterialTransaction::whereIn('material_id', $materialIds)
            ->selectRaw('material_id, SUM(quantity) as total_qty, COUNT(*) as tx_count')
            ->groupBy('material_id')
            ->pluck('total_qty', 'material_id');

        $txCounts = MaterialTransaction::whereIn('material_id', $materialIds)
            ->selectRaw('material_id, COUNT(*) as cnt')
            ->groupBy('material_id')
            ->pluck('cnt', 'material_id');

        /* ─── Scaffolding vs Formwork category stats ─── */
        $scaffoldingRevenue = 0.0;
        $formworkRevenue = 0.0;

        // Revenue by category via rental items
        $categoryRevenue = DB::table('rental_items')
            ->join('materials', 'rental_items.material_id', '=', 'materials.id')
            ->join('rentals', 'rental_items.rental_id', '=', 'rentals.id')
            ->where('rentals.billing_period', $currentPeriod)
            ->selectRaw('materials.category, SUM(rental_items.quantity * rental_items.effective_rate_per_day) as revenue')
            ->groupBy('materials.category')
            ->get()
            ->keyBy('category');

        $scaffoldingRevenue = (float) ($categoryRevenue['Scaffolding']->revenue ?? 0);
        $formworkRevenue = (float) ($categoryRevenue['Formwork']->revenue ?? 0);

        /* ─── 6-month transaction trend ─── */
        $months = collect(range(5, 0))->map(fn (int $n): string => Carbon::now()->subMonths($n)->format('Y-m'));
        $monthLabels = $months->map(fn ($ym): string => Carbon::createFromFormat('Y-m', $ym)->format('M Y'))->toArray();

        $scaffoldingTrend = [];
        $formworkTrend = [];

        foreach ($months as $ym) {
            [$y, $m] = explode('-', $ym);
            $s = (float) MaterialTransaction::whereHas('material', fn ($q) => $q->where('category', 'Scaffolding'))
                ->whereYear('created_at', $y)
                ->whereMonth('created_at', $m)
                ->sum('quantity');
            $f = (float) MaterialTransaction::whereHas('material', fn ($q) => $q->where('category', 'Formwork'))
                ->whereYear('created_at', $y)
                ->whereMonth('created_at', $m)
                ->sum('quantity');
            $scaffoldingTrend[] = round($s, 0);
            $formworkTrend[] = round($f, 0);
        }

        /* ─── Active sites ─── */
        $activeSites = Site::where('status', 'active')->where('is_central_store', false)->count();

        return [
            // KPIs
            'totalMaterials' => $totalMaterials,
            'scaffoldingCount' => $scaffoldingCount,
            'formworkCount' => $formworkCount,
            'activeMaterials' => $activeMaterials,
            'inactiveMaterials' => $inactiveMaterials,
            'totalTransactions' => $totalTransactions,
            'approvedTx' => $approvedTx,
            'pendingTx' => $pendingTx,
            'currentRevenue' => $currentRevenue,
            'lastRevenue' => $lastRevenue,
            'revenueDelta' => $revenueDelta,
            'depotStock' => $depotStock,
            'depotOut' => $depotOut,
            'activeSites' => $activeSites,

            // Category revenue
            'scaffoldingRevenue' => $scaffoldingRevenue,
            'formworkRevenue' => $formworkRevenue,

            // Material table
            'materials' => $materials,
            'txVolumes' => $txVolumes,
            'txCounts' => $txCounts,
            'activeCategory' => $this->activeCategory,

            // Chart data (JSON)
            'monthLabels' => json_encode($monthLabels),
            'scaffoldingTrend' => json_encode($scaffoldingTrend),
            'formworkTrend' => json_encode($formworkTrend),

            // Routes
            'materialsIndexUrl' => route('filament.admin.resources.materials.index'),
            'materialsCreateUrl' => route('filament.admin.resources.materials.create'),
            'txIndexUrl' => route('filament.admin.resources.material-transactions.index'),
            'txCreateUrl' => route('filament.admin.resources.material-transactions.create'),
            'canManage' => FilamentRoleAccess::canManageMaterials(),
            'canCreateTransactions' => FilamentRoleAccess::canCreateTransactions(),
        ];
    }

    /**
     * Livewire action to switch active category tab.
     */
    public function setCategory(string $category): void
    {
        $this->activeCategory = $category;
    }
}
