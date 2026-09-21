<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rental_no',
        'site_id',
        'rental_source',
        'external_vendor_name',
        'material_id',
        'billing_period',
        'start_date',
        'end_date',
        'days_used',
        'quantity_on_rent',
        'market_rate_snapshot',
        'discount_percent_snapshot',
        'depreciation_rate_snapshot',
        'effective_daily_rate',
        'subtotal_cost',
        'vat_percent',
        'vat_amount',
        'grand_total_cost',
        'status',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'days_used' => 'integer',
            'quantity_on_rent' => 'decimal:2',
            'market_rate_snapshot' => 'decimal:4',
            'discount_percent_snapshot' => 'decimal:2',
            'depreciation_rate_snapshot' => 'decimal:4',
            'effective_daily_rate' => 'decimal:4',
            'subtotal_cost' => 'decimal:2',
            'vat_percent' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'grand_total_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Rental $rental): void {
            if (empty($rental->rental_no)) {
                $rental->rental_no = 'RNT-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
            }
            if (empty($rental->billing_period) && $rental->start_date) {
                $rental->billing_period = Carbon::parse($rental->start_date)->format('Y-m');
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Computed total daily rate across all rental items.
     */
    public function totalEffectiveDailyRate(): float
    {
        if ($this->items->isNotEmpty()) {
            return (float) $this->items->sum(function (RentalItem $item): float {
                return (float) $item->effective_rate_per_day * (float) $item->quantity;
            });
        }

        return (float) $this->effective_daily_rate * (float) $this->quantity_on_rent;
    }

    /**
     * Days used computed dynamically or from snapshot.
     */
    protected function calculatedDays(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                if ($this->days_used > 0) {
                    return $this->days_used;
                }

                if ($this->start_date && $this->end_date) {
                    return max(1, Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1);
                }

                return 0;
            }
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'approved']);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    public function scopeForPeriod(Builder $query, string $period): Builder
    {
        return $query->where('billing_period', $period);
    }

    public function scopeForSite(Builder $query, int $siteId): Builder
    {
        return $query->where('site_id', $siteId);
    }
}
