<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Align legacy codes
        if ($legacyCs = Site::whereIn('code', ['CS', 'C/STORE'])->first()) {
            $legacyCs->update([
                'code' => 'CENTRAL STORE',
                'name' => 'Central Store',
                'is_central_store' => true,
            ]);
        }

        if ($legacyChaka = Site::where('code', 'CHAKA')->first()) {
            $legacyChaka->update(['code' => 'SCAFF-CHAKA', 'name' => 'SCAFF-CHAKA (sub-store)']);
        }

        if ($legacyGie1 = Site::where('code', 'GIE I')->first()) {
            $legacyGie1->update(['code' => 'GIE', 'name' => 'GIE']);
        }

        if ($legacySorga = Site::where('code', 'SORGA')->first()) {
            $legacySorga->update(['code' => 'SLJE/SORGA', 'name' => 'SLJE/SORGA']);
        }

        if ($legacy7Adv = Site::where('code', '7 ADVENTIST')->first()) {
            $legacy7Adv->update(['code' => '7-ADVENTIST', 'name' => '7-ADVENTIST']);
        }

        if ($legacyCbHs = Site::where('code', 'CB / HS')->first()) {
            $legacyCbHs->update(['code' => 'CB', 'name' => 'CB']);
        }

        $sites = [
            ['code' => 'EPU', 'name' => 'Ethiopia Police University', 'client' => 'Ethiopian Police University', 'location' => 'Addis Ababa East', 'is_central_store' => false],
            ['code' => 'EPU 2', 'name' => 'Ethiopia Police University Site 2', 'client' => 'Ethiopian Police University', 'location' => 'Sendafa / East Shewa', 'is_central_store' => false],
            ['code' => 'EPSE', 'name' => 'National Petroleum', 'client' => 'Ethiopian Petroleum Supply Enterprise', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'EAG', 'name' => 'Ethiopian Airlines Group', 'client' => 'Ethiopian Airlines', 'location' => 'Bole, Addis Ababa', 'is_central_store' => false],
            ['code' => 'EIH', 'name' => 'Ethiopian Investment Holding', 'client' => 'Ethiopian Investment Holdings', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'MOJ II', 'name' => 'Ministry of Justice Site 2', 'client' => 'Federal Ministry of Justice', 'location' => 'Arat Kilo, Addis Ababa', 'is_central_store' => false],
            ['code' => 'MOJ III', 'name' => 'Ministry of Justice Site 3', 'client' => 'Federal Ministry of Justice', 'location' => 'Arat Kilo, Addis Ababa', 'is_central_store' => false],
            ['code' => 'MoWSA', 'name' => 'Ministry of Women & Social Affairs', 'client' => 'Federal Ministry of Women & Social Affairs', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'NBE', 'name' => 'National Bank of Ethiopia', 'client' => 'National Bank of Ethiopia', 'location' => 'Sudan Street, Kirkos', 'is_central_store' => false],
            ['code' => 'NBE 2', 'name' => 'National Bank of Ethiopia Site 2', 'client' => 'National Bank of Ethiopia', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'ESL', 'name' => 'ESL', 'client' => 'Ethiopian Shipping & Logistics', 'location' => 'Addis Ababa / Kality', 'is_central_store' => false],
            ['code' => 'ESL KDP', 'name' => 'ESL KDP', 'client' => 'Ethiopian Shipping & Logistics', 'location' => 'Kality Dry Port', 'is_central_store' => false],
            ['code' => 'GLP', 'name' => 'GLP', 'client' => 'Ethiopian Shipping & Logistics', 'location' => 'Gelan Industrial Zone', 'is_central_store' => false],
            ['code' => 'GIE', 'name' => 'GIE', 'client' => 'GIE', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'GIE II', 'name' => 'GIE II', 'client' => 'GIE', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'GIE 3', 'name' => 'GIE 3', 'client' => 'GIE', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'SLJE/SORGA', 'name' => 'SLJE/SORGA', 'client' => 'SLJE / SORGA', 'location' => 'Nekemte / Oromia', 'is_central_store' => false],
            ['code' => 'FANA', 'name' => 'FANA', 'client' => 'Fana Broadcasting Corporate', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'BPD', 'name' => 'BPD', 'client' => 'BPD', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'ETTE', 'name' => 'ETTE', 'client' => 'ETTE', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'CMI', 'name' => 'CMI', 'client' => 'CMI', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'SPS', 'name' => 'SPS', 'client' => 'SPS', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'EPA-DB', 'name' => 'EPA-DB', 'client' => 'Environmental Protection Authority', 'location' => 'Debre Berhan', 'is_central_store' => false],
            ['code' => 'SHPR', 'name' => 'SHPR', 'client' => 'SHPR', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'JIMMA', 'name' => 'JIMMA (out-of-Addis)', 'client' => 'Jimma Municipal / EEIG', 'location' => 'Jimma, Oromia (Out-of-Addis)', 'is_central_store' => false],
            ['code' => 'FHC', 'name' => 'FHC', 'client' => 'Federal Housing Corporation', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'POESSA', 'name' => 'POESSA', 'client' => 'Public Servants Social Security Agency', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'EEU', 'name' => 'EEU', 'client' => 'Ethiopian Electric Utility', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => '7-ADVENTIST', 'name' => '7-ADVENTIST', 'client' => 'Seventh-day Adventist Church', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'SCAFF', 'name' => 'SCAFF (sub-store)', 'client' => 'EEIG Internal Sub-Store', 'location' => 'Central Sub-Store Yard', 'is_central_store' => false],
            ['code' => 'SCAFF-CHAKA', 'name' => 'SCAFF-CHAKA (sub-store)', 'client' => 'EEIG Internal Sub-Store', 'location' => 'Chaka Intermediate Depot', 'is_central_store' => false],
            ['code' => 'CB', 'name' => 'CB', 'client' => 'Commercial Bank of Ethiopia', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'HS', 'name' => 'HS', 'client' => 'High School Project', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'CENTRAL STORE', 'name' => 'Central Store', 'client' => 'EEIG / Internal', 'location' => 'Kality Central Yard', 'is_central_store' => true],
            ['code' => 'BCDS-2', 'name' => 'BCDS-2', 'client' => 'BCDS', 'location' => 'Addis Ababa', 'is_central_store' => false],
            ['code' => 'PRODUCTION', 'name' => 'Internal Production', 'client' => 'EEIG Internal Production', 'location' => 'Kality Production Facility', 'is_central_store' => false],
        ];

        foreach ($sites as $data) {
            $data['status'] = 'active';
            Site::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
