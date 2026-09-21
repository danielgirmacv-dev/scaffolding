<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'item_code',
        'name',
        'category',
        'unit_of_measure',
        'market_rate_per_day',
        'eeig_discount_percent',
        'depreciation_rate_per_day',
        'replacement_cost',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'market_rate_per_day' => 'decimal:4',
            'eeig_discount_percent' => 'decimal:2',
            'depreciation_rate_per_day' => 'decimal:4',
            'replacement_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Alias for unit / unit_of_measure
     */
    protected function unit(): Attribute
    {
        return Attribute::make(
            get: fn (): string => (string) ($this->unit_of_measure ?? 'Pcs'),
            set: fn (?string $value): array => ['unit_of_measure' => $value ?? 'Pcs']
        );
    }

    /**
     * Alias for current_market_rate_per_day / market_rate_per_day
     */
    protected function currentMarketRatePerDay(): Attribute
    {
        return Attribute::make(
            get: fn (): float => (float) ($this->market_rate_per_day ?? 0.0),
            set: fn ($value): array => ['market_rate_per_day' => $value]
        );
    }

    /**
     * Alias for eeig_discount_pct / eeig_discount_percent
     */
    protected function eeigDiscountPct(): Attribute
    {
        return Attribute::make(
            get: fn (): float => (float) ($this->eeig_discount_percent ?? 0.0),
            set: fn ($value): array => ['eeig_discount_percent' => $value]
        );
    }

    /**
     * Compute effective daily rental rate after EEIG discount and depreciation.
     * effective_rate = (market_rate * (1 - discount%)) - depreciation_rate
     */
    protected function effectiveDailyRate(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $discountFactor = 1 - ((float) $this->eeig_discount_percent / 100);
                $discounted = (float) $this->market_rate_per_day * $discountFactor;
                $effective = $discounted - (float) $this->depreciation_rate_per_day;

                return max(0.0, round($effective, 4));
            }
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MaterialTransaction::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function rentalItems(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    public function anomalies(): HasMany
    {
        return $this->hasMany(StockAnomaly::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
