<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Site;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScaffoldingUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_dashboard_page_loads_successfully(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('EEIG / EEC Scaffolding & Formwork System');
    }

    public function test_sites_pages_load(): void
    {
        $response = $this->get(route('sites.index'));
        $response->assertOk();
        $response->assertSee('All Sites');

        $site = Site::first();
        $detail = $this->get(route('sites.show', $site));
        $detail->assertOk();
        $detail->assertSee($site->code);
    }

    public function test_materials_pages_load(): void
    {
        $response = $this->get(route('materials.index'));
        $response->assertOk();
        $response->assertSee('Scaffolding &amp; Formwork Catalog', false);

        $material = Material::first();
        $detail = $this->get(route('materials.show', $material));
        $detail->assertOk();
        $detail->assertSee($material->name);
    }

    public function test_transactions_page_loads(): void
    {
        $response = $this->get(route('transactions.index'));
        $response->assertOk();
        $response->assertSee('Immutable Transaction Ledger');
    }

    public function test_rentals_page_loads(): void
    {
        $response = $this->get(route('rentals.index'));
        $response->assertOk();
        $response->assertSee('Rental Records');
    }

    public function test_company_balance_report_loads(): void
    {
        $response = $this->get(route('reports.company-balance'));
        $response->assertOk();
        $response->assertSee('Company Balance Matrix');
    }

    public function test_cost_summary_report_loads(): void
    {
        $response = $this->get(route('reports.cost-summary'));
        $response->assertOk();
        $response->assertSee('Cost Summary Report');
    }

    public function test_anomalies_report_loads(): void
    {
        $response = $this->get(route('reports.anomalies'));
        $response->assertOk();
        $response->assertSee('Stock Balance Anomalies');
    }
}
