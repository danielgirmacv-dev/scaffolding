<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAnomalyResource\Pages;
use App\Models\StockAnomaly;
use App\Support\FilamentRoleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class StockAnomalyResource extends Resource
{
    protected static ?string $model = StockAnomaly::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Stock Anomalies';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->whereIn('status', ['open', 'investigating'])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function canViewAny(): bool
    {
        return FilamentRoleAccess::canViewAnomalies();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return FilamentRoleAccess::canResolveAnomalies();
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
        return $form
            ->schema([
                Forms\Components\Section::make('Anomaly details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('source_sheet')
                            ->disabled(),
                        Forms\Components\TextInput::make('source_row')
                            ->disabled(),
                        Forms\Components\TextInput::make('site.name')
                            ->label('Site')
                            ->disabled(),
                        Forms\Components\TextInput::make('material.name')
                            ->label('Material')
                            ->disabled(),
                        Forms\Components\TextInput::make('calculated_negative_balance')
                            ->label('Negative balance')
                            ->disabled(),
                        Forms\Components\TextInput::make('error_type')
                            ->disabled(),
                        Forms\Components\Textarea::make('message')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Resolution')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'investigating' => 'Investigating',
                                'resolved' => 'Resolved',
                                'ignored' => 'Ignored',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('resolution_notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('site.code')
                    ->label('Site')
                    ->sortable(),
                Tables\Columns\TextColumn::make('material.name')
                    ->label('Material')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('error_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('calculated_negative_balance')
                    ->label('Balance')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::format((float) $state, precision: 2)
                        : number_format((float) $state, 2)
                    )
                    ->color('danger'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'investigating',
                        'success' => 'resolved',
                        'gray' => 'ignored',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'investigating' => 'Investigating',
                        'resolved' => 'Resolved',
                        'ignored' => 'Ignored',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Resolve')
                    ->visible(fn (): bool => FilamentRoleAccess::canResolveAnomalies()),
                Tables\Actions\Action::make('resolve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (StockAnomaly $record): bool => in_array($record->status, ['open', 'investigating'], true)
                        && FilamentRoleAccess::canResolveAnomalies())
                    ->form([
                        Forms\Components\Textarea::make('resolution_notes')
                            ->required(),
                    ])
                    ->action(function (StockAnomaly $record, array $data): void {
                        $record->update([
                            'status' => 'resolved',
                            'resolved_by' => auth()->id(),
                            'resolution_notes' => $data['resolution_notes'],
                        ]);
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockAnomalies::route('/'),
            'view' => Pages\ViewStockAnomaly::route('/{record}'),
            'edit' => Pages\EditStockAnomaly::route('/{record}/edit'),
        ];
    }
}
