<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialTransactionResource\Pages;
use App\Models\MaterialTransaction;
use App\Support\FilamentRoleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class MaterialTransactionResource extends Resource
{
    protected static ?string $model = MaterialTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', 'pending_approval')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function canViewAny(): bool
    {
        return FilamentRoleAccess::canViewTransactions();
    }

    public static function canCreate(): bool
    {
        return FilamentRoleAccess::canCreateTransactions();
    }

    public static function canEdit($record): bool
    {
        return FilamentRoleAccess::canEditTransactions();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return FilamentRoleAccess::scopeToUserSite(parent::getEloquentQuery());
    }

    public static function form(Form $form): Form
    {
        $user = FilamentRoleAccess::user();
        $allowedDirections = FilamentRoleAccess::allowedTransactionDirections();

        $directionLabelMap = [
            'transfer_out' => 'Transfer Out (To Project / Site)',
            'out' => 'Out (Dispatch / Scrap)',
            'in' => 'In (Receipt / Central Store)',
            'transfer_in' => 'Transfer In (From Site)',
            'adjustment' => 'Adjustment (Reconciliation)',
            'damaged' => 'Damaged',
            'lost' => 'Lost',
        ];

        $directionOptions = collect($allowedDirections)->mapWithKeys(fn (string $direction) => [
            $direction => $directionLabelMap[$direction] ?? ucfirst(str_replace('_', ' ', $direction)),
        ])->all();

        return $form
            ->schema([
                Forms\Components\Section::make('Movement Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('direction')
                            ->label('Movement Direction')
                            ->options($directionOptions)
                            ->default(in_array('transfer_out', $allowedDirections, true) ? 'transfer_out' : ($allowedDirections[0] ?? null))
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('site_id')
                            ->label(fn (Get $get): string => in_array($get('direction'), ['transfer_out', 'out'], true) ? 'Origin Site (From)' : 'Site')
                            ->relationship('site', 'name', fn (Builder $query) => $query->orderBy('name'))
                            ->required()
                            ->default($user?->site_id)
                            ->disabled($user?->isSiteEngineer() ?? false)
                            ->dehydrated()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('to_site_id')
                            ->label('Destination Site (To)')
                            ->relationship('toSite', 'name', fn (Builder $query) => $query->orderBy('name'))
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => in_array($get('direction'), ['transfer_out', 'out', null, ''], true))
                            ->required(fn (Get $get): bool => $get('direction') === 'transfer_out')
                            ->different('site_id')
                            ->helperText('Select the project site or sub-store receiving this dispatch.'),
                        Forms\Components\Select::make('from_site_id')
                            ->label('Source Site (From)')
                            ->relationship('fromSite', 'name', fn (Builder $query) => $query->orderBy('name'))
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => in_array($get('direction'), ['transfer_in', 'in'], true))
                            ->different('site_id')
                            ->helperText('Select the site or central store this material was sent from.'),
                        Forms\Components\Select::make('material_id')
                            ->relationship('material', 'name', fn (Builder $query) => $query->where('is_active', true)->orderBy('name'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity (Pcs / Units)')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01),
                        Forms\Components\TextInput::make('m2_coverage')
                            ->label('Scaffolding Area Coverage (M² / SET)')
                            ->numeric()
                            ->step(0.01)
                            ->helperText('Square metre coverage or set quantity for billing and site tracking.'),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('ref_no')
                            ->label('Reference / Waybill / Pad # / SIV')
                            ->maxLength(100),
                        Forms\Components\Select::make('maintenance_status')
                            ->label('Maintenance Status')
                            ->options([
                                'none' => 'None / Good Condition',
                                'in_maintenance' => 'In Maintenance (Damaged - Removed from Active Stock)',
                                'cleared' => 'Cleared / Repaired',
                                'scrapped' => 'Scrapped',
                            ])
                            ->default(fn (Get $get): string => $get('direction') === 'damaged' ? 'in_maintenance' : 'none')
                            ->visible(fn (Get $get): bool => in_array($get('direction'), ['damaged', 'in', 'transfer_in'], true) || filled($get('maintenance_status'))),
                        Forms\Components\Select::make('production_stage')
                            ->label('Production Stage')
                            ->options([
                                'none' => 'Standard / Not In-House Production',
                                'on_process' => 'On Process (WIP - Excluded from Available Stock)',
                                'finished' => 'Finished (Counted as Inflow & Ready for Dispatch)',
                            ])
                            ->default('none')
                            ->helperText('For In-House Production facility only. Sets in progress are excluded from active stock until marked Finished.'),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_no')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site.code')
                    ->label('Site')
                    ->sortable(),
                Tables\Columns\TextColumn::make('toSite.code')
                    ->label('To')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('material.name')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('direction')
                    ->colors([
                        'success' => fn ($state): bool => in_array($state, ['in', 'transfer_in'], true),
                        'danger' => fn ($state): bool => in_array($state, ['out', 'transfer_out', 'damaged', 'lost'], true),
                        'warning' => 'adjustment',
                    ]),
                Tables\Columns\TextColumn::make('quantity')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::format((float) $state, precision: 2)
                        : number_format((float) $state, 2)
                    ),
                Tables\Columns\TextColumn::make('m2_coverage')
                    ->label('M² / Set')
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state): string => filled($state)
                        ? (extension_loaded('intl') ? Number::format((float) $state, precision: 2) : number_format((float) $state, 2)).' m²'
                        : '—'
                    )
                    ->toggleable(),
                Tables\Columns\TextColumn::make('maintenance_status')
                    ->label('Maintenance')
                    ->badge()
                    ->colors([
                        'gray' => 'none',
                        'danger' => 'in_maintenance',
                        'success' => 'cleared',
                        'warning' => 'scrapped',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'in_maintenance' => 'In Repair',
                        'cleared' => 'Cleared',
                        'scrapped' => 'Scrapped',
                        default => 'Standard',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('production_stage')
                    ->label('Production')
                    ->badge()
                    ->colors([
                        'gray' => 'none',
                        'warning' => 'on_process',
                        'success' => 'finished',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'on_process' => 'On Process',
                        'finished' => 'Finished',
                        default => 'Standard',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending_approval',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'draft',
                    ]),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created by')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_approval' => 'Pending approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'draft' => 'Draft',
                    ]),
                Tables\Filters\SelectFilter::make('direction')
                    ->options([
                        'in' => 'In',
                        'out' => 'Out',
                        'transfer_out' => 'Transfer out',
                        'transfer_in' => 'Transfer in',
                        'adjustment' => 'Adjustment',
                        'damaged' => 'Damaged',
                        'lost' => 'Lost',
                    ]),
                Tables\Filters\SelectFilter::make('maintenance_status')
                    ->options([
                        'none' => 'Standard / None',
                        'in_maintenance' => 'In Maintenance',
                        'cleared' => 'Cleared / Repaired',
                        'scrapped' => 'Scrapped',
                    ]),
                Tables\Filters\SelectFilter::make('production_stage')
                    ->options([
                        'none' => 'Standard / N/A',
                        'on_process' => 'On Process',
                        'finished' => 'Finished',
                    ]),
                Tables\Filters\SelectFilter::make('site_id')
                    ->relationship('site', 'name')
                    ->visible(fn (): bool => ! FilamentRoleAccess::isSiteEngineer()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (MaterialTransaction $record): bool => $record->status === 'pending_approval'
                        && FilamentRoleAccess::canApproveTransactions())
                    ->action(function (MaterialTransaction $record): void {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        if ($record->linked_transaction_id) {
                            MaterialTransaction::whereKey($record->linked_transaction_id)->update([
                                'status' => 'approved',
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]);
                        }
                    }),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (MaterialTransaction $record): bool => $record->status === 'pending_approval'
                        && FilamentRoleAccess::canApproveTransactions())
                    ->action(function (MaterialTransaction $record): void {
                        $record->update(['status' => 'rejected']);

                        if ($record->linked_transaction_id) {
                            MaterialTransaction::whereKey($record->linked_transaction_id)
                                ->update(['status' => 'rejected']);
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterialTransactions::route('/'),
            'create' => Pages\CreateMaterialTransaction::route('/create'),
            'view' => Pages\ViewMaterialTransaction::route('/{record}'),
            'edit' => Pages\EditMaterialTransaction::route('/{record}/edit'),
        ];
    }
}
