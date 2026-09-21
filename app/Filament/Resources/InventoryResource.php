<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Material;
use App\Models\Site;
use App\Services\SiteContext;
use App\Support\FilamentRoleAccess;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class InventoryResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $slug = 'inventory';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Site Inventory';

    protected static ?string $modelLabel = 'Site Stock';

    protected static ?string $pluralModelLabel = 'Site Inventory Balances';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return FilamentRoleAccess::canAccessPanel();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        $activeSite = SiteContext::getActiveSite();
        $activeSiteId = $activeSite?->id ?? 0;

        return $table
            ->description($activeSite ? "Live inventory ledger balance for: [{$activeSite->code}] {$activeSite->name}" : 'Viewing All Sites')
            ->columns([
                Tables\Columns\TextColumn::make('item_code')
                    ->label('SKU')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Material')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge(),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('Unit')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('in_quantity')
                    ->label('Total In')
                    ->state(function (Material $record) use ($activeSiteId): float {
                        if (! $activeSiteId) {
                            return 0.0;
                        }

                        return (float) $record->transactions()
                            ->where('site_id', $activeSiteId)
                            ->whereIn('direction', ['in', 'transfer_in', 'adjustment'])
                            ->where('production_stage', '!=', 'on_process')
                            ->where('maintenance_status', '!=', 'in_maintenance')
                            ->sum('quantity');
                    })
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::format((float) $state, precision: 2)
                        : number_format((float) $state, 2)
                    )
                    ->color('gray'),
                Tables\Columns\TextColumn::make('out_quantity')
                    ->label('Total Out')
                    ->state(function (Material $record) use ($activeSiteId): float {
                        if (! $activeSiteId) {
                            return 0.0;
                        }

                        return (float) $record->transactions()
                            ->where('site_id', $activeSiteId)
                            ->whereIn('direction', ['out', 'transfer_out', 'damaged', 'lost'])
                            ->sum('quantity');
                    })
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::format((float) $state, precision: 2)
                        : number_format((float) $state, 2)
                    )
                    ->color('gray'),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current On-Site Stock')
                    ->state(function (Material $record) use ($activeSiteId): float {
                        if (! $activeSiteId) {
                            return 0.0;
                        }

                        $in = (float) $record->transactions()
                            ->where('site_id', $activeSiteId)
                            ->whereIn('direction', ['in', 'transfer_in', 'adjustment'])
                            ->where('production_stage', '!=', 'on_process')
                            ->where('maintenance_status', '!=', 'in_maintenance')
                            ->sum('quantity');

                        $out = (float) $record->transactions()
                            ->where('site_id', $activeSiteId)
                            ->whereIn('direction', ['out', 'transfer_out', 'damaged', 'lost'])
                            ->sum('quantity');

                        return max(0.0, round($in - $out, 2));
                    })
                    ->badge()
                    ->color(fn (float $state): string => $state > 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('stock_valuation')
                    ->label('Asset Valuation')
                    ->state(function (Material $record) use ($activeSiteId): float {
                        if (! $activeSiteId) {
                            return 0.0;
                        }

                        $in = (float) $record->transactions()
                            ->where('site_id', $activeSiteId)
                            ->whereIn('direction', ['in', 'transfer_in', 'adjustment'])
                            ->where('production_stage', '!=', 'on_process')
                            ->where('maintenance_status', '!=', 'in_maintenance')
                            ->sum('quantity');

                        $out = (float) $record->transactions()
                            ->where('site_id', $activeSiteId)
                            ->whereIn('direction', ['out', 'transfer_out', 'damaged', 'lost'])
                            ->sum('quantity');

                        $stock = max(0.0, round($in - $out, 2));

                        return round($stock * (float) $record->replacement_cost, 2);
                    })
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::currency((float) $state, in: 'ETB')
                        : 'ETB '.number_format((float) $state, 2)
                    )
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Scaffolding' => 'Scaffolding',
                        'Formwork' => 'Formwork',
                        'Shoring & Props' => 'Shoring & Props',
                        'Accessories' => 'Accessories',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('switch_site')
                    ->label('Switch Site Context')
                    ->icon('heroicon-m-building-office-2')
                    ->visible(fn (): bool => ! FilamentRoleAccess::isSiteEngineer())
                    ->form([
                        Select::make('site_id')
                            ->label('Select Active Project Site')
                            ->options(Site::query()->pluck('name', 'id'))
                            ->default(fn () => SiteContext::getActiveSiteId())
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        SiteContext::setActiveSiteId((int) $data['site_id']);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
        ];
    }
}
