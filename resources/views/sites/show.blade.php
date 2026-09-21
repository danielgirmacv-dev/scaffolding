@extends('layouts.app')
@section('page-title', $site->code . ' — Site Stock Balance')
@section('page-subtitle', $site->name)
@section('content')

<div class="stats-grid fade-in" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));">
    <div class="stat-card blue">
        <div class="stat-icon blue"><i data-lucide="building-2" style="width:18px;height:18px;"></i></div>
        <div class="stat-label">Site Code</div>
        <div class="stat-value" style="font-size:22px;">{{ $site->code }}</div>
        <div class="stat-sub">{{ $site->is_central_store ? 'Central Warehouse' : 'Project Site' }}</div>
    </div>
    <div class="stat-card teal">
        <div class="stat-icon teal"><i data-lucide="users" style="width:18px;height:18px;"></i></div>
        <div class="stat-label">Client</div>
        <div class="stat-value" style="font-size:14px;line-height:1.4;margin-top:4px;">{{ $site->client ?? '—' }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i data-lucide="receipt" style="width:18px;height:18px;"></i></div>
        <div class="stat-label">Monthly Rental ({{ $currentPeriod }})</div>
        <div class="stat-value" style="font-size:18px;">{{ number_format($monthlyRental, 2) }}</div>
        <div class="stat-sub">ETB incl. 15% VAT</div>
    </div>
    <div class="stat-card {{ $site->anomalies->count() > 0 ? 'red' : 'purple' }}">
        <div class="stat-icon {{ $site->anomalies->count() > 0 ? 'red' : 'purple' }}">
            <i data-lucide="{{ $site->anomalies->count() > 0 ? 'alert-triangle' : 'shield-check' }}" style="width:18px;height:18px;"></i>
        </div>
        <div class="stat-label">Open Anomalies</div>
        <div class="stat-value">{{ $site->anomalies->count() }}</div>
        <div class="stat-sub">{{ $site->anomalies->count() === 0 ? 'Ledger clean' : 'Requires review' }}</div>
    </div>
</div>

<div class="card fade-in-2">
    <div class="card-header">
        <div>
            <div class="card-title">Current Stock Balance — All Materials</div>
            <div class="card-subtitle">As of today · Derived from immutable ledger · Never stored as mutable column</div>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Item Code</th><th>Material Description</th><th>Category</th>
                    <th>UoM</th><th class="text-right">Balance</th><th>Health</th>
                </tr>
            </thead>
            <tbody>
                @forelse($balanceData as $row)
                <tr>
                    <td class="font-mono" style="color:var(--text-muted);">{{ $row->item_code ?? '—' }}</td>
                    <td style="font-weight:500;">{{ $row->name }}</td>
                    <td><span class="badge badge-purple">{{ $row->category }}</span></td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $row->unit_of_measure }}</td>
                    <td class="text-right font-bold {{ $row->balance < 0 ? 'text-red' : ($row->balance === 0.0 ? 'text-muted' : 'text-green') }}">
                        {{ number_format($row->balance, 0) }}
                    </td>
                    <td>
                        @if($row->balance < 0)
                            <span class="badge badge-red">⚠ Deficit</span>
                        @elseif($row->balance == 0)
                            <span class="badge badge-gray">Empty</span>
                        @else
                            <span class="badge badge-green">OK</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty-state">No inventory data for this site.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
