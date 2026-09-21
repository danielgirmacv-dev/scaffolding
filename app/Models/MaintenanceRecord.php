<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class MaintenanceRecord extends MaterialTransaction
{
    protected $table = 'material_transactions';

    protected static function booted(): void
    {
        static::addGlobalScope('maintenance', function (Builder $builder) {
            $builder->where(function (Builder $q) {
                $q->where('direction', 'damaged')
                    ->orWhere('maintenance_status', '!=', 'none');
            });
        });
    }
}
