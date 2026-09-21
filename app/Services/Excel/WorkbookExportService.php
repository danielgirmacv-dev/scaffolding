<?php

namespace App\Services\Excel;

use App\Services\Reports\ConsolidationReportService;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class WorkbookExportService
{
    public function __construct(
        protected ConsolidationReportService $reportService
    ) {}

    /**
     * Export the consolidated company stock balance matrix to an XLSX file.
     */
    public function exportCompanyBalance(string $outputPath, ?string $asOfDate = null): string
    {
        $data = $this->reportService->getCompanyBalanceReport($asOfDate);
        $writer = new Writer;
        $writer->openToFile($outputPath);

        // Header row: Material Name | UoM | Category | [Each Site Code] | Total Stock
        $headerCells = ['Item Code', 'Material Description', 'UoM', 'Category'];
        foreach ($data['sites'] as $site) {
            $headerCells[] = $site->code.($site->is_central_store ? ' (CS)' : '');
        }
        $headerCells[] = 'Company Total';
        $writer->addRow(Row::fromValues($headerCells));

        // Data rows
        foreach ($data['materials'] as $mat) {
            $rowValues = [
                $mat->item_code ?? '',
                $mat->name,
                $mat->unit_of_measure,
                $mat->category,
            ];

            foreach ($data['sites'] as $site) {
                $rowValues[] = $data['matrix'][$mat->id][$site->id] ?? 0.0;
            }

            $rowValues[] = $data['totals'][$mat->id] ?? 0.0;
            $writer->addRow(Row::fromValues($rowValues));
        }

        $writer->close();

        return $outputPath;
    }

    /**
     * Export the monthly rental cost summary per site.
     */
    public function exportCostSummary(string $outputPath, string $billingPeriod): string
    {
        $rows = $this->reportService->getCostSummaryReport($billingPeriod);
        $writer = new Writer;
        $writer->openToFile($outputPath);

        $writer->addRow(Row::fromValues([
            'Site Code',
            'Project Site Name',
            'Client',
            'Items on Rent',
            'Subtotal Cost (ETB)',
            'VAT 15% (ETB)',
            'Grand Total (ETB)',
        ]));

        $sumSubtotal = 0;
        $sumVat = 0;
        $sumGrand = 0;

        foreach ($rows as $row) {
            $sumSubtotal += $row->subtotal_cost;
            $sumVat += $row->vat_amount;
            $sumGrand += $row->grand_total_cost;

            $writer->addRow(Row::fromValues([
                $row->site_code,
                $row->site_name,
                $row->client ?? 'Internal/EEIG',
                $row->total_items_rented,
                number_format($row->subtotal_cost, 2, '.', ''),
                number_format($row->vat_amount, 2, '.', ''),
                number_format($row->grand_total_cost, 2, '.', ''),
            ]));
        }

        // Summary Total row
        $writer->addRow(Row::fromValues([
            'TOTAL',
            'ALL ACTIVE SITES',
            '',
            '',
            number_format($sumSubtotal, 2, '.', ''),
            number_format($sumVat, 2, '.', ''),
            number_format($sumGrand, 2, '.', ''),
        ]));

        $writer->close();

        return $outputPath;
    }
}
