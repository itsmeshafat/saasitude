<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $workspace_id
 * @property int|null $payment_id
 * @property int|null $subscription_id
 * @property string|null $number
 * @property int|null $amount
 * @property string|null $currency
 * @property string|null $status
 * @property string|null $pdf_path
 * @property array|null $line_items
 * @property array|null $metadata
 * @property Carbon|null $issued_at
 * @property-read Workspace $workspace
 * @property-read Payment|null $payment
 * @property-read Subscription|null $subscription
 */
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

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function formattedAmount(): string
    {
        return number_format($this->amount / 100, 2).' '.strtoupper($this->currency);
    }
}
