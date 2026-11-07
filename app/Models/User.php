<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use HasinHayder\Tyro\Concerns\HasTyroRoles;
use HasinHayder\TyroLogin\Traits\HasTwoFactorAuth;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property Carbon|null $suspended_at
 * @property string|null $suspension_reason
 * @property string|null $profile_photo_path
 * @property bool $use_gravatar
 * @property-read Collection<int, Workspace&object{pivot: Pivot}> $workspaces
 * @property-read Collection<int, Device> $devices
 * @property-read Collection<int, AppNotification> $appNotifications
 * @property-read Collection<int, DataExport> $dataExports
 * @property-read Collection<int, SupportTicket> $tickets
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasTwoFactorAuth, HasTyroRoles, Notifiable;

    /** @use HasFactory<UserFactory> */

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

    /**
     * @return BelongsToMany<Workspace, $this>
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Device, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * @return HasMany<AppNotification, $this>
     */
    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    /**
     * @return HasMany<DataExport, $this>
     */
    public function dataExports(): HasMany
    {
        return $this->hasMany(DataExport::class);
    }

    /**
     * @return HasMany<SupportTicket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function currentWorkspace(): ?Workspace
    {
        $id = session('current_workspace_id');

        if ($id) {
            $workspace = $this->workspaces()->find($id);
            if ($workspace) {
                return $workspace;
            }
        }

        return $this->workspaces()->where('is_personal', true)->first()
            ?? $this->workspaces()->first();
    }
}
