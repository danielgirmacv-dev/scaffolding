<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalResource\Pages;
use App\Filament\Resources\RentalResource\RelationManagers\ItemsRelationManager;
use App\Models\Rental;
use App\Services\SiteContext;
use App\Support\FilamentRoleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RentalResource extends Resource
{
    protected static ?string $model = Rental::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Rentals & Leases';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->whereIn('status', ['active', 'approved'])
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->startOfDay())
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function canViewAny(): bool
    {
        return FilamentRoleAccess::canViewRentals();
    }

    public static function canCreate(): bool
    {
        return FilamentRoleAccess::canManageRentals();
    }

    public static function canEdit($record): bool
    {
        return FilamentRoleAccess::canManageRentals();
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
                Forms\Components\Section::make('Rental Contract Details')
                    ->description('Equipment lease agreement for internal central store or third-party vendor hire')
                    ->schema([
                        Forms\Components\TextInput::make('rental_no')
                            ->label('Rental Reference #')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Auto-generated (e.g. RNT-20260915-XXXX)'),
                        Forms\Components\Select::make('site_id')
                            ->label('Project Site Context')
                            ->relationship('site', 'name', fn (Builder $query) => $query->orderBy('name'))
                            ->default(fn () => SiteContext::getActiveSiteId())
                            ->required(),
                        Forms\Components\Select::make('rental_source')
                            ->label('Rental Source')
                            ->options([
                                'central_store' => 'Central Store (EEIG Internal)',
                                'external_vendor' => 'External Vendor (Third-Party Hire)',
                            ])
                            ->default('central_store')
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('external_vendor_name')
                            ->label('External Vendor / Supplier Name')
                            ->maxLength(150)
                            ->visible(fn (Get $get): bool => $get('rental_source') === 'external_vendor')
                            ->required(fn (Get $get): bool => $get('rental_source') === 'external_vendor')
                            ->placeholder('e.g. ABC Scaffolding Rental Plc'),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Rental Start Date')
                            ->default(now())
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Rental End Date (Optional)')
                            ->helperText('Leave empty if open-ended ongoing rental'),
                        Forms\Components\Select::make('status')
                            ->label('Rental Status')
                            ->options([
                                'active' => 'Active On-Site',
                                'closed' => 'Closed / Returned',
                            ])
                            ->default('active')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('rental_no')
                    ->label('Rental #')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site.code')
                    ->label('Site')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rental_source')
                    ->label('Source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'central_store' => 'primary',
                        'external_vendor' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'central_store' => 'Central Store',
                        'external_vendor' => 'External Vendor',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('external_vendor_name')
                    ->label('Vendor')
                    ->placeholder('Internal EEIG')
                    ->limit(20),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('M d, Y')
                    ->placeholder('Ongoing')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active', 'approved' => 'success',
                        'closed' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'name')
                    ->visible(fn (): bool => ! FilamentRoleAccess::isSiteEngineer()),
                Tables\Filters\SelectFilter::make('rental_source')
                    ->options([
                        'central_store' => 'Central Store',
                        'external_vendor' => 'External Vendor',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (): bool => FilamentRoleAccess::canManageRentals()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => FilamentRoleAccess::isAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRentals::route('/'),
            'create' => Pages\CreateRental::route('/create'),
            'view' => Pages\ViewRental::route('/{record}'),
            'edit' => Pages\EditRental::route('/{record}/edit'),
        ];
    }
}
