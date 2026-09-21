<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Models\Site;
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

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return FilamentRoleAccess::canViewSites();
    }

    public static function canCreate(): bool
    {
        return FilamentRoleAccess::canManageSites();
    }

    public static function canEdit($record): bool
    {
        return FilamentRoleAccess::canManageSites();
    }

    public static function canDelete($record): bool
    {
        if (! FilamentRoleAccess::canManageSites()) {
            return false;
        }

        if ($record instanceof Site) {
            if ($record->is_central_store) {
                return false;
            }

            return ! $record->transactions()->exists()
                && ! $record->rentals()->exists()
                && ! $record->users()->exists();
        }

        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return FilamentRoleAccess::scopeToUserSite(
            parent::getEloquentQuery()->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]),
            'id'
        );
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(150),
                Forms\Components\TextInput::make('client')
                    ->maxLength(150),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_central_store')
                    ->label('Central store'),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'suspended' => 'Suspended',
                    ])
                    ->required()
                    ->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('type_label')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'primary' => fn ($state): bool => $state === 'Project Site',
                        'success' => fn ($state): bool => $state === 'Central Store',
                        'warning' => fn ($state): bool => in_array($state, ['Internal Sub-Store', 'In-House Production']),
                        'danger' => fn ($state): bool => $state === 'Project Site (Out-of-Addis)',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'suspended',
                        'gray' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Transactions'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'suspended' => 'Suspended',
                    ]),
                Tables\Filters\SelectFilter::make('site_type')
                    ->label('Site Classification')
                    ->options([
                        'project' => 'Project Site',
                        'central' => 'Central Store',
                        'sub_store' => 'Internal Sub-Store',
                        'production' => 'In-House Production',
                        'out_of_addis' => 'Project Site (Out-of-Addis)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'project' => $query->where('is_central_store', false)->whereNotIn('code', Site::INTERNAL_CODES)->where('code', '!=', 'JIMMA'),
                            'central' => $query->where(fn (Builder $q) => $q->where('is_central_store', true)->orWhereIn('code', ['CENTRAL STORE', 'CS', 'C/STORE'])),
                            'sub_store' => $query->whereIn('code', Site::SUB_STORE_CODES),
                            'production' => $query->where('code', 'PRODUCTION'),
                            'out_of_addis' => $query->where('code', 'JIMMA'),
                            default => $query,
                        };
                    }),
                Tables\Filters\TernaryFilter::make('is_central_store')
                    ->label('Central store'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Site $record): void {
                        if ($record->is_central_store) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot Delete Central Store')
                                ->body('The Central Store is the core inventory hub and cannot be deleted.')
                                ->persistent()
                                ->send();

                            $action->halt();
                        }

                        if ($record->transactions()->exists() || $record->rentals()->exists() || $record->users()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot Delete Site')
                                ->body("Site '{$record->name}' has recorded transactions, rentals, or assigned users. Consider changing its status to 'Completed' or 'Suspended' instead.")
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    }),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make()
                    ->before(function (Tables\Actions\ForceDeleteAction $action, Site $record): void {
                        if ($record->transactions()->exists() || $record->rentals()->exists() || $record->users()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot Permanently Delete Site')
                                ->body("Site '{$record->name}' has existing database history and cannot be permanently removed.")
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
                                if ($record->is_central_store || $record->transactions()->exists() || $record->rentals()->exists() || $record->users()->exists()) {
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
                                    ->body("Deleted {$deleted} sites. Skipped {$skipped} sites because they are central stores or have associated history.")
                                    ->persistent()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->success()
                                    ->title("{$deleted} sites deleted successfully.")
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
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'view' => Pages\ViewSite::route('/{record}'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}
