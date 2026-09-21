<?php

namespace App\Console\Commands;

use App\Models\StockAnomaly;
use Illuminate\Console\Command;

class AuditNegativeBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:audit-balances {--status=open : Filter anomalies by status (open, all, resolved)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit negative running balance flags and data entry anomalies';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $status = $this->option('status');

        $query = StockAnomaly::with(['site', 'material']);
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $anomalies = $query->orderBy('id', 'desc')->get();

        if ($anomalies->isEmpty()) {
            $this->info("No {$status} negative balance anomalies found. Inventory ledger is consistent.");

            return self::SUCCESS;
        }

        $this->warn("Found {$anomalies->count()} {$status} stock anomalies:");

        $tableData = $anomalies->map(fn ($a) => [
            $a->id,
            $a->source_sheet,
            $a->source_row ?? 'N/A',
            $a->site?->name ?? 'Unknown',
            $a->material?->name ?? 'Unknown',
            $a->calculated_negative_balance,
            $a->status,
            $a->message,
        ]);

        $this->table(
            ['ID', 'Sheet', 'Row', 'Site', 'Material', 'Deficit Balance', 'Status', 'Message'],
            $tableData
        );

        return self::SUCCESS;
    }
}
