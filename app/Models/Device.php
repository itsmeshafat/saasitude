<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $push_token
 * @property string|null $platform
 * @property string|null $app_version
 * @property Carbon|null $last_used_at
 * @property-read User $user
 */
class Device extends Model
{
    protected $fillable = ['user_id', 'push_token', 'platform', 'app_version', 'last_used_at'];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
