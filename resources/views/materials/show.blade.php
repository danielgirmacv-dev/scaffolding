@extends('layouts.app')
@section('title', $material->name . ' — Material Details')
@section('page-title', $material->name)
@section('page-subtitle', 'Item Code: ' . $material->item_code . ' · Category: ' . $material->category)

@section('topbar-actions')
    <a href="{{ route('materials.index') }}" class="btn btn-outline btn-sm">
        <i data-lucide="arrow-left" style="width:14px; height:14px; margin-right:4px;"></i> Back to Catalog
    </a>
@endsection

@section('content')
<div class="stats-grid fade-in" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-label">Market Rental / Day</div>
        <div class="stat-value text-blue font-mono">{{ number_format($material->market_rate_per_day, 4) }}</div>
        <div class="stat-sub">Base standard tariff (ETB)</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">EEIG Discount</div>
        <div class="stat-value text-amber font-mono">{{ $material->eeig_discount_percent }}%</div>
        <div class="stat-sub">Standard internal discount</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Effective Daily Rate</div>
        <div class="stat-value text-green font-mono">{{ number_format($material->effective_daily_rate, 4) }}</div>
        <div class="stat-sub">After internal discount (ETB)</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Replacement Cost</div>
        <div class="stat-value font-mono">{{ number_format($material->replacement_cost, 2) }}</div>
        <div class="stat-sub">Per {{ $material->unit_of_measure }}</div>
    </div>
</div>

<div class="card fade-in">
    <div class="card-header">
        <div>
            <div class="card-title">Recent Approved Transactions</div>
            <div class="card-subtitle">Last 20 movements involving {{ $material->name }}</div>
        </div>
        <div>
            <span class="badge badge-purple">{{ $material->unit_of_measure }}</span>
        </div>
    </div>

    @if($transactions->isEmpty())
        <div style="text-align:center; padding: 48px 16px;">
            <p style="color:var(--text-secondary); font-size:13px;">No approved transactions found for this material.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Site</th>
                        <th>Type</th>
                        <th>Pad / Ref #</th>
                        <th class="text-right">Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                        <tr>
                            <td class="font-mono text-xs">{{ $tx->transaction_date?->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('sites.show', $tx->site_id) }}" style="font-weight:600; color:var(--primary);">
                                    {{ $tx->site?->code ?? 'N/A' }}
                                </a>
                                <span class="text-xs text-secondary">({{ $tx->site?->name }})</span>
                            </td>
                            <td>
                                @if($tx->type === 'IN')
                                    <span class="badge badge-green">IN</span>
                                @elseif($tx->type === 'OUT')
                                    <span class="badge badge-amber">OUT</span>
                                @elseif($tx->type === 'transfer_in')
                                    <span class="badge badge-blue">Transfer IN</span>
                                @elseif($tx->type === 'transfer_out')
                                    <span class="badge badge-purple">Transfer OUT</span>
                                @else
                                    <span class="badge badge-default">{{ $tx->type }}</span>
                                @endif
                            </td>
                            <td class="font-mono text-xs text-muted">{{ $tx->reference_number ?? '—' }}</td>
                            <td class="text-right font-mono font-bold {{ in_array($tx->type, ['IN', 'transfer_in']) ? 'text-green' : 'text-amber' }}">
                                {{ in_array($tx->type, ['IN', 'transfer_in']) ? '+' : '-' }}{{ number_format($tx->quantity, 2) }}
                            </td>
                            <td>
                                <span class="badge badge-success">{{ ucfirst($tx->status) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
