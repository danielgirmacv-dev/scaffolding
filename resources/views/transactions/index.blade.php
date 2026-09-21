@extends('layouts.app')
@section('page-title', 'Stock Movements Ledger')
@section('content')
<div class="card fade-in">
    <div class="card-header">
        <div>
            <div class="card-title">Immutable Transaction Ledger</div>
            <div class="card-subtitle">{{ $transactions->total() }} total transactions · Running balance is always derived, never stored</div>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Ref / Tx No.</th><th>Date</th><th>Site</th><th>Material</th>
                    <th>Direction</th><th class="text-right">Qty</th><th>Transfer Route</th><th>Rec. By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr>
                    <td>
                        <div class="font-mono" style="font-size:11px;color:var(--text-muted);">{{ $tx->transaction_no }}</div>
                        @if($tx->ref_no)<div style="font-size:11px;color:var(--text-primary);">{{ $tx->ref_no }}</div>@endif
                    </td>
                    <td style="font-size:12px;white-space:nowrap;">{{ $tx->transaction_date->format('d M Y') }}</td>
                    <td><span class="badge badge-blue">{{ $tx->site->code }}</span></td>
                    <td style="font-size:12px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $tx->material->name }}
                    </td>
                    <td>
                        @php
                            $map = [
                                'in'=>['badge-green','↓ IN'],'out'=>['badge-red','↑ OUT'],
                                'transfer_in'=>['badge-teal','⇐ T.IN'],'transfer_out'=>['badge-amber','⇒ T.OUT'],
                                'damaged'=>['badge-red','✕ Damaged'],'lost'=>['badge-red','✕ Lost'],
                                'adjustment'=>['badge-purple','± Adjust'],
                            ];
                            [$cls,$lbl] = $map[$tx->direction] ?? ['badge-gray',$tx->direction];
                        @endphp
                        <span class="badge {{ $cls }}">{{ $lbl }}</span>
                    </td>
                    <td class="text-right font-bold">{{ number_format($tx->quantity, 0) }}</td>
                    <td style="font-size:11px;color:var(--text-muted);">
                        @if($tx->fromSite && $tx->toSite)
                            <span class="badge badge-gray">{{ $tx->fromSite->code }} → {{ $tx->toSite->code }}</span>
                        @else —
                        @endif
                    </td>
                    <td style="font-size:11px;color:var(--text-muted);">{{ $tx->creator?->name ?? 'System' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="empty-state">No transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--border);">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
