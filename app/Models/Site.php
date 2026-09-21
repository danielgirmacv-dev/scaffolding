<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'client',
        'location',
        'is_central_store',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_central_store' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MaterialTransaction::class);
    }

    public function outboundTransfers(): HasMany
    {
        return $this->hasMany(MaterialTransaction::class, 'from_site_id');
    }

    public function inboundTransfers(): HasMany
    {
        return $this->hasMany(MaterialTransaction::class, 'to_site_id');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, Rental::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function anomalies(): HasMany
    {
        return $this->hasMany(StockAnomaly::class);
    }

    public const INTERNAL_CODES = [
        'CENTRAL STORE',
        'CS',
        'C/STORE',
        'SCAFF',
        'SCAFF-CHAKA',
        'CHAKA',
        'PRODUCTION',
    ];

    public const SUB_STORE_CODES = [
        'SCAFF',
        'SCAFF-CHAKA',
        'CHAKA',
    ];

    public function isCentralStore(): bool
    {
        return $this->is_central_store || in_array($this->code, ['CENTRAL STORE', 'CS', 'C/STORE'], true);
    }

    public function isSubStore(): bool
    {
        return in_array($this->code, self::SUB_STORE_CODES, true);
    }

    public function isProduction(): bool
    {
        return $this->code === 'PRODUCTION';
    }

    public function isOutOfAddis(): bool
    {
        return $this->code === 'JIMMA';
    }

    public function isClientProject(): bool
    {
        return ! $this->isCentralStore() && ! $this->isSubStore() && ! $this->isProduction();
    }

    public function getTypeLabelAttribute(): string
    {
        if ($this->isCentralStore()) {
            return 'Central Store';
        }

        if ($this->isSubStore()) {
            return 'Internal Sub-Store';
        }

        if ($this->isProduction()) {
            return 'In-House Production';
        }

        if ($this->isOutOfAddis()) {
            return 'Project Site (Out-of-Addis)';
        }

        return 'Project Site';
    }

    public function scopeCentralStore(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('is_central_store', true)
                ->orWhereIn('code', ['CENTRAL STORE', 'CS', 'C/STORE']);
        });
    }

    public function scopeSubStores(Builder $query): Builder
    {
        return $query->whereIn('code', self::SUB_STORE_CODES);
    }

    public function scopeInternalLocations(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('is_central_store', true)
                ->orWhereIn('code', self::INTERNAL_CODES);
        });
    }

    public function scopeProjectSites(Builder $query): Builder
    {
        return $query->where('is_central_store', false)
            ->whereNotIn('code', self::INTERNAL_CODES);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Compute current on-site stock for a specific material.
     * Formula: SUM(direction in ['in', 'transfer_in']) - SUM(direction in ['out', 'transfer_out', 'damaged', 'lost'])
     */
    public function getStockForMaterial(int $materialId): float
    {
        $inQty = (float) $this->transactions()
            ->where('material_id', $materialId)
            ->whereIn('direction', ['in', 'transfer_in', 'adjustment'])
            ->where('production_stage', '!=', 'on_process')
            ->where('maintenance_status', '!=', 'in_maintenance')
            ->sum('quantity');

        $outQty = (float) $this->transactions()
            ->where('material_id', $materialId)
            ->whereIn('direction', ['out', 'transfer_out', 'damaged', 'lost'])
            ->sum('quantity');

        return max(0.0, round($inQty - $outQty, 2));
    }
}
