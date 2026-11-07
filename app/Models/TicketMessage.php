<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $support_ticket_id
 * @property int|null $user_id
 * @property bool $is_staff
 * @property string|null $body
 * @property-read SupportTicket $ticket
 * @property-read User|null $user
 */
class TicketMessage extends Model
{
    protected $fillable = ['support_ticket_id', 'user_id', 'is_staff', 'body'];

    protected $casts = [
        'is_staff' => 'boolean',
    ];

    /**
     * @return BelongsTo<SupportTicket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
