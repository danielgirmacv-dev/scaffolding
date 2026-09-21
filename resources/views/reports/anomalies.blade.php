@extends('layouts.app')
@section('title', 'Data Anomaly Audit')
@section('page-title', 'Stock Balance Anomalies')
@section('page-subtitle', 'Detected data entry inconsistencies, negative balances, and flagged transactions')

@section('content')
<div class="card fade-in">
    <div class="card-header">
        <div>
            <div class="card-title">Audit Flag Log</div>
            <div class="card-subtitle">Showing {{ $anomalies->total() }} flagged records from Excel audits &amp; balance checks</div>
        </div>
        <div>
            <span class="badge {{ $anomalies->where('status', 'open')->count() > 0 ? 'badge-danger' : 'badge-success' }}">
                {{ $anomalies->where('status', 'open')->count() }} Open Issues
            </span>
        </div>
    </div>

    @if($anomalies->isEmpty())
        <div style="text-align:center; padding: 48px 16px;">
            <div style="width:56px; height:56px; border-radius:50%; background:rgba(16,185,129,0.1); color:var(--accent-green); display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px;">
                <i data-lucide="check-circle" style="width:32px; height:32px;"></i>
            </div>
            <h3 style="font-size:16px; font-weight:600; color:var(--text-primary); margin-bottom:6px;">No Anomalies Detected</h3>
            <p style="color:var(--text-secondary); font-size:13px; max-width:400px; margin:0 auto;">
                All project sites and material balances are reconciled with zero negative balances or pending audit flags.
            </p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Status</th>
                        <th>Error Type</th>
                        <th>Site</th>
                        <th>Material</th>
                        <th>Negative Balance</th>
                        <th>Source Sheet / Row</th>
                        <th>Message</th>
                        <th>Logged At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($anomalies as $anomaly)
                        <tr>
                            <td class="font-mono text-xs text-muted">#{{ $anomaly->id }}</td>
                            <td>
                                @if($anomaly->status === 'open')
                                    <span class="badge badge-danger">Open</span>
                                @elseif($anomaly->status === 'investigating')
                                    <span class="badge badge-warning">Investigating</span>
                                @else
                                    <span class="badge badge-success">Resolved</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-default">{{ str_replace('_', ' ', $anomaly->error_type) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('sites.show', $anomaly->site_id) }}" style="font-weight:600; color:var(--primary);">
                                    {{ $anomaly->site?->code ?? 'N/A' }}
                                </a>
                                <div class="text-xs text-secondary">{{ $anomaly->site?->name }}</div>
                            </td>
                            <td>
                                <span style="font-weight:500;">{{ $anomaly->material?->name ?? 'N/A' }}</span>
                                <div class="text-xs text-muted">{{ $anomaly->material?->item_code }}</div>
                            </td>
                            <td class="font-mono" style="color:var(--accent-red); font-weight:600;">
                                {{ number_format($anomaly->calculated_negative_balance, 2) }}
                            </td>
                            <td class="text-xs font-mono">
                                {{ $anomaly->source_sheet ?? 'Live Audit' }}
                                @if($anomaly->source_row)
                                    <span class="text-muted">:R{{ $anomaly->source_row }}</span>
                                @endif
                            </td>
                            <td class="text-xs text-secondary" style="max-width:260px;">
                                {{ $anomaly->message }}
                            </td>
                            <td class="text-xs text-muted whitespace-nowrap">
                                {{ $anomaly->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($anomalies->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
                {{ $anomalies->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
