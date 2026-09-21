<?php

namespace Tests\Feature;

use App\Actions\Inventory\GetSiteBalanceAsOf;
use App\Actions\Inventory\RecordMaterialTransaction;
use App\Filament\Pages\CompanyBalanceSheet;
use App\Filament\Pages\CostSummaryReport;
use App\Filament\Pages\InventoryCharts;
use App\Filament\Resources\MaintenanceRecordResource;
use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Site;
use App\Models\User;
use App\Services\Reports\ConsolidationReportService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_site_classifications_and_helpers(): void
    {
        $central = Site::where('code', 'CENTRAL STORE')->firstOrFail();
        $this->assertTrue($central->isCentralStore());
        $this->assertEquals('Central Store', $central->type_label);

        $scaff = Site::where('code', 'SCAFF')->firstOrFail();
        $this->assertTrue($scaff->isSubStore());
        $this->assertEquals('Internal Sub-Store', $scaff->type_label);

        $chaka = Site::where('code', 'SCAFF-CHAKA')->firstOrFail();
        $this->assertTrue($chaka->isSubStore());
        $this->assertEquals('Internal Sub-Store', $chaka->type_label);

        $production = Site::where('code', 'PRODUCTION')->firstOrFail();
        $this->assertTrue($production->isProduction());
        $this->assertEquals('In-House Production', $production->type_label);

        $jimma = Site::where('code', 'JIMMA')->firstOrFail();
        $this->assertTrue($jimma->isOutOfAddis());
        $this->assertEquals('Project Site (Out-of-Addis)', $jimma->type_label);

        $epu = Site::where('code', 'EPU')->firstOrFail();
        $this->assertTrue($epu->isClientProject());
        $this->assertEquals('Project Site', $epu->type_label);
    }

    public function test_in_house_production_on_process_is_excluded_from_available_stock_until_finished(): void
    {
        $productionSite = Site::where('code', 'PRODUCTION')->firstOrFail();
        $material = Material::firstOrFail();
        $balanceAction = app(GetSiteBalanceAsOf::class);
        $recordAction = app(RecordMaterialTransaction::class);

        $initialBalance = $balanceAction->execute($productionSite->id, $material->id);

        // Record a production order phase that is still "on process"
        $tx = $recordAction->execute([
            'site_id' => $productionSite->id,
            'material_id' => $material->id,
            'direction' => 'in',
            'quantity' => 50,
            'production_stage' => 'on_process',
            'transaction_date' => now()->toDateString(),
            'notes' => 'H-Frame welding phase on process',
        ]);

        $this->assertEquals('on_process', $tx->production_stage);

        // Available balance must NOT include the 50 on-process units
        $balanceDuringWip = $balanceAction->execute($productionSite->id, $material->id);
        $this->assertEquals($initialBalance, $balanceDuringWip);

        // Mark production phase finished
        $tx->update(['production_stage' => 'finished']);

        // Now available stock must increase by 50
        $balanceAfterFinish = $balanceAction->execute($productionSite->id, $material->id);
        $this->assertEquals($initialBalance + 50, $balanceAfterFinish);
    }

    public function test_maintenance_damaged_items_are_removed_from_active_inventory(): void
    {
        $site = Site::where('code', 'EPU')->firstOrFail();
        $material = Material::firstOrFail();
        $balanceAction = app(GetSiteBalanceAsOf::class);
        $recordAction = app(RecordMaterialTransaction::class);

        // First give the site some active stock
        $recordAction->execute([
            'site_id' => $site->id,
            'material_id' => $material->id,
            'direction' => 'in',
            'quantity' => 100,
            'transaction_date' => now()->toDateString(),
        ]);

        $stockBeforeDamage = $balanceAction->execute($site->id, $material->id);
        $this->assertGreaterThanOrEqual(100, $stockBeforeDamage);

        // Report 15 items damaged
        $damagedTx = $recordAction->execute([
            'site_id' => $site->id,
            'material_id' => $material->id,
            'direction' => 'damaged',
            'quantity' => 15,
            'm2_coverage' => 45.0,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Bent couplers returned from site',
        ]);

        $this->assertEquals('in_maintenance', $damagedTx->maintenance_status);
        $this->assertEquals(45.0, (float) $damagedTx->m2_coverage);

        // Site stock must be reduced by 15
        $stockAfterDamage = $balanceAction->execute($site->id, $material->id);
        $this->assertEquals($stockBeforeDamage - 15, $stockAfterDamage);
    }

    public function test_sub_stores_and_production_are_excluded_from_client_cost_summary(): void
    {
        $reportService = app(ConsolidationReportService::class);
        $period = now()->format('Y-m');

        $rows = $reportService->getCostSummaryReport($period);
        $siteCodes = $rows->pluck('site_code')->all();

        // Must NOT contain Central Store, Sub-Stores, or Production
        $this->assertNotContains('CENTRAL STORE', $siteCodes);
        $this->assertNotContains('CS', $siteCodes);
        $this->assertNotContains('C/STORE', $siteCodes);
        $this->assertNotContains('SCAFF', $siteCodes);
        $this->assertNotContains('SCAFF-CHAKA', $siteCodes);
        $this->assertNotContains('PRODUCTION', $siteCodes);

        // Must contain client project sites
        $this->assertContains('EPU', $siteCodes);
        $this->assertContains('JIMMA', $siteCodes);
    }

    public function test_material_transaction_inter_site_transfer_with_m2_coverage(): void
    {
        $origin = Site::where('code', 'CENTRAL STORE')->firstOrFail();
        $dest = Site::where('code', 'EPU')->firstOrFail();
        $material = Material::firstOrFail();
        $recordAction = app(RecordMaterialTransaction::class);

        // Ensure origin has stock
        $recordAction->execute([
            'site_id' => $origin->id,
            'material_id' => $material->id,
            'direction' => 'in',
            'quantity' => 200,
            'transaction_date' => now()->toDateString(),
        ]);

        // Transfer with m2_coverage
        $transferOut = $recordAction->execute([
            'site_id' => $origin->id,
            'material_id' => $material->id,
            'direction' => 'transfer_out',
            'quantity' => 30,
            'm2_coverage' => 75.50,
            'to_site_id' => $dest->id,
            'ref_no' => 'WB-9988',
            'transaction_date' => now()->toDateString(),
        ]);

        $this->assertNotNull($transferOut->linked_transaction_id);
        $this->assertEquals(75.50, (float) $transferOut->m2_coverage);

        $pairedIn = MaterialTransaction::find($transferOut->linked_transaction_id);
        $this->assertNotNull($pairedIn);
        $this->assertEquals('transfer_in', $pairedIn->direction);
        $this->assertEquals($dest->id, $pairedIn->site_id);
        $this->assertEquals($origin->id, $pairedIn->from_site_id);
        $this->assertEquals(30, (float) $pairedIn->quantity);
        $this->assertEquals(75.50, (float) $pairedIn->m2_coverage);
    }

    public function test_filament_operational_pages_access_and_rendering(): void
    {
        $admin = User::where('email', 'admin@eecproducts.com')->firstOrFail();
        $this->actingAs($admin);

        $this->assertTrue(CompanyBalanceSheet::canAccess());
        $this->assertTrue(CostSummaryReport::canAccess());
        $this->assertTrue(MaintenanceRecordResource::canAccess());
        $this->assertTrue(InventoryCharts::canAccess());

        $this->get('/admin/company-balance-sheet')->assertOk();
        $this->get('/admin/cost-summary-report')->assertOk();
        $this->get('/admin/maintenance-records')->assertOk();
        $this->get('/admin/inventory-charts')->assertOk();
    }
}
