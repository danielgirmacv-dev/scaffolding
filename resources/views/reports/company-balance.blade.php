@extends('layouts.app')
@section('page-title', 'Company Balance Matrix')
@section('page-subtitle', 'As of ' . $asOfDate)
@section('topbar-actions')
    <form method="GET" style="display:flex;gap:8px;align-items:center;">
        <input type="date" name="as_of" value="{{ $asOfDate }}" class="form-control" style="width:160px;padding:7px 12px;">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>
@endsection
@section('content')
<div class="card fade-in">
    <div class="card-header">
        <div>
            <div class="card-title">Cross-Site Stock Balance Matrix</div>
            <div class="card-subtitle">Replaces "Company Balance" Excel sheet · Computed from immutable ledger</div>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="min-width:120px;">Item Code</th>
                    <th style="min-width:200px;">Material</th>
                    <th>UoM</th>
                    @foreach($sites as $site)
                        <th class="text-right" style="min-width:90px;">
                            <span class="badge {{ $site->is_central_store ? 'badge-purple' : 'badge-blue' }}" style="font-size:10px;">
                                {{ $site->code }}
                            </span>
                        </th>
                    @endforeach
                    <th class="text-right" style="min-width:90px;color:var(--text-primary);font-weight:700;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($materials as $mat)
                <tr>
                    <td class="font-mono" style="color:var(--text-muted);font-size:11px;">{{ $mat->item_code }}</td>
                    <td style="font-weight:500;">{{ $mat->name }}</td>
                    <td><span class="badge badge-gray" style="font-size:10px;">{{ $mat->unit_of_measure }}</span></td>
                    @foreach($sites as $site)
                        @php $bal = $matrix[$mat->id][$site->id] ?? 0.0; @endphp
                        <td class="text-right font-mono {{ $bal < 0 ? 'text-red' : ($bal === 0.0 ? 'text-muted' : '') }}">
                            {{ $bal != 0 ? number_format($bal, 0) : '—' }}
                        </td>
                    @endforeach
                    <td class="text-right font-mono font-bold {{ ($totals[$mat->id] ?? 0) > 0 ? 'text-green' : 'text-muted' }}">
                        {{ number_format($totals[$mat->id] ?? 0, 0) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
