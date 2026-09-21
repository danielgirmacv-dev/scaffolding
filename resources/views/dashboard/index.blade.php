@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'EEIG / EEC Scaffolding & Formwork System')

@section('topbar-actions')
    <a href="{{ route('transactions.index') }}" class="btn btn-outline btn-sm">
        <i data-lucide="list" style="width:14px;height:14px;"></i> All Movements
    </a>
    <a href="{{ route('reports.cost-summary') }}?period={{ now()->format('Y-m') }}" class="btn btn-primary btn-sm">
        <i data-lucide="file-spreadsheet" style="width:14px;height:14px;"></i> Monthly Report
    </a>
@endsection

@section('content')

<!-- ── KPI Stats ──────────────────────────────────────── -->
<div class="stats-grid fade-in">
    <div class="stat-card purple">
        <div class="stat-icon purple"><i data-lucide="building-2" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">Active Project Sites</div>
        <div class="stat-value">{{ $totalSites }}</div>
        <div class="stat-sub">+ Central Store</div>
    </div>

    <div class="stat-card teal">
        <div class="stat-icon teal"><i data-lucide="package" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">Materials in Catalog</div>
        <div class="stat-value">{{ $totalMaterials }}</div>
        <div class="stat-sub">Scaffolding &amp; Formwork</div>
    </div>

    <div class="stat-card blue">
        <div class="stat-icon blue"><i data-lucide="arrow-left-right" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">Total Movements</div>
        <div class="stat-value">{{ number_format($totalTransactions) }}</div>
        <div class="stat-sub">Approved transactions</div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon green"><i data-lucide="receipt" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">Current Month Rental</div>
        <div class="stat-value">{{ number_format($monthlyRentalTotal / 1000, 1) }}K</div>
        <div class="stat-sub">ETB {{ number_format($monthlyRentalTotal, 2) }} incl. VAT</div>
        @if($lastMonthRentalTotal > 0)
            @php $delta = $monthlyRentalTotal - $lastMonthRentalTotal; @endphp
            <span class="stat-change {{ $delta >= 0 ? 'up' : 'down' }}">
                <i data-lucide="{{ $delta >= 0 ? 'trending-up' : 'trending-down' }}" style="width:11px;height:11px;"></i>
                {{ number_format(abs($delta / max($lastMonthRentalTotal, 1) * 100), 1) }}% vs last month
            </span>
        @endif
    </div>

    @if($openAnomalies > 0)
    <div class="stat-card red">
        <div class="stat-icon red"><i data-lucide="alert-triangle" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">Open Anomalies</div>
        <div class="stat-value">{{ $openAnomalies }}</div>
        <div class="stat-sub"><a href="{{ route('reports.anomalies') }}" style="color:var(--accent-red);">View audit trail →</a></div>
    </div>
    @else
    <div class="stat-card green">
        <div class="stat-icon green"><i data-lucide="shield-check" style="width:20px;height:20px;"></i></div>
        <div class="stat-label">Ledger Health</div>
        <div class="stat-value" style="font-size:20px;">Clean ✓</div>
        <div class="stat-sub">No negative balances</div>
    </div>
    @endif
</div>

<!-- ── Middle Row ─────────────────────────────────────── -->
<div class="grid-2 fade-in-2">

    <!-- Site Overview -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Site Overview</div>
                <div class="card-subtitle">{{ $currentPeriod }} · Active project sites</div>
            </div>
            <a href="{{ route('sites.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Client</th>
                        <th class="text-right">Items on Site</th>
                        <th class="text-right">Rental (ETB)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siteSummaries as $s)
                    <tr>
                        <td>
                            <a href="{{ route('sites.show', $s['site']) }}" style="text-decoration:none;color:inherit;">
                                <div style="font-weight:600;">{{ $s['site']->code }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ Str::limit($s['site']->name, 28) }}</div>
                            </a>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);">{{ Str::limit($s['site']->client ?? '—', 20) }}</td>
                        <td class="text-right">
                            <span class="badge badge-blue">{{ $s['total_items'] }}</span>
                        </td>
                        <td class="text-right font-mono">
                            {{ $s['rental_cost'] > 0 ? number_format($s['rental_cost'], 2) : '—' }}
                        </td>
                        <td>
                            @php
                                $color = match($s['site']->status) {
                                    'active'    => 'badge-green',
                                    'completed' => 'badge-gray',
                                    'suspended' => 'badge-amber',
                                    default     => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $color }}">{{ ucfirst($s['site']->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="empty-state">No active sites found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Materials by Volume -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Top Materials by Volume</div>
                <div class="card-subtitle">Most-moved across all sites</div>
            </div>
            <a href="{{ route('materials.index') }}" class="btn btn-outline btn-sm">Full Catalog</a>
        </div>
        <div class="card-body">
            @foreach($topMaterials as $mat)
            @php $totalMoved = $mat->total_in + $mat->total_out; @endphp
            <div style="margin-bottom:18px;">
                <div class="flex-between mb-4" style="margin-bottom:8px;">
                    <div>
                        <div style="font-size:13px;font-weight:600;">{{ $mat->name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $mat->item_code }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:13px;font-weight:600;color:var(--accent-green);">↑ {{ number_format($mat->total_in) }}</div>
                        <div style="font-size:11px;color:var(--accent-amber);">↓ {{ number_format($mat->total_out) }} {{ $mat->unit_of_measure }}</div>
                    </div>
                </div>
                <div class="progress-bar-wrap">
                    @php
                        $pct = $totalMoved > 0 ? ($mat->total_in / max($mat->total_in, 1)) * 100 : 0;
                        $colors = ['#6366f1','#10b981','#f59e0b','#3b82f6','#a855f7'];
                        $clr = $colors[$loop->index % count($colors)];
                    @endphp
                    <div class="progress-bar-fill" style="width:{{ min(100, $pct) }}%;background:{{ $clr }};"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ── Recent Movements ───────────────────────────────── -->
<div class="card fade-in-3 mt-6" style="margin-top:20px;">
    <div class="card-header">
        <div>
            <div class="card-title">Recent Stock Movements</div>
            <div class="card-subtitle">Latest 8 approved transactions across all sites</div>
        </div>
        <a href="{{ route('transactions.index') }}" class="btn btn-outline btn-sm">Full Ledger</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Ref No.</th>
                    <th>Date</th>
                    <th>Site</th>
                    <th>Material</th>
                    <th>Direction</th>
                    <th class="text-right">Qty</th>
                    <th>Transfer Route</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $tx)
                <tr>
                    <td class="font-mono" style="color:var(--text-muted);">{{ $tx->transaction_no }}</td>
                    <td style="font-size:12px;">{{ $tx->transaction_date->format('d M Y') }}</td>
                    <td>
                        <span class="badge badge-blue">{{ $tx->site->code }}</span>
                    </td>
                    <td style="font-size:12px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $tx->material->name }}
                    </td>
                    <td>
                        @php
                            $dirStyles = [
                                'in'           => ['badge-green', '↓ IN'],
                                'out'          => ['badge-red', '↑ OUT'],
                                'transfer_in'  => ['badge-teal', '⇐ T.IN'],
                                'transfer_out' => ['badge-amber', '⇒ T.OUT'],
                                'damaged'      => ['badge-red', '✕ DMG'],
                                'lost'         => ['badge-red', '✕ LOST'],
                                'adjustment'   => ['badge-purple', '± ADJ'],
                            ];
                            [$cls, $label] = $dirStyles[$tx->direction] ?? ['badge-gray', $tx->direction];
                        @endphp
                        <span class="badge {{ $cls }}">{{ $label }}</span>
                    </td>
                    <td class="text-right font-bold">{{ number_format($tx->quantity, 0) }}</td>
                    <td style="font-size:11px;color:var(--text-muted);">
                        @if($tx->fromSite && $tx->toSite)
                            {{ $tx->fromSite->code }} → {{ $tx->toSite->code }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty-state">No transactions recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
