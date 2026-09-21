@extends('layouts.app')
@section('page-title', 'Rental Records')
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
        <div class="stat-icon blue"><i data-lucide="calculator" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">Subtotal (before VAT)</div>
        <div class="stat-value" style="font-size:22px;">{{ number_format($subtotalSum, 2) }}</div>
        <div class="stat-sub">ETB</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon amber"><i data-lucide="percent" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">VAT 15%</div>
        <div class="stat-value" style="font-size:22px;">{{ number_format($vatSum, 2) }}</div>
        <div class="stat-sub">ETB</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i data-lucide="receipt" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">Grand Total</div>
        <div class="stat-value" style="font-size:22px;">{{ number_format($grandTotal, 2) }}</div>
        <div class="stat-sub">ETB incl. VAT</div>
    </div>
</div>

<div class="card fade-in-2">
    <div class="card-header">
        <div>
            <div class="card-title">Rental Line Items — {{ $period }}</div>
            <div class="card-subtitle">{{ $rentals->total() }} items · Rates snapshotted at time of billing</div>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Rental No.</th><th>Site</th><th>Material</th><th>Period</th>
                    <th class="text-right">Days</th><th class="text-right">Qty</th>
                    <th class="text-right">Eff. Rate</th><th class="text-right">Subtotal</th>
                    <th class="text-right">VAT</th><th class="text-right">Grand Total</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rentals as $rental)
                <tr>
                    <td class="font-mono" style="font-size:11px;color:var(--text-muted);">{{ $rental->rental_no }}</td>
                    <td><span class="badge badge-blue">{{ $rental->site->code }}</span></td>
                    <td style="font-size:12px;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $rental->material->name }}</td>
                    <td style="font-size:12px;">{{ $rental->start_date->format('d M') }} – {{ $rental->end_date?->format('d M Y') ?? '…' }}</td>
                    <td class="text-right">{{ $rental->days_used }}</td>
                    <td class="text-right">{{ number_format($rental->quantity_on_rent, 0) }}</td>
                    <td class="text-right font-mono" style="font-size:12px;">{{ number_format($rental->effective_daily_rate, 4) }}</td>
                    <td class="text-right font-mono font-bold">{{ number_format($rental->subtotal_cost, 2) }}</td>
                    <td class="text-right font-mono" style="color:var(--accent-amber);">{{ number_format($rental->vat_amount, 2) }}</td>
                    <td class="text-right font-mono font-bold text-green">{{ number_format($rental->grand_total_cost, 2) }}</td>
                    <td><span class="badge badge-{{ $rental->status === 'approved' ? 'green' : 'amber' }}">{{ ucfirst($rental->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="11" class="empty-state">No rental records for this period. Run: <code>php artisan inventory:compute-rentals {{ $period }}</code></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--border);">{{ $rentals->withQueryString()->links() }}</div>
</div>
@endsection
