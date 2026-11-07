<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $gateway
 * @property string|null $event_id
 * @property array|null $payload
 * @property string|null $status
 * @property int|null $attempts
 * @property string|null $error_message
 * @property Carbon|null $processed_at
 */
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
