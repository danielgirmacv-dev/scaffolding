<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class FilamentRoleAccess
{
    public static function user(): ?User
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user;
    }

    public static function canAccessPanel(): bool
    {
        $user = self::user();

        return $user !== null && in_array($user->role, [
            'admin',
            'store_keeper',
            'site_engineer',
            'finance',
        ], true);
    }

    public static function isAdmin(): bool
    {
        return self::user()?->isAdmin() ?? false;
    }

    public static function isStoreKeeper(): bool
    {
        return self::user()?->isStoreKeeper() ?? false;
    }

    public static function isSiteEngineer(): bool
    {
        return self::user()?->isSiteEngineer() ?? false;
    }

    public static function isFinance(): bool
    {
        return self::user()?->isFinance() ?? false;
    }

    public static function canManageUsers(): bool
    {
        return self::isAdmin();
    }

    public static function canManageSites(): bool
    {
        return self::isAdmin();
    }

    public static function canViewSites(): bool
    {
        return self::canAccessPanel();
    }

    public static function canManageMaterials(): bool
    {
        return self::isAdmin() || self::isStoreKeeper();
    }

    public static function canViewMaterials(): bool
    {
        return self::canAccessPanel();
    }

    public static function canCreateTransactions(): bool
    {
        return self::isAdmin() || self::isStoreKeeper() || self::isSiteEngineer();
    }

    public static function canEditTransactions(): bool
    {
        return self::isAdmin() || self::isStoreKeeper();
    }

    public static function canViewTransactions(): bool
    {
        return self::canAccessPanel();
    }

    public static function canApproveTransactions(): bool
    {
        return self::isAdmin() || self::isStoreKeeper();
    }

    public static function canViewRentals(): bool
    {
        return self::isAdmin() || self::isFinance() || self::isStoreKeeper() || self::isSiteEngineer();
    }

    public static function canManageRentals(): bool
    {
        return self::isAdmin() || self::isFinance();
    }

    public static function canViewAnomalies(): bool
    {
        return self::isAdmin() || self::isStoreKeeper() || self::isFinance() || self::isSiteEngineer();
    }

    public static function canResolveAnomalies(): bool
    {
        return self::isAdmin() || self::isStoreKeeper();
    }

    public static function canViewInvoices(): bool
    {
        return self::isAdmin() || self::isFinance();
    }

    public static function canManageInvoices(): bool
    {
        return self::isAdmin() || self::isFinance();
    }

    /**
     * @return list<string>
     */
    public static function allowedTransactionDirections(): array
    {
        if (self::isAdmin() || self::isStoreKeeper()) {
            return ['in', 'out', 'transfer_out', 'adjustment', 'damaged', 'lost'];
        }

        if (self::isSiteEngineer()) {
            return ['out', 'damaged', 'lost'];
        }

        return [];
    }

    public static function defaultTransactionStatus(): string
    {
        if (self::isAdmin() || self::isStoreKeeper()) {
            return 'approved';
        }

        return 'pending_approval';
    }

    public static function scopeToUserSite(Builder $query, ?string $column = null): Builder
    {
        $user = self::user();

        if ($user !== null && $user->isSiteEngineer() && $user->site_id !== null) {
            $table = $query->getModel()->getTable();
            $col = $column ?? ($table === 'sites' ? 'id' : 'site_id');
            $query->where($table.'.'.$col, $user->site_id);
        }

        return $query;
    }
}
