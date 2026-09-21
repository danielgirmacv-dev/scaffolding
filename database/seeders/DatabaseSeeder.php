<?php

namespace Database\Seeders;

use App\Actions\Inventory\CalculateRentalCost;
use App\Actions\Inventory\RecordMaterialTransaction;
use App\Models\Material;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Construction Sites
        $centralStore = Site::create([
            'code' => 'CS',
            'name' => 'Central Store (Main Warehouse)',
            'client' => 'EEIG / Internal',
            'location' => 'Kality Central Yard',
            'is_central_store' => true,
            'status' => 'active',
        ]);

        $epu = Site::create([
            'code' => 'EPU',
            'name' => 'EPU Power Substation Project',
            'client' => 'Ethiopian Electric Power (EEP)',
            'location' => 'Addis Ababa East',
            'is_central_store' => false,
            'status' => 'active',
        ]);

        $moj = Site::create([
            'code' => 'MOJ II',
            'name' => 'Ministry of Justice HQ Phase II',
            'client' => 'Federal Ministry of Justice',
            'location' => 'Arat Kilo, Addis Ababa',
            'is_central_store' => false,
            'status' => 'active',
        ]);

        $glp = Site::create([
            'code' => 'GLP',
            'name' => 'Gelan Logistics Hub Project',
            'client' => 'Ethiopian Shipping & Logistics',
            'location' => 'Gelan Industrial Zone',
            'is_central_store' => false,
            'status' => 'active',
        ]);

        $nbe = Site::create([
            'code' => 'NBE',
            'name' => 'National Bank Expansion Tower',
            'client' => 'National Bank of Ethiopia',
            'location' => 'Sudan Street, Kirkos',
            'is_central_store' => false,
            'status' => 'active',
        ]);

        $this->call(SiteCatalogSeeder::class);

        // 2. Seed Users for Role-Based Access
        $admin = User::create([
            'name' => 'EEIG System Admin',
            'email' => 'admin@eecproducts.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $storeKeeper = User::create([
            'name' => 'Alemayehu Tadesse (Store Keeper)',
            'email' => 'storekeeper@eecproducts.com',
            'password' => Hash::make('password'),
            'role' => 'store_keeper',
            'site_id' => $centralStore->id,
        ]);

        $siteEngineer = User::create([
            'name' => 'Dawit Kebede (Site Engineer)',
            'email' => 'epu.engineer@eecproducts.com',
            'password' => Hash::make('password'),
            'role' => 'site_engineer',
            'site_id' => $epu->id,
        ]);

        $finance = User::create([
            'name' => 'Sara Wolde (Finance & Cost Analyst)',
            'email' => 'finance@eecproducts.com',
            'password' => Hash::make('password'),
            'role' => 'finance',
        ]);

        // 3. Seed Scaffolding & Formwork Materials Catalog
        $materialsData = [
            [
                'item_code' => 'SCAF-CL-01',
                'name' => 'Coupler Clamp / Swivel Clamp',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 1.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.15,
                'replacement_cost' => 120.00,
            ],
            [
                'item_code' => 'SCAF-IP-30',
                'name' => 'Inner Pipe (3.0m Steel Tube)',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 3.20,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.30,
                'replacement_cost' => 450.00,
            ],
            [
                'item_code' => 'SCAF-HF-SET',
                'name' => 'H-Frame Scaffolding Set',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'SET',
                'market_rate_per_day' => 12.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 1.20,
                'replacement_cost' => 2800.00,
            ],
            [
                'item_code' => 'SCAF-TJ-60',
                'name' => 'Top Jack / U-Head Jack (600mm)',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 2.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.25,
                'replacement_cost' => 380.00,
            ],
            [
                'item_code' => 'SCAF-BJ-60',
                'name' => 'Base Jack (600mm Solid)',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 2.20,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.20,
                'replacement_cost' => 350.00,
            ],
            [
                'item_code' => 'FORM-IB-20',
                'name' => 'I-Beam H20 Timber Formwork Beam',
                'category' => 'Formwork',
                'unit_of_measure' => 'ML',
                'market_rate_per_day' => 4.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.45,
                'replacement_cost' => 650.00,
            ],
            [
                'item_code' => 'FORM-SW-CL',
                'name' => 'Shear Wall Clamp / Alignment Clamp',
                'category' => 'Formwork',
                'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 3.80,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.35,
                'replacement_cost' => 520.00,
            ],
            [
                'item_code' => 'FORM-TR-15',
                'name' => 'Tie Rod 15/17mm (6.0m length)',
                'category' => 'Formwork',
                'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 1.80,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.18,
                'replacement_cost' => 260.00,
            ],
            [
                'item_code' => 'FORM-WN-01',
                'name' => 'Wing Nut / Water Stopper Nut',
                'category' => 'Formwork',
                'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 0.80,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.08,
                'replacement_cost' => 95.00,
            ],
            [
                'item_code' => 'SCAF-SP-30',
                'name' => 'Steel Plank Perforated (3.0m)',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 4.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.40,
                'replacement_cost' => 750.00,
            ],
            // --- Missing items added per EEIG requirements ---
            // CHS tubes (1m-6m)
            [
                'item_code' => 'SCAF-CHS-10', 'name' => 'CHS Tube (1.0m)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 1.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.10, 'replacement_cost' => 150.00,
            ],
            [
                'item_code' => 'SCAF-CHS-20', 'name' => 'CHS Tube (2.0m)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 2.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.20, 'replacement_cost' => 300.00,
            ],
            [
                'item_code' => 'SCAF-CHS-30', 'name' => 'CHS Tube (3.0m)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 3.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.30, 'replacement_cost' => 450.00,
            ],
            [
                'item_code' => 'SCAF-CHS-40', 'name' => 'CHS Tube (4.0m)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 4.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.40, 'replacement_cost' => 600.00,
            ],
            [
                'item_code' => 'SCAF-CHS-50', 'name' => 'CHS Tube (5.0m)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 5.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.50, 'replacement_cost' => 750.00,
            ],
            [
                'item_code' => 'SCAF-CHS-60', 'name' => 'CHS Tube (6.0m)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 6.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.60, 'replacement_cost' => 900.00,
            ],
            // Missing Inner Pipes (1m, 2m)
            [
                'item_code' => 'SCAF-IP-10', 'name' => 'Inner Pipe (1.0m Steel Tube)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 1.10, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.10, 'replacement_cost' => 150.00,
            ],
            [
                'item_code' => 'SCAF-IP-20', 'name' => 'Inner Pipe (2.0m Steel Tube)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 2.10, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.20, 'replacement_cost' => 300.00,
            ],
            // Missing I-Beams (1.9m, 2.9m, 3.9m, 4.9m, 5.9m)
            [
                'item_code' => 'FORM-IB-19', 'name' => 'I-Beam H20 Timber Formwork Beam (1.9m)', 'category' => 'Formwork', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 2.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.20, 'replacement_cost' => 300.00,
            ],
            [
                'item_code' => 'FORM-IB-29', 'name' => 'I-Beam H20 Timber Formwork Beam (2.9m)', 'category' => 'Formwork', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 3.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.30, 'replacement_cost' => 450.00,
            ],
            [
                'item_code' => 'FORM-IB-39', 'name' => 'I-Beam H20 Timber Formwork Beam (3.9m)', 'category' => 'Formwork', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 4.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.40, 'replacement_cost' => 600.00,
            ],
            [
                'item_code' => 'FORM-IB-49', 'name' => 'I-Beam H20 Timber Formwork Beam (4.9m)', 'category' => 'Formwork', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 5.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.50, 'replacement_cost' => 750.00,
            ],
            [
                'item_code' => 'FORM-IB-59', 'name' => 'I-Beam H20 Timber Formwork Beam (5.9m)', 'category' => 'Formwork', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 6.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.60, 'replacement_cost' => 900.00,
            ],
            // Missing H-Frame Extensions
            [
                'item_code' => 'SCAF-HF-EV', 'name' => 'H-Frame Extension Vertical', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 3.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.30, 'replacement_cost' => 600.00,
            ],
            [
                'item_code' => 'SCAF-HF-EH', 'name' => 'H-Frame Extension Horizontal', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 2.50, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.25, 'replacement_cost' => 500.00,
            ],
            // Pin Connector & Pin Lock
            [
                'item_code' => 'SCAF-PC-01', 'name' => 'Pin Connector', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 0.50, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.05, 'replacement_cost' => 50.00,
            ],
            [
                'item_code' => 'SCAF-PL-SET', 'name' => 'Pin Lock Scaffolding', 'category' => 'Scaffolding', 'unit_of_measure' => 'SET',
                'market_rate_per_day' => 15.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 1.50, 'replacement_cost' => 3000.00,
            ],
            // RHS tubes (2m, 3m, 4m, 6m)
            [
                'item_code' => 'SCAF-RHS-20', 'name' => 'RHS Tube (2.0m)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 2.50, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.25, 'replacement_cost' => 350.00,
            ],
            [
                'item_code' => 'SCAF-RHS-30', 'name' => 'RHS Tube (3.0m)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 3.50, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.35, 'replacement_cost' => 500.00,
            ],
            [
                'item_code' => 'SCAF-RHS-40', 'name' => 'RHS Tube (4.0m)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 4.50, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.45, 'replacement_cost' => 650.00,
            ],
            [
                'item_code' => 'SCAF-RHS-60', 'name' => 'RHS Tube (6.0m)', 'category' => 'Scaffolding', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 6.50, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.65, 'replacement_cost' => 950.00,
            ],
            // Prop
            [
                'item_code' => 'FORM-PR-01', 'name' => 'Prop', 'category' => 'Formwork', 'unit_of_measure' => 'Pcs',
                'market_rate_per_day' => 3.00, 'eeig_discount_percent' => 25.00, 'depreciation_rate_per_day' => 0.30, 'replacement_cost' => 600.00,
            ],
        ];

        $materials = [];
        foreach ($materialsData as $data) {
            $materials[$data['item_code']] = Material::create($data);
        }

        // 4. Record Initial Inflow to Central Store and Inter-Site Transfers
        $recordAction = app(RecordMaterialTransaction::class);

        // A. Initial procurement into Central Store
        foreach ($materials as $mat) {
            $recordAction->execute([
                'site_id' => $centralStore->id,
                'material_id' => $mat->id,
                'direction' => 'in',
                'quantity' => 2000,
                'ref_no' => 'PO-2026-001',
                'transaction_date' => '2026-08-01',
                'notes' => 'Central Store Opening Inventory Batch A',
                'created_by' => $storeKeeper->id,
                'approved_by' => $admin->id,
            ]);
        }

        // B. Inter-site transfers: Central Store -> EPU
        $recordAction->execute([
            'site_id' => $centralStore->id,
            'material_id' => $materials['SCAF-CL-01']->id,
            'direction' => 'transfer_out',
            'quantity' => 500,
            'to_site_id' => $epu->id,
            'ref_no' => 'WB-EPU-0101',
            'transaction_date' => '2026-08-10',
            'notes' => 'Mobilization for EPU Substation foundation',
            'created_by' => $storeKeeper->id,
            'approved_by' => $storeKeeper->id,
        ]);

        $recordAction->execute([
            'site_id' => $centralStore->id,
            'material_id' => $materials['SCAF-HF-SET']->id,
            'direction' => 'transfer_out',
            'quantity' => 250,
            'to_site_id' => $epu->id,
            'ref_no' => 'WB-EPU-0102',
            'transaction_date' => '2026-08-10',
            'notes' => 'H-Frames for EPU main structure',
            'created_by' => $storeKeeper->id,
            'approved_by' => $storeKeeper->id,
        ]);

        // C. Inter-site transfers: Central Store -> MOJ II
        $recordAction->execute([
            'site_id' => $centralStore->id,
            'material_id' => $materials['FORM-IB-20']->id,
            'direction' => 'transfer_out',
            'quantity' => 600,
            'to_site_id' => $moj->id,
            'ref_no' => 'WB-MOJ-0044',
            'transaction_date' => '2026-08-15',
            'notes' => 'Timber beams for MOJ 2nd floor slab',
            'created_by' => $storeKeeper->id,
            'approved_by' => $storeKeeper->id,
        ]);

        $recordAction->execute([
            'site_id' => $centralStore->id,
            'material_id' => $materials['FORM-SW-CL']->id,
            'direction' => 'transfer_out',
            'quantity' => 400,
            'to_site_id' => $moj->id,
            'ref_no' => 'WB-MOJ-0045',
            'transaction_date' => '2026-08-15',
            'notes' => 'Clamps for Shear Wall pour',
            'created_by' => $storeKeeper->id,
            'approved_by' => $storeKeeper->id,
        ]);

        // D. Inter-site transfer: Central Store -> GLP
        $recordAction->execute([
            'site_id' => $centralStore->id,
            'material_id' => $materials['SCAF-SP-30']->id,
            'direction' => 'transfer_out',
            'quantity' => 350,
            'to_site_id' => $glp->id,
            'ref_no' => 'WB-GLP-0012',
            'transaction_date' => '2026-08-20',
            'notes' => 'Steel planks for Gelan Warehouse facade',
            'created_by' => $storeKeeper->id,
            'approved_by' => $storeKeeper->id,
        ]);

        // 5. Compute August & September 2026 monthly rentals
        $rentalCalculator = app(CalculateRentalCost::class);
        $rentalCalculator->computeForPeriod('2026-08');
        $rentalCalculator->computeForPeriod('2026-09');
    }
}
