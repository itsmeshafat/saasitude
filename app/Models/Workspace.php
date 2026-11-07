<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $owner_id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $logo_path
 * @property bool $is_personal
 * @property array|null $settings
 * @property Carbon|null $trial_used_at
 * @property Carbon|null $suspended_at
 * @property-read User $owner
 * @property-read Collection<int, WorkspaceMember> $members
 * @property-read Collection<int, WorkspaceMember> $workspaceMembers
 * @property-read Collection<int, WorkspaceInvitation> $invitations
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read Subscription|null $latestSubscription
 * @property-read Collection<int, GatewayCustomer> $gatewayCustomers
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, Invoice> $invoices
 */
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * @return HasMany<WorkspaceMember, $this>
     */
    public function workspaceMembers(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    /**
     * @return HasMany<WorkspaceInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** Latest subscription of any status — eager-loadable, unlike activeSubscription(). */
    /**
     * @return HasOne<Subscription, $this>
     */
    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->latest()
            ->first();
    }

    /**
     * @return HasMany<GatewayCustomer, $this>
     */
    public function gatewayCustomers(): HasMany
    {
        return $this->hasMany(GatewayCustomer::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
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
