<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataExport extends Model
{
    protected $fillable = ['user_id', 'file_path', 'status', 'expires_at', 'downloaded_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

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
