<?php

namespace Tests\Feature;

use App\Actions\Inventory\RecordMaterialTransaction;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\MaterialResource;
use App\Filament\Resources\MaterialTransactionResource;
use App\Filament\Resources\RentalResource;
use App\Filament\Resources\SiteResource;
use App\Filament\Resources\UserResource;
use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filament authorization tests.
 *
 * NOTE: The intl PHP extension is not installed in this environment.
 * Tests that would render Filament tables (which use the intl `format()` method
 * internally) are limited to HTTP responses for create/non-table pages, or use
 * static policy method assertions only. Listing pages are verified via policy
 * methods rather than HTTP GET to avoid the intl dependency.
 */
class FilamentAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_filament_login(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_filament_dashboard(): void
    {
        $admin = User::where('email', 'admin@eecproducts.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_store_keeper_can_manage_transactions_but_not_users(): void
    {
        $storeKeeper = User::where('email', 'storekeeper@eecproducts.com')->firstOrFail();

        $this->actingAs($storeKeeper);

        $this->assertTrue(MaterialTransactionResource::canViewAny());
        $this->assertTrue(MaterialTransactionResource::canCreate());
        $this->assertFalse(UserResource::canViewAny());

        $this->get('/admin/users')
            ->assertForbidden();
    }

    public function test_site_engineer_can_create_transactions_but_not_manage_sites(): void
    {
        $engineer = User::where('email', 'epu.engineer@eecproducts.com')->firstOrFail();

        $this->actingAs($engineer);

        $this->assertTrue(MaterialTransactionResource::canViewAny());
        $this->assertTrue(MaterialTransactionResource::canCreate());
        $this->assertFalse(SiteResource::canCreate());

        $this->get('/admin/sites/create')
            ->assertForbidden();
    }

    public function test_finance_can_manage_invoices_but_not_materials(): void
    {
        $finance = User::where('email', 'finance@eecproducts.com')->firstOrFail();

        $this->actingAs($finance);

        $this->assertTrue(InvoiceResource::canViewAny());
        $this->assertTrue(InvoiceResource::canCreate());
        $this->assertFalse(MaterialResource::canCreate());

        $this->get('/admin/materials/create')
            ->assertForbidden();
    }

    public function test_store_keeper_can_view_rentals_but_not_create_them(): void
    {
        $storeKeeper = User::where('email', 'storekeeper@eecproducts.com')->firstOrFail();

        $this->actingAs($storeKeeper);

        $this->assertTrue(RentalResource::canViewAny());
        $this->assertFalse(RentalResource::canCreate());

        $this->get('/admin/rentals/create')
            ->assertForbidden();
    }

    /** store_keeper row in permission matrix: Invoices = denied */
    public function test_store_keeper_cannot_access_invoices(): void
    {
        $storeKeeper = User::where('email', 'storekeeper@eecproducts.com')->firstOrFail();

        $this->actingAs($storeKeeper);

        $this->assertFalse(InvoiceResource::canViewAny());
        $this->assertFalse(InvoiceResource::canCreate());

        // create route also forbidden (not a table render, safe without intl)
        $this->get('/admin/invoices/create')
            ->assertForbidden();
    }

    /** finance row in permission matrix: Invoices = Full CRUD */
    public function test_finance_has_full_invoice_crud_permissions(): void
    {
        $finance = User::where('email', 'finance@eecproducts.com')->firstOrFail();

        $this->actingAs($finance);

        $this->assertTrue(InvoiceResource::canViewAny());
        $this->assertTrue(InvoiceResource::canCreate());
        // canEdit / canDelete use a $record — pass a dummy non-null value
        $this->assertTrue(InvoiceResource::canEdit(new \stdClass));
        $this->assertFalse(InvoiceResource::canDelete(new \stdClass)); // only admin can delete
    }

    /** admin row in permission matrix: Users = Full CRUD */
    public function test_admin_can_manage_users(): void
    {
        $admin = User::where('email', 'admin@eecproducts.com')->firstOrFail();

        $this->actingAs($admin);

        $this->assertTrue(UserResource::canViewAny());

        // /admin/users/create is a form page (no table render) — safe without intl
        $this->get('/admin/users/create')
            ->assertOk();
    }

    public function test_storekeeper_can_access_filament_dashboard(): void
    {
        $user = User::where('email', 'storekeeper@eecproducts.com')->firstOrFail();
        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_site_engineer_can_access_filament_dashboard(): void
    {
        $user = User::where('email', 'epu.engineer@eecproducts.com')->firstOrFail();
        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_finance_can_access_filament_dashboard(): void
    {
        $user = User::where('email', 'finance@eecproducts.com')->firstOrFail();
        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_site_engineer_sees_only_assigned_site(): void
    {
        $engineer = User::where('email', 'epu.engineer@eecproducts.com')->firstOrFail();
        $this->actingAs($engineer);

        $this->get('/admin/sites')
            ->assertOk();

        $sites = SiteResource::getEloquentQuery()->get();
        $this->assertCount(1, $sites);
        $this->assertSame($engineer->site_id, $sites->first()->id);
    }

    public function test_store_keeper_cannot_create_rentals(): void
    {
        $storeKeeper = User::where('email', 'storekeeper@eecproducts.com')->firstOrFail();
        $this->actingAs($storeKeeper);

        $this->get('/admin/rentals')
            ->assertOk();

        $this->get('/admin/rentals/create')
            ->assertForbidden();
    }

    public function test_finance_cannot_create_material_transactions(): void
    {
        $finance = User::where('email', 'finance@eecproducts.com')->firstOrFail();
        $this->actingAs($finance);

        $this->get('/admin/material-transactions')
            ->assertOk();

        $this->get('/admin/material-transactions/create')
            ->assertForbidden();
    }

    public function test_store_keeper_can_access_create_material_transaction_page(): void
    {
        $storeKeeper = User::where('email', 'storekeeper@eecproducts.com')->firstOrFail();
        $this->actingAs($storeKeeper);

        $this->get('/admin/material-transactions/create')
            ->assertOk();
    }

    public function test_transfer_with_to_site_creates_paired_records(): void
    {
        $admin = User::where('email', 'admin@eecproducts.com')->firstOrFail();
        $this->actingAs($admin);

        $centralStore = Site::where('is_central_store', true)->firstOrFail();
        $epu = Site::where('code', 'EPU')->firstOrFail();
        $material = Material::where('item_code', 'SCAF-CL-01')->firstOrFail();

        $recordAction = app(RecordMaterialTransaction::class);
        $tx = $recordAction->execute([
            'site_id' => $centralStore->id,
            'material_id' => $material->id,
            'direction' => 'transfer_out',
            'quantity' => 20,
            'to_site_id' => $epu->id,
            'ref_no' => 'WB-TEST-TO-SITE-01',
            'transaction_date' => now()->toDateString(),
            'notes' => 'Testing To field linkage',
            'created_by' => $admin->id,
            'status' => 'approved',
        ]);

        $this->assertNotNull($tx->to_site_id);
        $this->assertSame($epu->id, $tx->to_site_id);
        $this->assertNotNull($tx->linked_transaction_id);

        $paired = MaterialTransaction::find($tx->linked_transaction_id);
        $this->assertNotNull($paired);
        $this->assertSame('transfer_in', $paired->direction);
        $this->assertSame($epu->id, $paired->site_id);
        $this->assertSame($centralStore->id, $paired->from_site_id);
    }
}
