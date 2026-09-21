<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalItem extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rental_id',
        'material_id',
        'quantity',
        'unit_day_rate',
        'eeig_discount_pct',
        'effective_rate_per_day',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_day_rate' => 'decimal:4',
            'eeig_discount_pct' => 'decimal:2',
            'effective_rate_per_day' => 'decimal:4',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RentalItem $item): void {
            $item->calculateEffectiveRate();
        });
    }

    /**
     * Pricing Formula: effective_rate = unit_day_rate * (1 - eeig_discount_pct / 100)
     */
    public function calculateEffectiveRate(): float
    {
        $rate = (float) $this->unit_day_rate;
        $discount = (float) $this->eeig_discount_pct;
        $effective = $rate * (1 - ($discount / 100));

        $this->effective_rate_per_day = max(0.0, round($effective, 4));

        return (float) $this->effective_rate_per_day;
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
