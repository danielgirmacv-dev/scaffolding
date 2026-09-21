<x-filament-panels::page>
    <style>
        /* ── Inventory Visual Dashboard Custom Styles ── */
        .chart-dashboard-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            font-family: inherit;
        }

        /* Hero Banner */
        .chart-hero-banner {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #312e81 100%) !important;
            padding: 1.75rem 2rem;
            box-shadow: 0 10px 25px -5px rgba(30, 27, 75, 0.4), 0 8px 10px -6px rgba(30, 27, 75, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff !important;
        }
        .chart-hero-banner h1 {
            color: #ffffff !important;
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.25;
            margin: 0.35rem 0;
        }
        .chart-hero-banner p {
            color: #c7d2fe !important;
            font-size: 0.885rem;
            max-width: 48rem;
            line-height: 1.5;
            margin: 0;
        }
        .chart-hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(8px);
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #ffffff !important;
            letter-spacing: 0.02em;
        }

        /* Quick-Nav Cards Grid */
        .chart-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 0.875rem;
        }
        .chart-legend-card {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            padding: 0.95rem 1.15rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }
        .chart-legend-card:hover {
            transform: translateY(-2px);
            border-color: #cbd5e1;
            box-shadow: 0 6px 16px -2px rgba(15, 23, 42, 0.08);
        }
        .dark .chart-legend-card {
            background: #1e293b;
            border-color: #334155;
        }
        .dark .chart-legend-card:hover {
            border-color: #475569;
            box-shadow: 0 6px 16px -2px rgba(0, 0, 0, 0.3);
        }
        .chart-icon-bubble {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.65rem;
            flex-shrink: 0;
        }

        /* Chart Section Wrappers */
        .chart-section {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .chart-section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .chart-section-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.6rem;
            font-size: 0.85rem;
            font-weight: 700;
            color: #ffffff;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }
        .chart-section-title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.015em;
            color: #0f172a;
            margin: 0;
        }
        .dark .chart-section-title {
            color: #f8fafc;
        }
        .chart-section-desc {
            font-size: 0.785rem;
            color: #64748b;
            margin: 0.15rem 0 0 0;
            line-height: 1.4;
        }
        .dark .chart-section-desc {
            color: #94a3b8;
        }
        .chart-box-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            padding: 0.35rem;
        }
        .dark .chart-box-card {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }
    </style>

    <div class="chart-dashboard-container">

        {{-- ── Hero Header Banner ────────────────────────────────────────────── --}}
        <div class="chart-hero-banner">
            <div style="display: flex; flex-direction: column; gap: 1rem; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <svg style="width: 1.25rem; height: 1.25rem; color: #a5b4fc;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                        </svg>
                        <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #a5b4fc;">EEIG Scaffolding &amp; Formwork Enterprise</span>
                    </div>
                    <h1>Inventory &amp; Operations Visual Dashboard</h1>
                    <p>
                        Real-time visualization calculated directly from approved site transactions across all 35 project sites and central warehouse.
                        Maintenance-held items and work-in-progress materials are strictly excluded to ensure auditable balance integrity.
                    </p>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <div class="chart-hero-pill">
                        <span style="width: 0.55rem; height: 0.55rem; border-radius: 9999px; background: #34d399; box-shadow: 0 0 8px #34d399;"></span>
                        Approved Txns Only
                    </div>
                    <div class="chart-hero-pill">
                        <span style="width: 0.55rem; height: 0.55rem; border-radius: 9999px; background: #fbbf24; box-shadow: 0 0 8px #fbbf24;"></span>
                        Maintenance Excluded
                    </div>
                    <div class="chart-hero-pill">
                        <span style="width: 0.55rem; height: 0.55rem; border-radius: 9999px; background: #a78bfa; box-shadow: 0 0 8px #a78bfa;"></span>
                        WIP Production Excluded
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Quick-Nav Metric Cards ────────────────────────────────────────── --}}
        <div class="chart-cards-grid">
            {{-- Card 1 --}}
            <div class="chart-legend-card">
                <div class="chart-icon-bubble" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5;">
                    <svg style="width: 1.35rem; height: 1.35rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;" class="dark:!text-white">1. Company Totals</div>
                    <div style="font-size: 0.725rem; color: #64748b;" class="dark:!text-gray-400">Total company stock</div>
                </div>
            </div>

            {{-- Card 2 --}}
            <div class="chart-legend-card">
                <div class="chart-icon-bubble" style="background: rgba(139, 92, 246, 0.12); color: #7c3aed;">
                    <svg style="width: 1.35rem; height: 1.35rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;" class="dark:!text-white">2. By Material</div>
                    <div style="font-size: 0.725rem; color: #64748b;" class="dark:!text-gray-400">Balance across 35 sites</div>
                </div>
            </div>

            {{-- Card 3 --}}
            <div class="chart-legend-card">
                <div class="chart-icon-bubble" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                    <svg style="width: 1.35rem; height: 1.35rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;" class="dark:!text-white">3. By Site</div>
                    <div style="font-size: 0.725rem; color: #64748b;" class="dark:!text-gray-400">Breakdown per site</div>
                </div>
            </div>

            {{-- Card 4 --}}
            <div class="chart-legend-card">
                <div class="chart-icon-bubble" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                    <svg style="width: 1.35rem; height: 1.35rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;" class="dark:!text-white">4. Rental Revenue</div>
                    <div style="font-size: 0.725rem; color: #64748b;" class="dark:!text-gray-400">Monthly ETB per site</div>
                </div>
            </div>

            {{-- Card 5 --}}
            <div class="chart-legend-card">
                <div class="chart-icon-bubble" style="background: rgba(6, 182, 212, 0.12); color: #0891b2;">
                    <svg style="width: 1.35rem; height: 1.35rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;" class="dark:!text-white">5. M² Coverage</div>
                    <div style="font-size: 0.725rem; color: #64748b;" class="dark:!text-gray-400">Scaffolding surface area</div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════
             SECTION 1 — Company-Wide Totals
             ═══════════════════════════════════════════════════════════════════ --}}
        <div class="chart-section">
            <div class="chart-section-header">
                <div class="chart-section-badge" style="background: linear-gradient(135deg, #4f46e5, #3730a3);">1</div>
                <div>
                    <h2 class="chart-section-title">Total Company Inventory — All Materials</h2>
                    <p class="chart-section-desc">Aggregate stock per material type across the entire company (equivalent to the TOTAL SUM row in the Company Balance Sheet).</p>
                </div>
            </div>
            <div class="chart-box-card">
                @livewire(\App\Filament\Widgets\CompanyInventoryTotalsChart::class)
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════
             SECTION 2 — Per-Material Site Comparison
             ═══════════════════════════════════════════════════════════════════ --}}
        <div class="chart-section">
            <div class="chart-section-header">
                <div class="chart-section-badge" style="background: linear-gradient(135deg, #7c3aed, #5b21b6);">2</div>
                <div>
                    <h2 class="chart-section-title">Stock Balance by Site — Per Material</h2>
                    <p class="chart-section-desc">Select a material from the dropdown to compare balances across all 35 project sites. Zero-balance sites are shown in muted gray to identify stock gaps.</p>
                </div>
            </div>
            <div class="chart-box-card">
                @livewire(\App\Filament\Widgets\InventoryByMaterialChart::class)
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════
             SECTION 3 — Per-Site Material Breakdown
             ═══════════════════════════════════════════════════════════════════ --}}
        <div class="chart-section">
            <div class="chart-section-header">
                <div class="chart-section-badge" style="background: linear-gradient(135deg, #059669, #047857);">3</div>
                <div>
                    <h2 class="chart-section-title">Material Breakdown by Site</h2>
                    <p class="chart-section-desc">Select any project site or depot from the dropdown. Horizontal bars show the exact stock profile of that specific location.</p>
                </div>
            </div>
            <div class="chart-box-card">
                @livewire(\App\Filament\Widgets\InventoryBySiteChart::class)
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════
             SECTION 4 — Monthly Rental Revenue by Site
             ═══════════════════════════════════════════════════════════════════ --}}
        <div class="chart-section">
            <div class="chart-section-header">
                <div class="chart-section-badge" style="background: linear-gradient(135deg, #d97706, #b45309);">4</div>
                <div>
                    <h2 class="chart-section-title">Monthly Rental Revenue by Site (ETB)</h2>
                    <p class="chart-section-desc">
                        ETB billing amounts per site for the selected billing period, sorted highest revenue first.
                    </p>
                </div>
            </div>
            <div class="chart-box-card">
                @livewire(\App\Filament\Widgets\RentalRevenueBySiteChart::class)
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════
             SECTION 5 — M² / SET Coverage by Material and Site
             ═══════════════════════════════════════════════════════════════════ --}}
        <div class="chart-section">
            <div class="chart-section-header">
                <div class="chart-section-badge" style="background: linear-gradient(135deg, #0891b2, #0e7490);">5</div>
                <div>
                    <h2 class="chart-section-title">Scaffolding Area Coverage (M²) by Site &amp; Material</h2>
                    <p class="chart-section-desc">Shows scaffolding surface area coverage (M²) for materials tracked in square meters: CHS tubes, H-Frame, Pin Lock, and Props.</p>
                </div>
            </div>
            <div class="chart-box-card">
                @livewire(\App\Filament\Widgets\M2CoverageBySiteChart::class)
            </div>
        </div>

        {{-- ── Audit & Verification Footer ────────────────────────────────── --}}
        <div style="display: flex; align-items: flex-start; gap: 0.75rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; background: #f8fafc; padding: 1rem 1.25rem; font-size: 0.785rem; color: #64748b;" class="dark:!bg-gray-800/60 dark:!border-gray-700 dark:!text-gray-400">
            <svg style="width: 1.15rem; height: 1.15rem; color: #6366f1; flex-shrink: 0; margin-top: 0.1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
            </svg>
            <div>
                <strong style="color: #1e293b;" class="dark:!text-gray-200">Auditable Inventory Guarantee:</strong>
                All charts calculate directly from approved transactions in the database.
                Items currently in maintenance (<code style="font-family: monospace; background: rgba(0,0,0,0.06); padding: 0.1rem 0.3rem; border-radius: 0.25rem;">maintenance_status = in_maintenance</code>)
                and work-in-progress production (<code style="font-family: monospace; background: rgba(0,0,0,0.06); padding: 0.1rem 0.3rem; border-radius: 0.25rem;">production_stage = on_process</code>)
                are excluded from active site totals. This page is <strong style="color: #1e293b;" class="dark:!text-gray-200">view-only</strong>.
            </div>
        </div>

    </div>
</x-filament-panels::page>
