<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice {{ $invoice->invoice_no }} - EEC/EEIG Scaffolding & Formwork</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a202c;
        }
        body {
            background-color: #f7fafc;
            padding: 40px 20px;
        }
        .invoice-card {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .logo-title h1 {
            font-size: 22px;
            font-weight: 800;
            color: #d97706;
            letter-spacing: -0.5px;
        }
        .logo-title p {
            font-size: 12px;
            color: #718096;
            margin-top: 4px;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-meta h2 {
            font-size: 24px;
            color: #2d3748;
        }
        .invoice-meta p {
            font-size: 13px;
            color: #4a5568;
            margin-top: 2px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 16px;
            border-radius: 6px;
        }
        .box h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #718096;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .box p {
            font-size: 13px;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th {
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
        }
        td {
            padding: 10px 12px;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-wrap {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 35px;
        }
        .summary-table {
            width: 340px;
        }
        .summary-table td {
            padding: 8px 12px;
            font-size: 13px;
        }
        .grand-total td {
            border-top: 2px solid #2d3748;
            font-size: 16px;
            font-weight: bold;
            color: #b45309;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
            text-align: center;
        }
        .sign-line {
            height: 45px;
            border-bottom: 1px solid #94a3b8;
            margin-bottom: 8px;
        }
        .signatures p {
            font-size: 11px;
            color: #64748b;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print {
            background: #d97706;
            color: white;
            border: none;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-print:hover {
            background: #b45309;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .invoice-card {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
    </div>

    <div class="invoice-card">
        <div class="header-row">
            <div class="logo-title">
                <h1>EEC / EEIG CONSTRUCTION GROUP</h1>
                <p>Scaffolding & Formwork Division — Inter-Site Rental & Inventory Billing</p>
                <p style="margin-top: 4px; font-size: 11px; color: #94a3b8;">TIN: 0012345678 | VAT Reg: 987654321</p>
            </div>
            <div class="invoice-meta">
                <h2>RENTAL INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ $invoice->invoice_no }}</p>
                <p><strong>Status:</strong> <span style="text-transform: uppercase; font-weight: bold; color: {{ $invoice->payment_status === 'paid' ? '#059669' : '#d97706' }};">{{ $invoice->payment_status }}</span></p>
                <p><strong>Date Issued:</strong> {{ now()->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="details-grid">
            <div class="box">
                <h3>Billed To (Project Site)</h3>
                <p><strong>Site:</strong> [{{ $site?->code }}] {{ $site?->name }}</p>
                <p><strong>Client:</strong> {{ $site?->client }}</p>
                <p><strong>Location:</strong> {{ $site?->location ?? 'Site Project Yard' }}</p>
            </div>
            <div class="box">
                <h3>Rental Agreement Details</h3>
                <p><strong>Rental Contract #:</strong> {{ $rental?->rental_no }}</p>
                <p><strong>Rental Source:</strong> {{ $rental?->rental_source === 'external_vendor' ? 'External Vendor (' . ($rental->external_vendor_name ?? 'Commercial') . ')' : 'Central Store (Internal EEIG)' }}</p>
                <p><strong>Billing Period:</strong> {{ \Carbon\Carbon::parse($invoice->period_start)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($invoice->period_end)->format('M d, Y') }}</p>
                <p><strong>Duration:</strong> {{ $invoice->rental_days }} Billing Days</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Material Component</th>
                    <th class="text-center">Unit</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Market Rate/Day</th>
                    <th class="text-right">EEIG Disc %</th>
                    <th class="text-right">Effective Rate/Day</th>
                    <th class="text-right">Period Total (ETB)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                    @php
                        $days = $invoice->rental_days ?: 1;
                        $lineTotal = (float) $item->effective_rate_per_day * (float) $item->quantity * $days;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->material?->name }}</strong>
                            <div style="font-size: 10px; color: #94a3b8;">SKU: {{ $item->material?->item_code ?? 'N/A' }}</div>
                        </td>
                        <td class="text-center">{{ $item->material?->unit_of_measure }}</td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">ETB {{ number_format($item->unit_day_rate, 2) }}</td>
                        <td class="text-right">{{ number_format($item->eeig_discount_pct, 1) }}%</td>
                        <td class="text-right">ETB {{ number_format($item->effective_rate_per_day, 4) }}</td>
                        <td class="text-right"><strong>ETB {{ number_format($lineTotal, 2) }}</strong></td>
                    </tr>
                @empty
                    @if($rental && $rental->quantity_on_rent > 0)
                        <tr>
                            <td>1</td>
                            <td>
                                <strong>{{ $rental->material?->name ?? 'Scaffolding Equipment' }}</strong>
                                <div style="font-size: 10px; color: #94a3b8;">Rental Agreement: {{ $rental->rental_no }}</div>
                            </td>
                            <td class="text-center">Pcs</td>
                            <td class="text-right">{{ number_format($rental->quantity_on_rent, 2) }}</td>
                            <td class="text-right">ETB {{ number_format($rental->effective_daily_rate, 2) }}</td>
                            <td class="text-right">{{ number_format($rental->discount_percent_snapshot, 1) }}%</td>
                            <td class="text-right">ETB {{ number_format($rental->effective_daily_rate, 4) }}</td>
                            <td class="text-right"><strong>ETB {{ number_format($invoice->subtotal, 2) }}</strong></td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 20px; color: #94a3b8;">No equipment rental items found on this agreement.</td>
                        </tr>
                    @endif
                @endforelse
            </tbody>
        </table>

        <div class="summary-wrap">
            <table class="summary-table">
                <tr>
                    <td><strong>Gross Subtotal:</strong></td>
                    <td class="text-right">ETB {{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                    <tr>
                        <td style="color: #059669;"><strong>EEIG Rebate Discount:</strong></td>
                        <td class="text-right" style="color: #059669;">- ETB {{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>Taxable Base:</strong></td>
                    <td class="text-right">ETB {{ number_format(max(0, $invoice->subtotal - $invoice->discount_amount), 2) }}</td>
                </tr>
                <tr>
                    <td><strong>VAT (15.00%):</strong></td>
                    <td class="text-right">ETB {{ number_format($invoice->vat_amount, 2) }}</td>
                </tr>
                <tr class="grand-total">
                    <td><strong>Total Invoice:</strong></td>
                    <td class="text-right">ETB {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($invoice->notes)
            <div style="background: #f8fafc; border-left: 4px solid #d97706; padding: 12px; margin-bottom: 30px; font-size: 12px;">
                <strong>Notes / Instructions:</strong> {{ $invoice->notes }}
            </div>
        @endif

        <div class="signatures">
            <div>
                <div class="sign-line"></div>
                <p><strong>Prepared By</strong></p>
                <p>{{ $invoice->creator?->name ?? 'Site Storekeeper' }}</p>
            </div>
            <div>
                <div class="sign-line"></div>
                <p><strong>Project Engineer Approved</strong></p>
                <p>Site Representative</p>
            </div>
            <div>
                <div class="sign-line"></div>
                <p><strong>Finance & Audit Clearance</strong></p>
                <p>EEIG Commercial Division</p>
            </div>
        </div>
    </div>

</body>
</html>
