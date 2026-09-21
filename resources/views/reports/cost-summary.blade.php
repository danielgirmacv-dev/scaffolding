@extends('layouts.app')
@section('page-title', 'Cost Summary Report')
@section('page-subtitle', 'Period: ' . $period)
@section('topbar-actions')
    <form method="GET" style="display:flex;gap:8px;align-items:center;">
        <input type="month" name="period" value="{{ $period }}" class="form-control" style="width:160px;padding:7px 12px;">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>
@endsection
@section('content')

<div class="stats-grid fade-in" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
    <div class="stat-card blue">
        <div class="stat-icon blue"><i data-lucide="building-2" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">Sites Billed</div>
        <div class="stat-value">{{ $rows->where('total_items_rented','>',0)->count() }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon amber"><i data-lucide="percent" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">VAT Total</div>
        <div class="stat-value" style="font-size:20px;">{{ number_format($vatSum, 2) }}</div>
        <div class="stat-sub">ETB</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i data-lucide="circle-dollar-sign" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">Grand Total (incl. VAT)</div>
        <div class="stat-value" style="font-size:20px;">{{ number_format($grandTotal, 2) }}</div>
        <div class="stat-sub">ETB</div>
    </div>
</div>

<div class="card fade-in-2">
    <div class="card-header">
        <div>
            <div class="card-title">Cost Summary per Site — {{ $period }}</div>
            <div class="card-subtitle">Replaces "COST summary" &amp; "RENTAL COST AN." Excel sheets</div>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Site Code</th><th>Project Site</th><th>Client</th>
                    <th class="text-right">Items on Rent</th><th class="text-right">Subtotal (ETB)</th>
                    <th class="text-right">VAT 15%</th><th class="text-right">Grand Total (ETB)</th>
                </tr>
            </thead>
            <tbody>
                @php $grandSubtotal = 0; $grandVat = 0; $grandGrand = 0; @endphp
                @forelse($rows as $row)
                @php
                    $grandSubtotal += $row->subtotal_cost;
                    $grandVat += $row->vat_amount;
                    $grandGrand += $row->grand_total_cost;
                @endphp
                <tr>
                    <td><span class="badge badge-blue">{{ $row->site_code }}</span></td>
                    <td style="font-weight:600;">{{ $row->site_name }}</td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $row->client ?? '—' }}</td>
                    <td class="text-right">
                        @if($row->total_items_rented > 0)
                            <span class="badge badge-teal">{{ $row->total_items_rented }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-right font-mono">{{ $row->subtotal_cost > 0 ? number_format($row->subtotal_cost, 2) : '—' }}</td>
                    <td class="text-right font-mono" style="color:var(--accent-amber);">{{ $row->vat_amount > 0 ? number_format($row->vat_amount, 2) : '—' }}</td>
                    <td class="text-right font-mono font-bold {{ $row->grand_total_cost > 0 ? 'text-green' : 'text-muted' }}">
                        {{ $row->grand_total_cost > 0 ? number_format($row->grand_total_cost, 2) : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty-state">No rental data for {{ $period }}. Run: <code>php artisan inventory:compute-rentals {{ $period }}</code></td></tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
            <tfoot>
                <tr style="background:rgba(99,102,241,0.06);border-top:2px solid var(--border);">
                    <td colspan="4" style="padding:14px 16px;font-weight:700;font-size:13px;">COMPANY TOTAL</td>
                    <td class="text-right font-mono font-bold">{{ number_format($grandSubtotal, 2) }}</td>
                    <td class="text-right font-mono font-bold" style="color:var(--accent-amber);">{{ number_format($grandVat, 2) }}</td>
                    <td class="text-right font-mono font-bold text-green" style="font-size:15px;">{{ number_format($grandGrand, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
