<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters & Controls Bar --}}
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap gap-3 items-center">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">As Of Date</label>
                    <input type="date" wire:model.live="asOfDate" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 py-1.5 px-3">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Category</label>
                    <select wire:model.live="category" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 py-1.5 px-3">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Search Material</label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Filter by name or SKU..." class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 py-1.5 px-3 w-56">
                </div>
            </div>

            {{-- Legend --}}
            <div class="flex flex-wrap gap-2 items-center text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Central Store
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> Project Site
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 font-medium">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Sub-Store
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 font-medium">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> Production
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 font-medium">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Out-of-Addis
                </span>
            </div>
        </div>

        {{-- Master Inventory Matrix Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Master Inventory Matrix (Company Balance)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Rows represent material items; columns represent all active project sites and internal depots. Data computed live from immutable transaction ledgers.</p>
                </div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    Showing {{ count($materials) }} items across {{ count($sites) }} locations
                </div>
            </div>

            <div class="overflow-x-auto" style="max-height: 70vh;">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-gray-100 dark:bg-gray-900/90 text-gray-600 dark:text-gray-300 uppercase tracking-wider sticky top-0 z-20 backdrop-blur">
                        <tr class="divide-x divide-gray-200 dark:divide-gray-800">
                            <th class="p-2.5 font-bold sticky left-0 z-30 bg-gray-100 dark:bg-gray-900 min-w-[100px]">SKU</th>
                            <th class="p-2.5 font-bold sticky left-[100px] z-30 bg-gray-100 dark:bg-gray-900 min-w-[220px]">Material Description</th>
                            <th class="p-2.5 font-bold min-w-[65px] text-center">Unit</th>
                            @foreach($sites as $site)
                                @php
                                    $badgeClass = match(true) {
                                        $site->isCentralStore() => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300',
                                        $site->isSubStore() => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-300',
                                        $site->isProduction() => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border-purple-300',
                                        $site->isOutOfAddis() => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-300',
                                        default => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-300',
                                    };
                                @endphp
                                <th class="p-2 text-right min-w-[85px] font-semibold">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[11px] font-bold border {{ $badgeClass }}" title="{{ $site->name }} ({{ $site->type_label }})">
                                        {{ $site->code }}
                                    </span>
                                </th>
                            @endforeach
                            <th class="p-2.5 text-right min-w-[95px] font-bold bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white sticky right-0 z-20">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700/60 font-mono">
                        @forelse($materials as $mat)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors divide-x divide-gray-200/50 dark:divide-gray-800/50">
                                <td class="p-2 font-bold text-gray-500 dark:text-gray-400 sticky left-0 z-10 bg-white dark:bg-gray-800">{{ $mat->item_code }}</td>
                                <td class="p-2 font-sans font-medium text-gray-900 dark:text-white sticky left-[100px] z-10 bg-white dark:bg-gray-800 truncate max-w-[220px]" title="{{ $mat->name }}">
                                    {{ $mat->name }}
                                </td>
                                <td class="p-2 text-center text-[11px] text-gray-500 dark:text-gray-400">{{ $mat->unit_of_measure }}</td>
                                @foreach($sites as $site)
                                    @php
                                        $bal = $matrix[$mat->id][$site->id] ?? 0.0;
                                    @endphp
                                    <td class="p-2 text-right {{ $bal < 0 ? 'text-red-600 font-bold bg-red-50/50 dark:bg-red-950/30' : ($bal > 0 ? 'text-gray-800 dark:text-gray-200 font-medium' : 'text-gray-300 dark:text-gray-600') }}">
                                        {{ $bal != 0 ? number_format($bal, 0) : '—' }}
                                    </td>
                                @endforeach
                                @php
                                    $tot = $totals[$mat->id] ?? 0.0;
                                @endphp
                                <td class="p-2 text-right font-bold {{ $tot > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($tot < 0 ? 'text-red-600' : 'text-gray-400') }} bg-gray-50/80 dark:bg-gray-800/90 sticky right-0 z-10">
                                    {{ number_format($tot, 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($sites) + 4 }}" class="p-8 text-center text-gray-500">
                                    No materials found matching the current filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
