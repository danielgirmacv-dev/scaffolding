<?php

namespace App\Console\Commands;

use App\Actions\Inventory\CalculateRentalCost;
use Illuminate\Console\Command;

class ComputeMonthlyRentals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:compute-rentals {period? : Billing month in YYYY-MM format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and snapshot monthly inter-project scaffolding rental costs across all sites';

    /**
     * Execute the console command.
     */
    public function handle(CalculateRentalCost $calculator): int
    {
        $period = $this->argument('period') ?: now()->format('Y-m');

        $this->info("Computing rental costs for billing period: {$period}");

        $rentals = $calculator->computeForPeriod($period);

        $this->info("Generated {$rentals->count()} rental line items.");

        $totalCost = $rentals->sum('grand_total_cost');
        $this->info('Total Grand Rental Cost (incl. 15% VAT): ETB '.number_format($totalCost, 2));

        return self::SUCCESS;
    }
}
