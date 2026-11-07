<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string|null $number
 * @property int|null $user_id
 * @property int|null $workspace_id
 * @property string|null $subject
 * @property string|null $category
 * @property string|null $status
 * @property Carbon|null $closed_at
 * @property string|null $guest_email
 * @property-read User $user
 * @property-read Workspace $workspace
 * @property-read Collection<int, TicketMessage> $messages
 */
class SupportTicket extends Model
{
    protected $fillable = [
        'number', 'user_id', 'workspace_id', 'guest_email', 'subject', 'category', 'status', 'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * @return HasMany<TicketMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function close(): void
    {
        $this->forceFill(['status' => 'closed', 'closed_at' => now()])->save();
    }
}
