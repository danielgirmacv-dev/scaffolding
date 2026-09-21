<?php

namespace App\Services\Excel;

use App\Actions\Inventory\RecordMaterialTransaction;
use App\Models\Material;
use App\Models\Site;
use App\Models\StockAnomaly;
use Carbon\Carbon;
use Illuminate\Support\Str;
use OpenSpout\Reader\XLSX\Reader;

class WorkbookImportService
{
    protected const ROLLUP_SHEETS = [
        'COMPANY BALANCE',
        'COMPANYBAL',
        'RENTAL COST AN.',
        'RENTAL COST',
        'RENTAL COST AN',
        'COST SUMMARY',
        'COSTSUMMARY',
        'SUMMARY',
        'TOTALS',
    ];

    public function __construct(
        protected RecordMaterialTransaction $recordTransactionAction
    ) {}

    /**
     * Ingest a multi-sheet Excel workbook and import site transactions into single database.
     *
     * @param  string  $filePath  Absolute path to XLSX workbook
     * @return array{
     *     sites_processed: int,
     *     transactions_created: int,
     *     anomalies_flagged: int,
     *     errors: array<int, string>
     * }
     */
    public function importWorkbook(string $filePath): array
    {
        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("Workbook file not found at: {$filePath}");
        }

        $reader = new Reader;
        $reader->open($filePath);

        $sitesCount = 0;
        $txCount = 0;
        $anomaliesCount = 0;
        $errors = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            $sheetName = trim($sheet->getName());
            $normalizedSheetName = strtoupper($sheetName);

            if ($this->isRollupSheet($normalizedSheetName)) {
                continue;
            }

            // Provision or find site
            $isCentral = str_contains($normalizedSheetName, 'CENTRAL') || str_contains($normalizedSheetName, 'STORE') || $normalizedSheetName === 'CS';
            $site = Site::firstOrCreate(
                ['code' => $sheetName],
                [
                    'name' => $isCentral ? 'Central Store' : 'Project Site '.$sheetName,
                    'is_central_store' => $isCentral,
                    'status' => 'active',
                ]
            );

            $sitesCount++;
            $headerMap = [];
            $rowNumber = 0;

            foreach ($sheet->getRowIterator() as $row) {
                $rowNumber++;
                $cells = array_map(fn ($cell) => trim((string) $cell->getValue()), $row->getCells());

                // Detect header row
                if (empty($headerMap)) {
                    $lowerCells = array_map(fn ($c) => strtolower(preg_replace('/[^a-z0-9]/', '', $c)), $cells);

                    if ($this->isHeaderRow($lowerCells)) {
                        $headerMap = $this->buildHeaderMap($lowerCells);

                        continue;
                    }
                }

                if (empty($headerMap)) {
                    continue;
                }

                // Process data row
                try {
                    $itemCode = $this->getVal($cells, $headerMap, 'item_code');
                    $description = $this->getVal($cells, $headerMap, 'description');
                    $qtyIn = (float) $this->getVal($cells, $headerMap, 'qty_in');
                    $qtyOut = (float) $this->getVal($cells, $headerMap, 'qty_out');
                    $refNo = $this->getVal($cells, $headerMap, 'ref_no');
                    $dateRaw = $this->getVal($cells, $headerMap, 'date');
                    $uom = $this->getVal($cells, $headerMap, 'uom') ?: 'Pcs';
                    $unitPrice = (float) $this->getVal($cells, $headerMap, 'unit_price');

                    if ($itemCode === '' && $description === '') {
                        continue;
                    }

                    $matName = $description ?: $itemCode;
                    $material = Material::firstOrCreate(
                        ['name' => $matName],
                        [
                            'item_code' => $itemCode ?: Str::slug($matName),
                            'unit_of_measure' => $uom,
                            'market_rate_per_day' => $unitPrice > 0 ? $unitPrice : 0.0,
                            'eeig_discount_percent' => 25.00,
                            'category' => 'Scaffolding',
                            'is_active' => true,
                        ]
                    );

                    $txDate = $this->parseDate($dateRaw);

                    // Record IN movement if present
                    if ($qtyIn > 0) {
                        $this->recordTransactionAction->execute([
                            'site_id' => $site->id,
                            'material_id' => $material->id,
                            'direction' => 'in',
                            'quantity' => $qtyIn,
                            'ref_no' => $refNo ?: 'IMPORT-ROW-'.$rowNumber,
                            'transaction_date' => $txDate,
                            'notes' => 'Imported from workbook sheet '.$sheetName.' row '.$rowNumber,
                            'source_sheet' => $sheetName,
                            'source_row' => $rowNumber,
                            'allow_negative_as_anomaly' => true,
                        ]);
                        $txCount++;
                    }

                    // Record OUT movement if present
                    if ($qtyOut > 0) {
                        $this->recordTransactionAction->execute([
                            'site_id' => $site->id,
                            'material_id' => $material->id,
                            'direction' => 'out',
                            'quantity' => $qtyOut,
                            'ref_no' => $refNo ?: 'IMPORT-ROW-'.$rowNumber,
                            'transaction_date' => $txDate,
                            'notes' => 'Imported from workbook sheet '.$sheetName.' row '.$rowNumber,
                            'source_sheet' => $sheetName,
                            'source_row' => $rowNumber,
                            'allow_negative_as_anomaly' => true,
                        ]);
                        $txCount++;
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Sheet {$sheetName} Row {$rowNumber}: ".$e->getMessage();
                }
            }
        }

        $reader->close();

        return [
            'sites_processed' => $sitesCount,
            'transactions_created' => $txCount,
            'anomalies_flagged' => StockAnomaly::count(),
            'errors' => $errors,
        ];
    }

    protected function isRollupSheet(string $sheetName): bool
    {
        return in_array($sheetName, self::ROLLUP_SHEETS, true);
    }

    /**
     * @param  list<string>  $cells
     */
    protected function isHeaderRow(array $cells): bool
    {
        $joined = implode(' ', $cells);

        return str_contains($joined, 'item') || str_contains($joined, 'itm') || str_contains($joined, 'description') || str_contains($joined, 'material');
    }

    /**
     * @param  list<string>  $cells
     * @return array<string, int>
     */
    protected function buildHeaderMap(array $cells): array
    {
        $map = [];
        foreach ($cells as $idx => $header) {
            if (str_contains($header, 'itemno') || str_contains($header, 'itmno') || str_contains($header, 'code')) {
                $map['item_code'] = $idx;
            } elseif (str_contains($header, 'description') || str_contains($header, 'material') || str_contains($header, 'desc') || str_contains($header, 'itemname')) {
                $map['description'] = $idx;
            } elseif (str_contains($header, 'qtyin') || str_contains($header, 'in') || str_contains($header, 'received') || str_contains($header, 'delivered')) {
                if (! isset($map['qty_in'])) {
                    $map['qty_in'] = $idx;
                }
            } elseif (str_contains($header, 'qtyout') || str_contains($header, 'out') || str_contains($header, 'issued') || str_contains($header, 'returned')) {
                if (! isset($map['qty_out'])) {
                    $map['qty_out'] = $idx;
                }
            } elseif (str_contains($header, 'ref') || str_contains($header, 'waybill') || str_contains($header, 'siv') || str_contains($header, 'pad')) {
                $map['ref_no'] = $idx;
            } elseif (str_contains($header, 'date')) {
                $map['date'] = $idx;
            } elseif (str_contains($header, 'unit') || str_contains($header, 'uom')) {
                $map['uom'] = $idx;
            } elseif (str_contains($header, 'price') || str_contains($header, 'rate')) {
                $map['unit_price'] = $idx;
            }
        }

        return $map;
    }

    /**
     * @param  list<string>  $cells
     * @param  array<string, int>  $headerMap
     */
    protected function getVal(array $cells, array $headerMap, string $key): string
    {
        if (isset($headerMap[$key]) && isset($cells[$headerMap[$key]])) {
            return trim((string) $cells[$headerMap[$key]]);
        }

        return '';
    }

    protected function parseDate(string $raw): string
    {
        if (empty($raw)) {
            return now()->toDateString();
        }

        try {
            return Carbon::parse($raw)->toDateString();
        } catch (\Throwable) {
            return now()->toDateString();
        }
    }
}
