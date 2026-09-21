<?php

namespace App\Filament\Resources\RentalResource\RelationManagers;

use App\Models\Material;
use App\Models\RentalItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Rental Items & Scaffolding Breakdown';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('material_id')
                    ->label('Material / Component')
                    ->relationship('material', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set): void {
                        if ($material = Material::find($state)) {
                            $set('unit_day_rate', $material->market_rate_per_day);
                            $set('eeig_discount_pct', $material->eeig_discount_percent);

                            $discount = (float) $material->eeig_discount_percent;
                            $rate = (float) $material->market_rate_per_day;
                            $effective = $rate * (1 - ($discount / 100));
                            $set('effective_rate_per_day', round($effective, 4));
                        }
                    }),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->minValue(0.01)
                    ->required(),
                Forms\Components\TextInput::make('unit_day_rate')
                    ->label('Market Unit Day Rate')
                    ->numeric()
                    ->prefix('ETB')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        $rate = (float) $get('unit_day_rate');
                        $discount = (float) $get('eeig_discount_pct');
                        $effective = $rate * (1 - ($discount / 100));
                        $set('effective_rate_per_day', round($effective, 4));
                    }),
                Forms\Components\TextInput::make('eeig_discount_pct')
                    ->label('EEIG Discount %')
                    ->numeric()
                    ->suffix('%')
                    ->default(0.00)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        $rate = (float) $get('unit_day_rate');
                        $discount = (float) $get('eeig_discount_pct');
                        $effective = $rate * (1 - ($discount / 100));
                        $set('effective_rate_per_day', round($effective, 4));
                    }),
                Forms\Components\TextInput::make('effective_rate_per_day')
                    ->label('Effective Daily Rate')
                    ->numeric()
                    ->prefix('ETB')
                    ->helperText('Formula: unit_day_rate * (1 - eeig_discount_pct / 100)')
                    ->disabled()
                    ->dehydrated(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('material.item_code')
                    ->label('SKU')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('material.name')
                    ->label('Item Description')
                    ->wrap(),
                Tables\Columns\TextColumn::make('material.unit_of_measure')
                    ->label('Unit')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::format((float) $state, precision: 2)
                        : number_format((float) $state, 2)
                    )
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('unit_day_rate')
                    ->label('Market Rate/Day')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::currency((float) $state, in: 'ETB')
                        : 'ETB '.number_format((float) $state, 2)
                    ),
                Tables\Columns\TextColumn::make('eeig_discount_pct')
                    ->label('EEIG Disc%')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('effective_rate_per_day')
                    ->label('Effective Rate/Day')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::currency((float) $state, in: 'ETB')
                        : 'ETB '.number_format((float) $state, 2)
                    )
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('daily_subtotal')
                    ->label('Daily Subtotal')
                    ->state(function (RentalItem $record): float {
                        return round((float) $record->effective_rate_per_day * (float) $record->quantity, 2);
                    })
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::currency((float) $state, in: 'ETB')
                        : 'ETB '.number_format((float) $state, 2)
                    )
                    ->weight('bold'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
