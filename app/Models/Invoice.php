<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'invoice_no',
        'rental_id',
        'period_start',
        'period_end',
        'rental_days',
        'subtotal',
        'discount_amount',
        'vat_amount',
        'total_amount',
        'payment_status',
        'notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'rental_days' => 'integer',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice): void {
            if (empty($invoice->invoice_no)) {
                $invoice->invoice_no = 'INV-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
            }
        });
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate invoice based on rental items and days.
     * Total Invoice = (effective_rate * qty * rental_days) + 15% VAT
     */
    public function calculateAmounts(): void
    {
        if ($this->period_start && $this->period_end) {
            $days = Carbon::parse($this->period_start)->diffInDays(Carbon::parse($this->period_end)) + 1;
            $this->rental_days = max(1, (int) $days);
        }

        $rental = $this->rental()->with('items')->first();

        $subtotal = 0.0;
        $discountTotal = 0.0;

        if ($rental && $rental->items->isNotEmpty()) {
            foreach ($rental->items as $item) {
                $grossItemCost = (float) $item->unit_day_rate * (float) $item->quantity * $this->rental_days;
                $effectiveItemCost = (float) $item->effective_rate_per_day * (float) $item->quantity * $this->rental_days;
                $discount = $grossItemCost - $effectiveItemCost;

                $subtotal += $grossItemCost;
                $discountTotal += max(0.0, $discount);
            }
        } elseif ($rental && $rental->quantity_on_rent > 0) {
            // Support legacy single-item rental snapshot
            $rate = (float) $rental->market_rate_snapshot ?: (float) $rental->effective_daily_rate;
            $subtotal = $rate * (float) $rental->quantity_on_rent * $this->rental_days;
            $discountTotal = $subtotal * ((float) $rental->discount_percent_snapshot / 100);
        }

        $taxable = max(0.0, $subtotal - $discountTotal);
        $vat = round($taxable * 0.15, 2);
        $total = round($taxable + $vat, 2);

        $this->subtotal = round($subtotal, 2);
        $this->discount_amount = round($discountTotal, 2);
        $this->vat_amount = $vat;
        $this->total_amount = $total;
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('payment_status', 'issued');
    }
}
