<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialTransaction extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'transaction_no',
        'site_id',
        'material_id',
        'direction',
        'quantity',
        'm2_coverage',
        'maintenance_status',
        'production_stage',
        'from_site_id',
        'to_site_id',
        'linked_transaction_id',
        'ref_no',
        'transaction_date',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'ip_address',
        'user_agent',
        'source_sheet',
        'source_row',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'm2_coverage' => 'decimal:2',
            'transaction_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function fromSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'from_site_id');
    }

    public function toSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'to_site_id');
    }

    public function linkedTransaction(): BelongsTo
    {
        return $this->belongsTo(MaterialTransaction::class, 'linked_transaction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPositiveMovement(): bool
    {
        return in_array($this->direction, ['in', 'transfer_in'], true);
    }

    public function isNegativeMovement(): bool
    {
        return in_array($this->direction, ['out', 'transfer_out', 'damaged', 'lost'], true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeForSite(Builder $query, int $siteId): Builder
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeForMaterial(Builder $query, int $materialId): Builder
    {
        return $query->where('material_id', $materialId);
    }

    public function scopeAsOfDate(Builder $query, string $date): Builder
    {
        return $query->where('transaction_date', '<=', $date);
    }

    public function scopeMaintenance(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('direction', 'damaged')
                ->orWhere('maintenance_status', '!=', 'none');
        });
    }

    public function scopeInMaintenance(Builder $query): Builder
    {
        return $query->where('maintenance_status', 'in_maintenance');
    }

    public function scopeProduction(Builder $query): Builder
    {
        return $query->where('production_stage', '!=', 'none');
    }
}
