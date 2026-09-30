<x-filament-panels::page>
    {{-- ════════════════════════════════════════════════════════════
         EEIG Material Advanced Dashboard
         ════════════════════════════════════════════════════════════ --}}

    <style>
        /* ── Base tokens ── */
        .md-dash {
            --clr-scaf: #6366f1;
            --clr-scaf-lt: #eef2ff;
            --clr-form: #0891b2;
            --clr-form-lt: #ecfeff;
            --clr-success: #059669;
            --clr-warn: #d97706;
            --clr-danger: #dc2626;
            --clr-muted: #64748b;
            --radius: 14px;
            --shadow: 0 4px 24px rgba(0,0,0,.08);
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }

        /* ── KPI grid ── */
        .md-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }
        .md-kpi {
            background: white;
            border-radius: var(--radius);
            padding: 20px 22px;
            box-shadow: var(--shadow);
            border-left: 4px solid transparent;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s;
            text-decoration: none;
            display: block;
            position: relative;
            overflow: hidden;
        }
        .dark .md-kpi { background: #1e293b; }
        .md-kpi:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,0,0,.14); }
        .md-kpi.scaf  { border-color: var(--clr-scaf); }
        .md-kpi.form  { border-color: var(--clr-form); }
        .md-kpi.green { border-color: var(--clr-success); }
        .md-kpi.amber { border-color: var(--clr-warn); }
        .md-kpi.rose  { border-color: #e11d48; }
        .md-kpi.slate { border-color: #475569; }

        .md-kpi__icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 12px;
            font-size: 22px;
        }
        .md-kpi.scaf  .md-kpi__icon { background: #eef2ff; color: var(--clr-scaf); }
        .md-kpi.form  .md-kpi__icon { background: #ecfeff; color: var(--clr-form); }
        .md-kpi.green .md-kpi__icon { background: #ecfdf5; color: var(--clr-success); }
        .md-kpi.amber .md-kpi__icon { background: #fffbeb; color: var(--clr-warn); }
        .md-kpi.rose  .md-kpi__icon { background: #fff1f2; color: #e11d48; }
        .md-kpi.slate .md-kpi__icon { background: #f1f5f9; color: #475569; }

        .md-kpi__label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--clr-muted); margin-bottom: 4px; }
        .md-kpi__value { font-size: 28px; font-weight: 800; line-height: 1; color: #0f172a; }
        .dark .md-kpi__value { color: #f1f5f9; }
        .md-kpi__sub   { font-size: 12px; color: var(--clr-muted); margin-top: 6px; }

        /* ── Category Tab Bar ── */
        .md-tabs {
            display: flex; gap: 8px; flex-wrap: wrap;
            padding: 4px 0;
        }
        .md-tab {
            padding: 8px 20px;
            border-radius: 999px;
            font-size: 13px; font-weight: 600;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all .2s;
        }
        .md-tab.all   { background: #f1f5f9; color: #0f172a; }
        .md-tab.all.active, .md-tab.all:hover { background: #0f172a; color: white; }
        .md-tab.scaf  { background: var(--clr-scaf-lt); color: var(--clr-scaf); border-color: var(--clr-scaf); }
        .md-tab.scaf.active, .md-tab.scaf:hover { background: var(--clr-scaf); color: white; }
        .md-tab.form  { background: var(--clr-form-lt); color: var(--clr-form); border-color: var(--clr-form); }
        .md-tab.form.active, .md-tab.form:hover { background: var(--clr-form); color: white; }

        /* ── Section header ── */
        .md-section-header {
            display: flex; align-items: center; justify-content: space-between;
            margin: 28px 0 14px;
        }
        .md-section-title {
            font-size: 16px; font-weight: 800; color: #0f172a;
            display: flex; align-items: center; gap: 8px;
        }
        .dark .md-section-title { color: #f1f5f9; }

        .md-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
        }
        .md-btn.primary { background: var(--clr-scaf); color: white; }
        .md-btn.primary:hover { background: #4338ca; }
        .md-btn.success { background: var(--clr-success); color: white; }
        .md-btn.success:hover { background: #047857; }
        .md-btn.outline { border: 2px solid #e2e8f0; color: #475569; background: white; }
        .md-btn.outline:hover { border-color: #cbd5e1; background: #f8fafc; }

        /* ── Card ── */
        .md-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
        }
        .dark .md-card { background: #1e293b; }

        /* ── Category hero cards ── */
        .md-cat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media(max-width:768px) { .md-cat-grid { grid-template-columns: 1fr; } }

        .md-cat-card {
            border-radius: var(--radius);
            padding: 28px;
            position: relative; overflow: hidden;
            text-decoration: none; display: block;
            transition: transform .15s, box-shadow .15s;
            cursor: pointer;
        }
        .md-cat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 40px rgba(0,0,0,.18); }
        .md-cat-card.scaf {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 60%, #3730a3 100%);
            color: white;
        }
        .md-cat-card.form {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 60%, #155e75 100%);
            color: white;
        }
        .md-cat-card__title { font-size: 13px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; opacity: .8; margin-bottom: 8px; }
        .md-cat-card__value { font-size: 48px; font-weight: 900; line-height: 1; }
        .md-cat-card__sub   { font-size: 14px; opacity: .75; margin-top: 8px; }
        .md-cat-card__cta {
            margin-top: 20px;
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.2);
            padding: 8px 16px; border-radius: 8px;
            font-size: 13px; font-weight: 600;
            backdrop-filter: blur(4px);
            transition: background .15s;
        }
        .md-cat-card:hover .md-cat-card__cta { background: rgba(255,255,255,.3); }
        .md-cat-card__bg-icon {
            position: absolute; right: -10px; bottom: -10px;
            font-size: 120px; opacity: .1;
            pointer-events: none;
        }

        /* ── Revenue split bar ── */
        .rev-split { height: 10px; border-radius: 999px; overflow: hidden; display: flex; margin: 8px 0; }
        .rev-split__scaf { background: var(--clr-scaf); transition: width .5s; }
        .rev-split__form { background: var(--clr-form);  transition: width .5s; }

        /* ── Material table ── */
        .md-table-wrap { overflow-x: auto; border-radius: 12px; }
        .md-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .md-table th {
            background: #f8fafc; color: var(--clr-muted);
            font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
            padding: 10px 14px; text-align: left; white-space: nowrap;
            border-bottom: 2px solid #e2e8f0;
        }
        .dark .md-table th { background: #0f172a; color: #94a3b8; border-color: #334155; }
        .md-table td {
            padding: 12px 14px; border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .dark .md-table td { border-color: #1e293b; color: #e2e8f0; }
        .md-table tr:hover td { background: #f8fafc; }
        .dark .md-table tr:hover td { background: #1e293b; }

        .cat-badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 700; letter-spacing: .04em;
        }
        .cat-badge.scaf { background: #eef2ff; color: var(--clr-scaf); }
        .cat-badge.form { background: #ecfeff; color: var(--clr-form); }

        .status-dot {
            display: inline-block; width: 8px; height: 8px;
            border-radius: 50%; margin-right: 5px;
        }
        .status-dot.active   { background: var(--clr-success); }
        .status-dot.inactive { background: #94a3b8; }

        .vol-bar-wrap { display: flex; align-items: center; gap: 8px; min-width: 100px; }
        .vol-bar-bg { flex: 1; height: 6px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
        .vol-bar { height: 100%; border-radius: 99px; transition: width .4s; }
        .vol-bar.scaf { background: var(--clr-scaf); }
        .vol-bar.form { background: var(--clr-form); }

        .md-link {
            color: var(--clr-scaf); font-weight: 600; text-decoration: none;
            transition: color .15s;
        }
        .md-link:hover { color: #4338ca; text-decoration: underline; }

        /* ── Chart container ── */
        .md-chart-wrap {
            display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
        }
        @media(max-width: 900px) { .md-chart-wrap { grid-template-columns: 1fr; } }
        .md-chart-box { padding: 20px; }

        /* ── Pending badge ── */
        .pending-badge {
            display: inline-flex; align-items: center; gap: 4px;
            background: #fef3c7; color: #92400e;
            padding: 3px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 700;
        }

        /* ── Delta chip ── */
        .delta-chip {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700;
        }
        .delta-chip.up   { background: #dcfce7; color: #166534; }
        .delta-chip.down { background: #fee2e2; color: #991b1b; }
    </style>

    <div class="md-dash space-y-6">

        {{-- ═══ KPI ROW ═══ --}}
        <div class="md-kpi-grid">

            {{-- Total Materials --}}
            <a href="{{ $materialsIndexUrl }}" class="md-kpi scaf">
                <div class="md-kpi__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                </div>
                <div class="md-kpi__label">Total Materials</div>
                <div class="md-kpi__value">{{ $totalMaterials }}</div>
                <div class="md-kpi__sub">{{ $activeMaterials }} active · {{ $inactiveMaterials }} inactive</div>
            </a>

            {{-- Scaffolding --}}
            <a href="{{ $materialsIndexUrl }}?tableFilters[category][value]=Scaffolding" class="md-kpi scaf">
                <div class="md-kpi__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875C20.625 4.254 20.121 3.75 19.5 3.75H4.5c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125z"/></svg>
                </div>
                <div class="md-kpi__label">Scaffolding Items</div>
                <div class="md-kpi__value">{{ $scaffoldingCount }}</div>
                <div class="md-kpi__sub">{{ round($scaffoldingCount / max($totalMaterials,1) * 100) }}% of catalog</div>
            </a>

            {{-- Formwork --}}
            <a href="{{ $materialsIndexUrl }}?tableFilters[category][value]=Formwork" class="md-kpi form">
                <div class="md-kpi__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-8.25zM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-2.25z"/></svg>
                </div>
                <div class="md-kpi__label">Formwork Items</div>
                <div class="md-kpi__value">{{ $formworkCount }}</div>
                <div class="md-kpi__sub">{{ round($formworkCount / max($totalMaterials,1) * 100) }}% of catalog</div>
            </a>

            {{-- Transactions --}}
            <a href="{{ $txIndexUrl }}" class="md-kpi green">
                <div class="md-kpi__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                </div>
                <div class="md-kpi__label">Total Transactions</div>
                <div class="md-kpi__value">{{ $totalTransactions }}</div>
                <div class="md-kpi__sub">
                    {{ $approvedTx }} approved
                    @if($pendingTx > 0)
                        · <span class="pending-badge">⚠ {{ $pendingTx }} pending</span>
                    @endif
                </div>
            </a>

            {{-- Revenue --}}
            <a href="{{ route('filament.admin.resources.rentals.index') }}" class="md-kpi amber">
                <div class="md-kpi__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="md-kpi__label">This Month Revenue</div>
                <div class="md-kpi__value">ETB {{ number_format($currentRevenue, 0) }}</div>
                <div class="md-kpi__sub">
                    @if($lastRevenue > 0)
                        <span class="delta-chip {{ $revenueDelta >= 0 ? 'up' : 'down' }}">
                            {{ $revenueDelta >= 0 ? '▲' : '▼' }} {{ number_format(abs($revenueDelta), 1) }}%
                        </span>
                        vs {{ \Carbon\Carbon::now()->subMonth()->format('M Y') }}
                    @else
                        Current period
                    @endif
                </div>
            </a>

            {{-- Depot Stock --}}
            <a href="{{ $txIndexUrl }}" class="md-kpi slate">
                <div class="md-kpi__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                </div>
                <div class="md-kpi__label">Central Depot Stock</div>
                <div class="md-kpi__value">{{ number_format($depotStock, 0) }}</div>
                <div class="md-kpi__sub">{{ number_format($depotOut, 0) }} units mobilized to sites</div>
            </a>

            {{-- Active Sites --}}
            <a href="{{ route('filament.admin.resources.sites.index') }}" class="md-kpi rose">
                <div class="md-kpi__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
                </div>
                <div class="md-kpi__label">Active Project Sites</div>
                <div class="md-kpi__value">{{ $activeSites }}</div>
                <div class="md-kpi__sub">Sites currently receiving materials</div>
            </a>

        </div>

        {{-- ═══ CATEGORY HERO CARDS ═══ --}}
        <div class="md-cat-grid">

            {{-- Scaffolding hero --}}
            <a href="{{ $materialsIndexUrl }}?tableFilters[category][value]=Scaffolding" class="md-cat-card scaf">
                <div class="md-cat-card__bg-icon">🏗️</div>
                <div class="md-cat-card__title">Scaffolding Category</div>
                <div class="md-cat-card__value">{{ $scaffoldingCount }}</div>
                <div class="md-cat-card__sub">catalog items · ETB {{ number_format($scaffoldingRevenue, 0) }} this month</div>
                <div class="rev-split" style="width: 180px;">
                    @php $scafPct = ($scaffoldingRevenue + $formworkRevenue) > 0 ? ($scaffoldingRevenue / ($scaffoldingRevenue + $formworkRevenue) * 100) : 50; @endphp
                    <div class="rev-split__scaf" style="width: {{ $scafPct }}%; background: rgba(255,255,255,.5);"></div>
                    <div class="rev-split__form" style="width: {{ 100 - $scafPct }}%; background: rgba(255,255,255,.2);"></div>
                </div>
                <div class="md-cat-card__cta">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    View All Scaffolding Items
                </div>
            </a>

            {{-- Formwork hero --}}
            <a href="{{ $materialsIndexUrl }}?tableFilters[category][value]=Formwork" class="md-cat-card form">
                <div class="md-cat-card__bg-icon">🧱</div>
                <div class="md-cat-card__title">Formwork Category</div>
                <div class="md-cat-card__value">{{ $formworkCount }}</div>
                <div class="md-cat-card__sub">catalog items · ETB {{ number_format($formworkRevenue, 0) }} this month</div>
                <div class="rev-split" style="width: 180px;">
                    @php $formPct = ($scaffoldingRevenue + $formworkRevenue) > 0 ? ($formworkRevenue / ($scaffoldingRevenue + $formworkRevenue) * 100) : 50; @endphp
                    <div class="rev-split__form" style="width: {{ $formPct }}%; background: rgba(255,255,255,.5);"></div>
                    <div class="rev-split__scaf" style="width: {{ 100 - $formPct }}%; background: rgba(255,255,255,.2);"></div>
                </div>
                <div class="md-cat-card__cta">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    View All Formwork Items
                </div>
            </a>

        </div>

        {{-- ═══ TREND CHARTS ═══ --}}
        <div class="md-card">
            <div class="md-section-header" style="margin: 0 0 16px;">
                <div class="md-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    6-Month Transaction Volume by Category
                </div>
                <a href="{{ $txIndexUrl }}" class="md-btn outline">View All Transactions</a>
            </div>
            <div class="md-chart-wrap">
                <div class="md-chart-box" style="padding: 0; min-height: 260px;">
                    <canvas id="scaffoldingTrendChart" style="max-height: 260px;"></canvas>
                </div>
                <div class="md-chart-box" style="padding: 0; min-height: 260px;">
                    <canvas id="formworkTrendChart" style="max-height: 260px;"></canvas>
                </div>
            </div>
        </div>

        {{-- ═══ MATERIAL TABLE ═══ --}}
        <div class="md-card">
            <div class="md-section-header">
                <div class="md-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                    Material Catalog
                </div>
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    {{-- Tab filter --}}
                    <div class="md-tabs">
                        <button wire:click="setCategory('all')" class="md-tab all {{ $activeCategory === 'all' ? 'active' : '' }}">All ({{ $totalMaterials }})</button>
                        <button wire:click="setCategory('Scaffolding')" class="md-tab scaf {{ $activeCategory === 'Scaffolding' ? 'active' : '' }}">🏗 Scaffolding ({{ $scaffoldingCount }})</button>
                        <button wire:click="setCategory('Formwork')" class="md-tab form {{ $activeCategory === 'Formwork' ? 'active' : '' }}">🧱 Formwork ({{ $formworkCount }})</button>
                    </div>
                    @if($canManage)
                        <a href="{{ $materialsCreateUrl }}" class="md-btn primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Add Material
                        </a>
                    @endif
                </div>
            </div>

            @php
                $maxVol = $txVolumes->max() ?: 1;
            @endphp

            <div class="md-table-wrap">
                <table class="md-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Market Rate/day</th>
                            <th>EEIG Disc.</th>
                            <th>Effective Rate</th>
                            <th>Replacement</th>
                            <th>Volume</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $material)
                            @php
                                $vol = (float) ($txVolumes[$material->id] ?? 0);
                                $pct = $maxVol > 0 ? min(100, ($vol / $maxVol) * 100) : 0;
                                $isCat = strtolower($material->category) === 'scaffolding' ? 'scaf' : 'form';
                                $eff = $material->effective_daily_rate;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('filament.admin.resources.materials.view', $material) }}" class="md-link" style="font-family: monospace; font-size: 12px;">
                                        {{ $material->item_code ?? '—' }}
                                    </a>
                                </td>
                                <td style="font-weight: 600; max-width: 220px;">
                                    <a href="{{ route('filament.admin.resources.materials.view', $material) }}" class="md-link" style="font-weight: 700; color: #0f172a;">
                                        {{ $material->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="cat-badge {{ $isCat }}">{{ $material->category }}</span>
                                </td>
                                <td style="color: var(--clr-muted);">{{ $material->unit_of_measure }}</td>
                                <td style="font-family: monospace;">ETB {{ number_format((float)$material->market_rate_per_day, 2) }}</td>
                                <td>
                                    <span style="background: #fef9c3; color: #854d0e; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700;">
                                        {{ number_format((float)$material->eeig_discount_percent, 1) }}%
                                    </span>
                                </td>
                                <td style="font-family: monospace; font-weight: 700; color: var(--clr-success);">
                                    ETB {{ number_format($eff, 4) }}
                                </td>
                                <td style="font-family: monospace;">ETB {{ number_format((float)$material->replacement_cost, 0) }}</td>
                                <td>
                                    <div class="vol-bar-wrap">
                                        <div class="vol-bar-bg">
                                            <div class="vol-bar {{ $isCat }}" style="width: {{ $pct }}%;"></div>
                                        </div>
                                        <span style="font-size: 11px; color: var(--clr-muted); white-space: nowrap;">{{ number_format($vol, 0) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-dot {{ $material->is_active ? 'active' : 'inactive' }}"></span>
                                    <span style="font-size: 12px; color: {{ $material->is_active ? 'var(--clr-success)' : '#94a3b8' }}; font-weight: 600;">
                                        {{ $material->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <a href="{{ route('filament.admin.resources.materials.view', $material) }}" title="View" style="color: #6366f1; padding: 4px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </a>
                                        @if($canManage)
                                            <a href="{{ route('filament.admin.resources.materials.edit', $material) }}" title="Edit" style="color: #059669; padding: 4px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                            </a>
                                        @endif
                                        <a href="{{ $txIndexUrl }}?tableFilters[material_id][value]={{ $material->id }}" title="View Transactions" style="color: #0891b2; padding: 4px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 40px; color: var(--clr-muted);">
                                    No materials found for this category.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 14px; display: flex; align-items: center; justify-content: space-between; font-size: 13px; color: var(--clr-muted);">
                <span>Showing {{ $materials->count() }} materials</span>
                <a href="{{ $materialsIndexUrl }}" class="md-btn outline" style="font-size: 12px; padding: 5px 12px;">Full Material List →</a>
            </div>
        </div>

        {{-- ═══ QUICK ACTIONS ═══ --}}
        <div class="md-card">
            <div class="md-section-title" style="margin-bottom: 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                Quick Actions
            </div>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="{{ $materialsIndexUrl }}" class="md-btn primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    All Materials
                </a>
                @if($canManage)
                    <a href="{{ $materialsCreateUrl }}" class="md-btn success">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add Material
                    </a>
                @endif
                <a href="{{ $txIndexUrl }}" class="md-btn outline">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    Transactions
                </a>
                @if($canCreateTransactions ?? \App\Support\FilamentRoleAccess::canCreateTransactions())
                    <a href="{{ $txCreateUrl }}" class="md-btn outline">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Record Transaction
                    </a>
                @endif
                <a href="{{ route('filament.admin.pages.company-balance-sheet') }}" class="md-btn outline">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m2.25-2.25h7.5m-7.5 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m8.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c-.621 0-1.125.504-1.125 1.125m2.25-2.25h7.5m-7.5 0c.621 0 1.125.504 1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125"/></svg>
                    Balance Sheet
                </a>
                <a href="{{ route('filament.admin.pages.inventory-charts') }}" class="md-btn outline">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Inventory Charts
                </a>
            </div>
        </div>

    </div>

    {{-- ═══ CHART.JS ═══ --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const labels = {!! $monthLabels !!};
        const scaffoldingData = {!! $scaffoldingTrend !!};
        const formworkData    = {!! $formworkTrend !!};

        const commonOpts = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,.92)',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
                y: { beginAtZero: true, grid: { color: 'rgba(156,163,175,.15)' }, ticks: { font: { size: 11 } } },
            },
        };

        // Scaffolding trend
        const scafCtx = document.getElementById('scaffoldingTrendChart');
        if (scafCtx) {
            new Chart(scafCtx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Scaffolding Transaction Volume',
                        data: scaffoldingData,
                        backgroundColor: 'rgba(99,102,241,.75)',
                        borderColor: '#6366f1',
                        borderWidth: 2,
                        borderRadius: 6,
                    }],
                },
                options: {
                    ...commonOpts,
                    plugins: {
                        ...commonOpts.plugins,
                        title: {
                            display: true,
                            text: '🏗️  Scaffolding — Transaction Volume (Units)',
                            font: { size: 13, weight: 'bold' },
                            color: '#6366f1',
                            padding: { bottom: 12 },
                        },
                    },
                },
            });
        }

        // Formwork trend
        const formCtx = document.getElementById('formworkTrendChart');
        if (formCtx) {
            new Chart(formCtx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Formwork Transaction Volume',
                        data: formworkData,
                        backgroundColor: 'rgba(8,145,178,.75)',
                        borderColor: '#0891b2',
                        borderWidth: 2,
                        borderRadius: 6,
                    }],
                },
                options: {
                    ...commonOpts,
                    plugins: {
                        ...commonOpts.plugins,
                        title: {
                            display: true,
                            text: '🧱  Formwork — Transaction Volume (Units)',
                            font: { size: 13, weight: 'bold' },
                            color: '#0891b2',
                            padding: { bottom: 12 },
                        },
                    },
                },
            });
        }
    })();
    </script>
    @endpush

</x-filament-panels::page>
