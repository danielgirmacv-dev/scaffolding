<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceRecordResource\Pages;
use App\Models\MaintenanceRecord;
use App\Support\FilamentRoleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Number;

class MaintenanceRecordResource extends Resource
{
    protected static ?string $model = MaintenanceRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Maintenance Register';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Maintenance Record';

    protected static ?string $pluralModelLabel = 'Maintenance Register';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('maintenance_status', 'in_maintenance')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Items currently in maintenance / awaiting repair';
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
        return FilamentRoleAccess::isAdmin();
    }

    public static function getEloquentQuery(): Builder
    {
        return FilamentRoleAccess::scopeToUserSite(parent::getEloquentQuery());
    }

    public static function form(Form $form): Form
    {
        $user = FilamentRoleAccess::user();

        return $form
            ->schema([
                Forms\Components\Section::make('Damage / Maintenance Entry')
                    ->description('Record scaffolding material that has been damaged on site or sent for repair.')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('site_id')
                            ->label('Site')
                            ->relationship('site', 'name', fn (Builder $query) => $query->orderBy('name'))
                            ->required()
                            ->default($user?->site_id)
                            ->disabled($user?->isSiteEngineer() ?? false)
                            ->dehydrated()
                            ->searchable()
                            ->preload()
                            ->helperText('The site where the damage occurred.'),

                        Forms\Components\Select::make('material_id')
                            ->label('Material / Item')
                            ->relationship('material', 'name', fn (Builder $query) => $query->where('is_active', true)->orderBy('name'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Select the damaged or defective material item.'),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity (Pcs / Units)')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01),

                        Forms\Components\TextInput::make('m2_coverage')
                            ->label('Scaffolding Area (M² / SET)')
                            ->numeric()
                            ->step(0.01)
                            ->helperText('Square metre or set coverage of the damaged equipment.'),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Date of Damage / Entry')
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('ref_no')
                            ->label('Reference / Report No.')
                            ->maxLength(100)
                            ->helperText('Damage report reference, inspection form, or pad number.'),

                        Forms\Components\Hidden::make('direction')
                            ->default('damaged'),

                        Forms\Components\Select::make('maintenance_status')
                            ->label('Maintenance Status')
                            ->options([
                                'in_maintenance' => '🔧 In Maintenance (Removed from Active Stock)',
                                'cleared' => '✅ Cleared / Repaired (Returned to Stock)',
                                'scrapped' => '🗑️ Scrapped (Written Off)',
                            ])
                            ->default('in_maintenance')
                            ->required()
                            ->helperText('Cleared items are returned to active stock; Scrapped items are written off permanently.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes / Description of Damage')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Describe the nature of the damage, repair instructions, or disposal reason.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_no')
                    ->label('Ref #')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('site.code')
                    ->label('Site')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('material.name')
                    ->label('Material / Item')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::format((float) $state, precision: 2)
                        : number_format((float) $state, 2)
                    )
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('m2_coverage')
                    ->label('M² / Set')
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state): string => filled($state)
                        ? (extension_loaded('intl') ? Number::format((float) $state, precision: 2) : number_format((float) $state, 2)).' m²'
                        : '—'
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('maintenance_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'in_maintenance' => 'In Maintenance',
                        'cleared' => 'Cleared / Repaired',
                        'scrapped' => 'Scrapped',
                        default => 'Logged',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'in_maintenance' => 'danger',
                        'cleared' => 'success',
                        'scrapped' => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('ref_no')
                    ->label('Reference')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Logged by')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('maintenance_status')
                    ->label('Status')
                    ->options([
                        'in_maintenance' => 'In Maintenance',
                        'cleared' => 'Cleared / Repaired',
                        'scrapped' => 'Scrapped',
                    ]),

                Tables\Filters\SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'name')
                    ->visible(fn (): bool => ! FilamentRoleAccess::isSiteEngineer()),

                Tables\Filters\Filter::make('active_maintenance')
                    ->label('In Maintenance Only')
                    ->query(fn (Builder $query): Builder => $query->where('maintenance_status', 'in_maintenance'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (): bool => FilamentRoleAccess::canEditTransactions()),

                Tables\Actions\Action::make('mark_cleared')
                    ->label('Mark Cleared')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Cleared / Repaired')
                    ->modalDescription('This will mark the item as repaired and return it to active stock. Continue?')
                    ->visible(fn (MaintenanceRecord $record): bool => $record->maintenance_status === 'in_maintenance'
                        && FilamentRoleAccess::canEditTransactions()
                    )
                    ->action(function (MaintenanceRecord $record): void {
                        $record->update(['maintenance_status' => 'cleared']);
                        Notification::make()
                            ->title('Item marked as Cleared / Repaired')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_scrapped')
                    ->label('Mark Scrapped')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Scrapped / Written Off')
                    ->modalDescription('This will permanently write off the item. It will not return to active stock. Continue?')
                    ->visible(fn (MaintenanceRecord $record): bool => $record->maintenance_status === 'in_maintenance'
                        && FilamentRoleAccess::canEditTransactions()
                    )
                    ->action(function (MaintenanceRecord $record): void {
                        $record->update(['maintenance_status' => 'scrapped']);
                        Notification::make()
                            ->title('Item marked as Scrapped')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\Action::make('reopen')
                    ->label('Re-open')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('This will move the record back to "In Maintenance" status.')
                    ->visible(fn (MaintenanceRecord $record): bool => in_array($record->maintenance_status, ['cleared', 'scrapped'], true)
                        && FilamentRoleAccess::isAdmin()
                    )
                    ->action(function (MaintenanceRecord $record): void {
                        $record->update(['maintenance_status' => 'in_maintenance']);
                        Notification::make()
                            ->title('Record re-opened — status set to In Maintenance')
                            ->info()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (): bool => FilamentRoleAccess::isAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_clear')
                        ->label('Mark Selected as Cleared')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => FilamentRoleAccess::canEditTransactions())
                        ->action(function (Collection $records): void {
                            $records->each(fn ($r) => $r->update(['maintenance_status' => 'cleared']));
                            Notification::make()
                                ->title($records->count().' item(s) marked as Cleared')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulk_scrap')
                        ->label('Mark Selected as Scrapped')
                        ->icon('heroicon-o-trash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => FilamentRoleAccess::canEditTransactions())
                        ->action(function (Collection $records): void {
                            $records->each(fn ($r) => $r->update(['maintenance_status' => 'scrapped']));
                            Notification::make()
                                ->title($records->count().' item(s) marked as Scrapped')
                                ->warning()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => FilamentRoleAccess::isAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceRecords::route('/'),
            'create' => Pages\CreateMaintenanceRecord::route('/create'),
            'view' => Pages\ViewMaintenanceRecord::route('/{record}'),
            'edit' => Pages\EditMaintenanceRecord::route('/{record}/edit'),
        ];
    }
}
