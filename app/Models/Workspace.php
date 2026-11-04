<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'logo_path',
        'is_personal',
        'settings',
        'trial_used_at',
        'suspended_at',
    ];

    protected $casts = [
        'is_personal' => 'boolean',
        'settings' => 'array',
        'trial_used_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function workspaceMembers(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->latest()
            ->first();
    }

    public function gatewayCustomers(): HasMany
    {
        return $this->hasMany(GatewayCustomer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }
}
