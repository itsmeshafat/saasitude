<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'workspace_id', 'payment_id', 'subscription_id', 'number',
        'amount', 'currency', 'status', 'pdf_path', 'line_items', 'metadata', 'issued_at',
    ];

    protected $casts = [
        'line_items' => 'array',
        'metadata' => 'array',
        'issued_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function formattedAmount(): string
    {
        return number_format($this->amount / 100, 2).' '.strtoupper($this->currency);
    }
}
