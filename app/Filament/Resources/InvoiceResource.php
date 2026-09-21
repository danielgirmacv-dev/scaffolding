<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Rental;
use App\Support\FilamentRoleAccess;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Financial / Reports';

    protected static ?string $navigationLabel = 'Rental Invoices';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return FilamentRoleAccess::canViewInvoices();
    }

    public static function canCreate(): bool
    {
        return FilamentRoleAccess::canManageInvoices();
    }

    public static function canEdit($record): bool
    {
        return FilamentRoleAccess::canManageInvoices();
    }

    public static function canDelete($record): bool
    {
        return FilamentRoleAccess::isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Header')
                    ->description('Equipment lease billing context and duration')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_no')
                            ->label('Invoice #')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Auto-generated (e.g. INV-YYYYMMDD-XXXX)'),
                        Forms\Components\Select::make('rental_id')
                            ->label('Rental Agreement')
                            ->relationship('rental', 'rental_no')
                            ->getOptionLabelFromRecordUsing(fn (Rental $record): string => "{$record->rental_no} — [{$record->site?->code}] {$record->site?->name}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                                if ($state && $rental = Rental::with('items')->find($state)) {
                                    if ($rental->start_date && ! $get('period_start')) {
                                        $set('period_start', $rental->start_date->toDateString());
                                    }
                                    if ($rental->end_date && ! $get('period_end')) {
                                        $set('period_end', $rental->end_date->toDateString());
                                    }
                                }
                            }),
                        Forms\Components\DatePicker::make('period_start')
                            ->label('Billing Period Start')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateFields($get, $set)),
                        Forms\Components\DatePicker::make('period_end')
                            ->label('Billing Period End')
                            ->required()
                            ->afterOrEqual('period_start')
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateFields($get, $set)),
                        Forms\Components\TextInput::make('rental_days')
                            ->label('Rental Days')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'draft' => 'Draft',
                                'issued' => 'Issued / Sent',
                                'paid' => 'Paid in Full',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Costing & Calculations (15% VAT)')
                    ->description('Formula: Total Invoice = (Effective Rate * Qty * Rental Days) + 15% VAT')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Gross Subtotal')
                            ->numeric()
                            ->prefix('ETB')
                            ->required(),
                        Forms\Components\TextInput::make('discount_amount')
                            ->label('EEIG Discount Rebate')
                            ->numeric()
                            ->prefix('ETB')
                            ->default(0.00)
                            ->required(),
                        Forms\Components\TextInput::make('vat_amount')
                            ->label('VAT Amount (15%)')
                            ->numeric()
                            ->prefix('ETB')
                            ->required(),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Invoice Amount')
                            ->numeric()
                            ->prefix('ETB')
                            ->extraInputAttributes(['style' => 'font-weight: bold; font-size: 1.1em; color: #d97706;'])
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Billing Notes & Approvals')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function recalculateFields(Get $get, Set $set): void
    {
        $start = $get('period_start');
        $end = $get('period_end');
        $rentalId = $get('rental_id');

        if (! $start || ! $end || ! $rentalId) {
            return;
        }

        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        if ($endDate->lt($startDate)) {
            $set('rental_days', 0);
            $set('subtotal', 0);
            $set('discount_amount', 0);
            $set('vat_amount', 0);
            $set('total_amount', 0);

            return;
        }

        $days = max(1, $startDate->diffInDays($endDate) + 1);
        $set('rental_days', $days);

        $rental = Rental::with('items')->find($rentalId);
        if (! $rental) {
            return;
        }

        $subtotal = 0.0;
        $discountTotal = 0.0;

        if ($rental->items->isNotEmpty()) {
            foreach ($rental->items as $item) {
                $grossCost = (float) $item->unit_day_rate * (float) $item->quantity * $days;
                $effectiveCost = (float) $item->effective_rate_per_day * (float) $item->quantity * $days;
                $subtotal += $grossCost;
                $discountTotal += max(0.0, $grossCost - $effectiveCost);
            }
        } elseif ($rental->quantity_on_rent > 0) {
            $rate = (float) $rental->market_rate_snapshot ?: (float) $rental->effective_daily_rate;
            $subtotal = $rate * (float) $rental->quantity_on_rent * $days;
            $discountTotal = $subtotal * ((float) $rental->discount_percent_snapshot / 100);
        }

        $taxable = max(0.0, $subtotal - $discountTotal);
        $vat = round($taxable * 0.15, 2);
        $total = round($taxable + $vat, 2);

        $set('subtotal', round($subtotal, 2));
        $set('discount_amount', round($discountTotal, 2));
        $set('vat_amount', $vat);
        $set('total_amount', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice #')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rental.rental_no')
                    ->label('Rental Contract')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rental.site.code')
                    ->label('Site')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_start')
                    ->label('Period')
                    ->state(fn (Invoice $record): string => Carbon::parse($record->period_start)->format('M d').' - '.Carbon::parse($record->period_end)->format('M d, Y')),
                Tables\Columns\TextColumn::make('rental_days')
                    ->label('Days')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::currency((float) $state, in: 'ETB')
                        : 'ETB '.number_format((float) $state, 2)
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vat_amount')
                    ->label('VAT (15%)')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::currency((float) $state, in: 'ETB')
                        : 'ETB '.number_format((float) $state, 2)
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total (Inc. VAT)')
                    ->formatStateUsing(fn ($state): string => extension_loaded('intl')
                        ? Number::currency((float) $state, in: 'ETB')
                        : 'ETB '.number_format((float) $state, 2)
                    )
                    ->weight('bold')
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'issued' => 'info',
                        default => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'draft' => 'Draft',
                        'issued' => 'Issued',
                        'paid' => 'Paid',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Print PDF')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn (Invoice $record): string => route('invoices.print', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('recalculate')
                    ->label('Auto-Calculate')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->action(function (Invoice $record): void {
                        $record->calculateAmounts();
                        $record->save();

                        Notification::make()
                            ->title('Invoice Recalculated')
                            ->body("Updated Total: ETB {$record->total_amount} ({$record->rental_days} billing days)")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
