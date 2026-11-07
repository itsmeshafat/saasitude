<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $workspace_id
 * @property int|null $subscription_id
 * @property string|null $gateway
 * @property string|null $gateway_payment_id
 * @property int|null $amount
 * @property string|null $currency
 * @property string|null $status
 * @property array|null $metadata
 * @property-read Workspace $workspace
 * @property-read Subscription|null $subscription
 * @property-read Invoice|null $invoice
 */
class Payment extends Model
{
    protected $fillable = [
        'workspace_id', 'subscription_id', 'gateway', 'gateway_payment_id',
        'amount', 'currency', 'status', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return HasOne<Invoice, $this>
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
