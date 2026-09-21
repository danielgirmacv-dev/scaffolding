<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalog = [
            // Standard Clamps
            [
                'item_code' => 'SCAF-CL-01',
                'name' => 'Coupler Clamp',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 1.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.15,
                'replacement_cost' => 120.00,
            ],
            [
                'item_code' => 'FORM-SW-CL',
                'name' => 'Shear Wall Clamp',
                'category' => 'Formwork',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 3.80,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.35,
                'replacement_cost' => 520.00,
            ],

            // CHS Tubes 1m - 6m
            [
                'item_code' => 'SCAF-CHS-10',
                'name' => 'CHS Tube 1m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 1.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.10,
                'replacement_cost' => 150.00,
            ],
            [
                'item_code' => 'SCAF-CHS-20',
                'name' => 'CHS Tube 2m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 2.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.20,
                'replacement_cost' => 300.00,
            ],
            [
                'item_code' => 'SCAF-CHS-30',
                'name' => 'CHS Tube 3m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 3.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.30,
                'replacement_cost' => 450.00,
            ],
            [
                'item_code' => 'SCAF-CHS-40',
                'name' => 'CHS Tube 4m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 4.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.40,
                'replacement_cost' => 600.00,
            ],
            [
                'item_code' => 'SCAF-CHS-50',
                'name' => 'CHS Tube 5m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 5.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.50,
                'replacement_cost' => 750.00,
            ],
            [
                'item_code' => 'SCAF-CHS-60',
                'name' => 'CHS Tube 6m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 6.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.60,
                'replacement_cost' => 900.00,
            ],

            // Inner Pipes
            [
                'item_code' => 'SCAF-IP-10',
                'name' => 'Inner Pipe (INN.PIPE) 1m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 1.10,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.10,
                'replacement_cost' => 150.00,
            ],
            [
                'item_code' => 'SCAF-IP-20',
                'name' => 'Inner Pipe (INN.PIPE) 2m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 2.10,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.20,
                'replacement_cost' => 300.00,
            ],
            [
                'item_code' => 'SCAF-IP-30',
                'name' => 'Inner Pipe (INN.PIPE) 3m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 3.20,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.30,
                'replacement_cost' => 450.00,
            ],

            // I-Beams
            [
                'item_code' => 'FORM-IB-19',
                'name' => 'I-Beam 1.9m',
                'category' => 'Formwork',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 2.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.20,
                'replacement_cost' => 300.00,
            ],
            [
                'item_code' => 'FORM-IB-29',
                'name' => 'I-Beam 2.9m',
                'category' => 'Formwork',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 3.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.30,
                'replacement_cost' => 450.00,
            ],
            [
                'item_code' => 'FORM-IB-39',
                'name' => 'I-Beam 3.9m',
                'category' => 'Formwork',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 4.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.40,
                'replacement_cost' => 600.00,
            ],
            [
                'item_code' => 'FORM-IB-49',
                'name' => 'I-Beam 4.9m',
                'category' => 'Formwork',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 5.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.50,
                'replacement_cost' => 750.00,
            ],
            [
                'item_code' => 'FORM-IB-59',
                'name' => 'I-Beam 5.9m',
                'category' => 'Formwork',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 6.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.60,
                'replacement_cost' => 900.00,
            ],

            // Jacks & Frames
            [
                'item_code' => 'SCAF-TJ-60',
                'name' => 'Top Jack',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 2.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.25,
                'replacement_cost' => 380.00,
            ],
            [
                'item_code' => 'SCAF-BJ-60',
                'name' => 'Base Jack',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 2.20,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.20,
                'replacement_cost' => 350.00,
            ],
            [
                'item_code' => 'SCAF-HF-SET',
                'name' => 'H-Frame',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'SET',
                'market_rate_per_day' => 12.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 1.20,
                'replacement_cost' => 2800.00,
            ],
            [
                'item_code' => 'SCAF-HF-EV',
                'name' => 'H-Frame Extension Vertical',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 3.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.30,
                'replacement_cost' => 600.00,
            ],
            [
                'item_code' => 'SCAF-HF-EH',
                'name' => 'H-Frame Extension Horizontal',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 2.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.25,
                'replacement_cost' => 500.00,
            ],

            // Connectors & Pin Lock
            [
                'item_code' => 'SCAF-PC-01',
                'name' => 'Pin Connector',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 0.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.05,
                'replacement_cost' => 50.00,
            ],
            [
                'item_code' => 'SCAF-PL-SET',
                'name' => 'Pin Lock Scaffolding',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'SET',
                'market_rate_per_day' => 15.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 1.50,
                'replacement_cost' => 3000.00,
            ],

            // RHS Tubes
            [
                'item_code' => 'SCAF-RHS-20',
                'name' => 'RHS Tube 2m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 2.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.25,
                'replacement_cost' => 350.00,
            ],
            [
                'item_code' => 'SCAF-RHS-30',
                'name' => 'RHS Tube 3m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 3.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.35,
                'replacement_cost' => 500.00,
            ],
            [
                'item_code' => 'SCAF-RHS-40',
                'name' => 'RHS Tube 4m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 4.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.45,
                'replacement_cost' => 650.00,
            ],
            [
                'item_code' => 'SCAF-RHS-60',
                'name' => 'RHS Tube 6m',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 6.50,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.65,
                'replacement_cost' => 950.00,
            ],

            // Props & Formwork accessories
            [
                'item_code' => 'FORM-PR-01',
                'name' => 'Prop',
                'category' => 'Formwork',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 3.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.30,
                'replacement_cost' => 600.00,
            ],
            [
                'item_code' => 'FORM-TR-15',
                'name' => 'Tie Rod 15/17mm (6.0m)',
                'category' => 'Formwork',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 1.80,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.18,
                'replacement_cost' => 260.00,
            ],
            [
                'item_code' => 'FORM-WN-01',
                'name' => 'Wing Nut / Water Stopper Nut',
                'category' => 'Formwork',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 0.80,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.08,
                'replacement_cost' => 95.00,
            ],
            [
                'item_code' => 'SCAF-SP-30',
                'name' => 'Steel Plank Perforated (3.0m)',
                'category' => 'Scaffolding',
                'unit_of_measure' => 'PCS',
                'market_rate_per_day' => 4.00,
                'eeig_discount_percent' => 25.00,
                'depreciation_rate_per_day' => 0.40,
                'replacement_cost' => 750.00,
            ],
        ];

        foreach ($catalog as $item) {
            Material::updateOrCreate(
                ['item_code' => $item['item_code']],
                $item
            );
        }
    }
}
