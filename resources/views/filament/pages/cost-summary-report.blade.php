<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter & Period Selector --}}
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-wrap gap-4 items-center justify-between">
            <div class="flex items-center gap-3">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Billing Period</label>
                <input type="month" wire:model.live="period" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 py-1.5 px-3">
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400">
                VAT Rate: <span class="font-bold text-gray-700 dark:text-gray-200">15.00%</span> | Currency: <span class="font-bold text-gray-700 dark:text-gray-200">ETB</span> | Excluding internal stores & depots
            </div>
        </div>

        {{-- Stat Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Subtotal Revenue</div>
                <div class="text-2xl font-bold font-mono text-gray-900 dark:text-white mt-1">ETB {{ number_format($totalSubtotal, 2) }}</div>
                <div class="text-xs text-gray-400 mt-1">Net rental billings before VAT</div>
            </div>

            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total 15% VAT</div>
                <div class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400 mt-1">ETB {{ number_format($totalVat, 2) }}</div>
                <div class="text-xs text-gray-400 mt-1">Applicable Ethiopian VAT (15%)</div>
            </div>

            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Grand Total Revenue</div>
                <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">ETB {{ number_format($grandTotal, 2) }}</div>
                <div class="text-xs text-gray-400 mt-1">Total receivable inclusive of VAT</div>
            </div>

            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Active Rentals Recorded</div>
                <div class="text-2xl font-bold font-mono text-blue-600 dark:text-blue-400 mt-1">{{ number_format($totalItems, 0) }}</div>
                <div class="text-xs text-gray-400 mt-1">Items billed across {{ count($rows) }} sites</div>
            </div>
        </div>

        {{-- Financial Rollup Table Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Project Cost Rollup — {{ $period }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Replaces legacy "COST summary" sheet. Shows revenue earned per active project site.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-300 uppercase tracking-wider text-xs">
                        <tr class="divide-x divide-gray-200 dark:divide-gray-800">
                            <th class="p-3 font-bold min-w-[100px]">Site Code</th>
                            <th class="p-3 font-bold min-w-[200px]">Project Name</th>
                            <th class="p-3 font-bold min-w-[180px]">Client</th>
                            <th class="p-3 text-center font-bold min-w-[100px]">Items Rented</th>
                            <th class="p-3 text-right font-bold min-w-[140px]">Subtotal (ETB)</th>
                            <th class="p-3 text-right font-bold min-w-[120px]">15% VAT (ETB)</th>
                            <th class="p-3 text-right font-bold min-w-[150px]">Grand Total (ETB)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 font-mono text-xs">
                        @forelse($rows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors divide-x divide-gray-200/50 dark:divide-gray-800/50">
                                <td class="p-3 font-bold text-blue-600 dark:text-blue-400 font-mono">{{ $row->site_code }}</td>
                                <td class="p-3 font-sans font-medium text-gray-900 dark:text-white">{{ $row->site_name }}</td>
                                <td class="p-3 font-sans text-gray-500 dark:text-gray-400">{{ $row->client ?? '—' }}</td>
                                <td class="p-3 text-center font-bold text-gray-700 dark:text-gray-300">{{ $row->total_items_rented }}</td>
                                <td class="p-3 text-right text-gray-900 dark:text-white">{{ number_format($row->subtotal_cost, 2) }}</td>
                                <td class="p-3 text-right text-amber-600 dark:text-amber-400">{{ number_format($row->vat_amount, 2) }}</td>
                                <td class="p-3 text-right font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($row->grand_total_cost, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500 font-sans">
                                    No rental records found for billing period {{ $period }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($rows) > 0)
                        <tfoot class="bg-gray-100 dark:bg-gray-900 font-mono font-bold text-xs border-t-2 border-gray-300 dark:border-gray-600 divide-x divide-gray-200 dark:divide-gray-800">
                            <tr>
                                <td colspan="3" class="p-3 text-right uppercase font-sans text-gray-700 dark:text-gray-300">Total Company Rollup:</td>
                                <td class="p-3 text-center text-blue-600 dark:text-blue-400">{{ number_format($totalItems, 0) }}</td>
                                <td class="p-3 text-right text-gray-900 dark:text-white">{{ number_format($totalSubtotal, 2) }}</td>
                                <td class="p-3 text-right text-amber-600 dark:text-amber-400">{{ number_format($totalVat, 2) }}</td>
                                <td class="p-3 text-right text-emerald-600 dark:text-emerald-400">ETB {{ number_format($grandTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
