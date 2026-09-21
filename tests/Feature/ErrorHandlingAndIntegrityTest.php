<?php

namespace Tests\Feature;

use App\Filament\Resources\MaterialResource;
use App\Filament\Resources\SiteResource;
use App\Models\Material;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ErrorHandlingAndIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_material_with_transactions_cannot_be_deleted_via_can_delete(): void
    {
        $admin = User::where('email', 'admin@eecproducts.com')->firstOrFail();
        $this->actingAs($admin);

        $materialWithTx = Material::whereHas('transactions')->firstOrFail();

        $this->assertFalse(
            MaterialResource::canDelete($materialWithTx),
            'Materials with existing transactions should not be deletable.'
        );

        $unusedMaterial = Material::create([
            'name' => 'Temporary Test Clamp',
            'item_code' => 'TEST-UNUSED-CLAMP-001',
            'category' => 'Scaffolding',
            'unit_of_measure' => 'Pcs',
            'market_rate_per_day' => 10.0,
            'eeig_discount_percent' => 25.0,
            'depreciation_rate_per_day' => 0.0,
            'replacement_cost' => 150.0,
            'is_active' => true,
        ]);

        $this->assertTrue(
            MaterialResource::canDelete($unusedMaterial),
            'Unused materials without transactions or rentals should be deletable.'
        );
    }

    public function test_central_store_and_sites_with_transactions_cannot_be_deleted(): void
    {
        $admin = User::where('email', 'admin@eecproducts.com')->firstOrFail();
        $this->actingAs($admin);

        $centralStore = Site::where('is_central_store', true)->firstOrFail();
        $this->assertFalse(
            SiteResource::canDelete($centralStore),
            'Central store should never be deletable.'
        );

        $projectSiteWithTx = Site::where('is_central_store', false)
            ->whereHas('transactions')
            ->firstOrFail();

        $this->assertFalse(
            SiteResource::canDelete($projectSiteWithTx),
            'Project sites with existing transaction history should not be deletable.'
        );

        $freshSite = Site::create([
            'code' => 'TMP-TEST',
            'name' => 'Temporary Test Project',
            'is_central_store' => false,
            'status' => 'active',
        ]);

        $this->assertTrue(
            SiteResource::canDelete($freshSite),
            'Fresh sites without transactions, rentals, or users should be deletable.'
        );
    }

    public function test_material_resource_includes_soft_deletes_in_query(): void
    {
        $material = Material::create([
            'name' => 'Soft Deleted Item',
            'item_code' => 'SOFT-DEL-01',
            'category' => 'Scaffolding',
            'unit_of_measure' => 'Pcs',
            'market_rate_per_day' => 5.0,
            'eeig_discount_percent' => 20.0,
            'depreciation_rate_per_day' => 0.0,
            'replacement_cost' => 50.0,
            'is_active' => true,
        ]);

        $material->delete();

        $this->assertSoftDeleted('materials', ['id' => $material->id]);

        $query = MaterialResource::getEloquentQuery();
        $this->assertTrue(
            $query->whereKey($material->id)->exists(),
            'MaterialResource query should include soft-deleted records for trash filter access.'
        );
    }

    public function test_global_exception_handler_intercepts_foreign_key_violation_for_api(): void
    {
        $exception = new QueryException(
            'mysql',
            'delete from materials where id = 1',
            [],
            new \Exception('Cannot delete or update a parent row: a foreign key constraint fails (SQLSTATE[23000]: Integrity constraint violation: 1451)', 23000)
        );

        $request = Request::create('/api/materials/1', 'DELETE');
        $request->headers->set('Accept', 'application/json');

        /** @var Application $app */
        $app = app();
        $handler = $app->make(ExceptionHandler::class);

        $response = $handler->render($request, $exception);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('INTEGRITY_CONSTRAINT_VIOLATION', $data['error']);
        $this->assertStringContainsString('referenced by existing transactions', $data['message']);
    }
}
