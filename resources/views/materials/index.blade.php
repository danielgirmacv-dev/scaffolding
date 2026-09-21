@extends('layouts.app')
@section('page-title', 'Materials Catalog')
@section('content')
<div class="card fade-in">
    <div class="card-header">
        <div><div class="card-title">Scaffolding &amp; Formwork Catalog</div>
        <div class="card-subtitle">{{ $materials->count() }} materials · Unit prices, discount &amp; depreciation rates</div></div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Code</th><th>Material Name</th><th>Category</th><th>UoM</th>
                    <th class="text-right">Market Rate/Day</th><th class="text-right">EEIG Discount</th>
                    <th class="text-right">Eff. Rate/Day</th><th class="text-right">Replace Cost</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($materials->groupBy('category') as $category => $group)
                    <tr style="background:var(--user-bg);">
                        <td colspan="9" style="font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);padding:10px 16px 8px;">
                            {{ $category }}
                        </td>
                    </tr>
                    @foreach($group as $mat)
                    <tr>
                        <td class="font-mono" style="color:var(--text-muted);">{{ $mat->item_code }}</td>
                        <td style="font-weight:600;">
                            <a href="{{ route('materials.show', $mat) }}" style="color:var(--text-primary); text-decoration:none; transition:color 0.15s ease;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-primary)'">
                                {{ $mat->name }}
                            </a>
                        </td>
                        <td><span class="badge badge-purple">{{ $mat->category }}</span></td>
                        <td><span class="badge badge-gray">{{ $mat->unit_of_measure }}</span></td>
                        <td class="text-right font-mono">{{ number_format($mat->market_rate_per_day, 4) }}</td>
                        <td class="text-right">
                            <span class="badge badge-amber">{{ $mat->eeig_discount_percent }}%</span>
                        </td>
                        <td class="text-right font-mono text-green font-bold">
                            {{ number_format($mat->effective_daily_rate, 4) }}
                        </td>
                        <td class="text-right font-mono">{{ number_format($mat->replacement_cost, 2) }}</td>
                        <td>
                            <span class="badge {{ $mat->is_active ? 'badge-green' : 'badge-gray' }}">
                                {{ $mat->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
