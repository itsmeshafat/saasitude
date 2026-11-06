<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $fillable = ['gateway', 'event_id', 'payload', 'status', 'attempts', 'error_message', 'processed_at'];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function markProcessed(): void
    {
        $this->update(['status' => 'processed', 'processed_at' => now()]);
    }

    public function markFailed(string $error): void
    {
        $this->increment('attempts');
        $this->update(['status' => 'failed', 'error_message' => $error]);
    }
}
