<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $file_path
 * @property string|null $status
 * @property Carbon|null $expires_at
 * @property Carbon|null $downloaded_at
 * @property-read User $user
 */
class DataExport extends Model
{
    protected $fillable = ['user_id', 'file_path', 'status', 'expires_at', 'downloaded_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready' && ! $this->expired();
    }

    public function expired(): bool
    {
        return $this->expires_at->isPast();
    }
}
