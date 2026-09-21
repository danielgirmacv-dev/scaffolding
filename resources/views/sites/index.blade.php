@extends('layouts.app')
@section('page-title', 'Project Sites')
@section('content')
<div class="card fade-in">
    <div class="card-header">
        <div>
            <div class="card-title">All Sites</div>
            <div class="card-subtitle">{{ $sites->count() }} total — includes Central Store</div>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Code</th><th>Name</th><th>Client</th><th>Location</th>
                    <th class="text-right">Transactions</th><th class="text-right">Rentals</th>
                    <th>Type</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sites->sortByDesc('is_central_store') as $site)
                <tr>
                    <td><span class="badge badge-blue">{{ $site->code }}</span></td>
                    <td style="font-weight:600;">{{ $site->name }}</td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $site->client ?? '—' }}</td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $site->location ?? '—' }}</td>
                    <td class="text-right">{{ number_format($site->transactions_count) }}</td>
                    <td class="text-right">{{ number_format($site->rentals_count) }}</td>
                    <td>
                        @if($site->is_central_store)
                            <span class="badge badge-purple">Central Store</span>
                        @else
                            <span class="badge badge-teal">Project Site</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $site->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($site->status) }}</span>
                    </td>
                    <td><a href="{{ route('sites.show', $site) }}" class="btn btn-outline btn-sm">View →</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
