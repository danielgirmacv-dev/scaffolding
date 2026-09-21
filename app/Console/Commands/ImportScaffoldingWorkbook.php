<?php

namespace App\Console\Commands;

use App\Services\Excel\WorkbookImportService;
use Illuminate\Console\Command;

class ImportScaffoldingWorkbook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:import-workbook {file : Path to the multi-sheet XLSX file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import scaffolding workbook sheets into the single database ledger and flag anomalies';

    /**
     * Execute the console command.
     */
    public function handle(WorkbookImportService $importService): int
    {
        $filePath = (string) $this->argument('file');

        if (! file_exists($filePath)) {
            $this->error("File does not exist at: {$filePath}");

            return self::FAILURE;
        }

        $this->info("Starting workbook ingestion from: {$filePath}");

        $result = $importService->importWorkbook($filePath);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Sites Processed', $result['sites_processed']],
                ['Transactions Created', $result['transactions_created']],
                ['Anomalies Flagged (Negative Balances)', $result['anomalies_flagged']],
            ]
        );

        if (! empty($result['errors'])) {
            $this->warn('Encountered non-fatal errors during parsing:');
            foreach (array_slice($result['errors'], 0, 10) as $err) {
                $this->line(" - {$err}");
            }
        }

        $this->info('Import completed successfully.');

        return self::SUCCESS;
    }
}
