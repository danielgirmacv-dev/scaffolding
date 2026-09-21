<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Models\Material;
use App\Support\FilamentRoleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return FilamentRoleAccess::canViewMaterials();
    }

    public static function canCreate(): bool
    {
        return FilamentRoleAccess::canManageMaterials();
    }

    public static function canEdit($record): bool
    {
        return FilamentRoleAccess::canManageMaterials();
    }

    public static function canDelete($record): bool
    {
        if (! FilamentRoleAccess::canManageMaterials()) {
            return false;
        }

        // Prevent deletion if material is referenced in transactions, rentals, or rental items
        if ($record instanceof Material) {
            return ! $record->transactions()->exists()
                && ! $record->rentals()->exists()
                && ! $record->rentalItems()->exists();
        }

        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Catalog')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('item_code')
                            ->label('Item Code / SKU')
                            ->placeholder('e.g. SCAF-CL-02 or FORM-IB-60')
                            ->helperText('Format: SCAF-[CODE]-[NUM] for Scaffolding, FORM-[CODE]-[NUM] for Formwork.')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\Select::make('category')
                            ->options([
                                'Scaffolding' => 'Scaffolding',
                                'Formwork' => 'Formwork',
                            ])
                            ->required()
                            ->default('Scaffolding'),
                        Forms\Components\TextInput::make('unit_of_measure')
                            ->required()
                            ->maxLength(30)
                            ->default('Pcs'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),
                Forms\Components\Section::make('Rental rates')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('market_rate_per_day')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('eeig_discount_percent')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(25)
                            ->suffix('%'),
                        Forms\Components\TextInput::make('depreciation_rate_per_day')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('replacement_cost')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('ETB'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('Unit'),
                Tables\Columns\TextColumn::make('market_rate_per_day')
                    ->label('Market/day')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::format((float) $state, precision: 2)
                        : number_format((float) $state, 2)
                    ),
                Tables\Columns\TextColumn::make('effective_daily_rate')
                    ->label('Effective/day')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::format((float) $state, precision: 4)
                        : number_format((float) $state, 4)
                    ),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Scaffolding' => 'Scaffolding',
                        'Formwork' => 'Formwork',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Material $record): void {
                        if ($record->transactions()->exists() || $record->rentals()->exists() || $record->rentalItems()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot Delete Material')
                                ->body("Material '{$record->name}' has existing ledger transactions or rental records and cannot be deleted. Consider deactivating it instead.")
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    }),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make()
                    ->before(function (Tables\Actions\ForceDeleteAction $action, Material $record): void {
                        if ($record->transactions()->exists() || $record->rentals()->exists() || $record->rentalItems()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot Permanently Delete Material')
                                ->body("Material '{$record->name}' has permanent transaction references and cannot be deleted due to ledger audit constraints.")
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $deleted = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                if ($record->transactions()->exists() || $record->rentals()->exists() || $record->rentalItems()->exists()) {
                                    $skipped++;
                                } else {
                                    $record->delete();
                                    $deleted++;
                                }
                            }

                            if ($skipped > 0) {
                                Notification::make()
                                    ->warning()
                                    ->title('Bulk Delete Warning')
                                    ->body("Deleted {$deleted} materials. Skipped {$skipped} materials because they have active ledger or rental history.")
                                    ->persistent()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->success()
                                    ->title("{$deleted} materials deleted successfully.")
                                    ->send();
                            }
                        }),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'view' => Pages\ViewMaterial::route('/{record}'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
