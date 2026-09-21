<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'site_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function transactionsCreated(): HasMany
    {
        return $this->hasMany(MaterialTransaction::class, 'created_by');
    }

    public function transactionsApproved(): HasMany
    {
        return $this->hasMany(MaterialTransaction::class, 'approved_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStoreKeeper(): bool
    {
        return in_array($this->role, ['store_keeper', 'admin'], true);
    }

    public function isFinance(): bool
    {
        return in_array($this->role, ['finance', 'admin'], true);
    }

    public function isSiteEngineer(): bool
    {
        return $this->role === 'site_engineer';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, [
            'admin',
            'store_keeper',
            'site_engineer',
            'finance',
        ], true);
    }
}
