<?php

namespace App\Models;

use App\Events\PaymentFailed;
use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCanceled;
use App\Services\EntitlementService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $workspace_id
 * @property int $plan_id
 * @property string|null $gateway
 * @property string|null $gateway_subscription_id
 * @property string|null $gateway_customer_id
 * @property string|null $status
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $current_period_start
 * @property Carbon|null $current_period_end
 * @property bool $cancel_at_period_end
 * @property Carbon|null $canceled_at
 * @property array|null $metadata
 * @property bool $app_driven_renewal
 * @property-read Workspace $workspace
 * @property-read Plan $plan
 */
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id', 'plan_id', 'gateway', 'gateway_subscription_id',
        'gateway_customer_id', 'status', 'trial_ends_at', 'current_period_start',
        'current_period_end', 'cancel_at_period_end', 'app_driven_renewal', 'canceled_at', 'metadata',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'app_driven_renewal' => 'boolean',
        'canceled_at' => 'datetime',
        'last_event_at' => 'datetime',
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
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    /**
     * Monotonic webhook guard (ADR-008): an event older than the last
     * applied event time must be ignored, so out-of-order delivery or
     * admin replay can never rewind state (e.g. revive a canceled
     * subscription with a stale "active" snapshot). Events without a
     * timestamp are always applied — we can't judge their age. Gateway
     * timestamps have second resolution, so two events within the same
     * second can still reorder; the mark tolerates ties (lt, not lte).
     */
    public function eventIsStale(?Carbon $eventTime): bool
    {
        if ($eventTime === null || $this->last_event_at === null) {
            return false;
        }

        return $eventTime->lt($this->last_event_at)
            // Cancellation is terminal: even without a high-water mark,
            // nothing older than canceled_at may revive the subscription.
            || ($this->canceled_at !== null && $this->status === 'canceled' && $eventTime->lt($this->canceled_at));
    }

    /** Advance the high-water mark; never moves backwards. */
    public function markEventApplied(?Carbon $eventTime): void
    {
        if ($eventTime === null) {
            return;
        }

        if ($this->last_event_at === null || $eventTime->gt($this->last_event_at)) {
            $this->forceFill(['last_event_at' => $eventTime])->save();
        }
    }

    public function activate(array $periodData = []): void
    {
        $this->update(array_merge(['status' => 'active', 'cancel_at_period_end' => false], $periodData));
        app(EntitlementService::class)->flush($this->workspace_id);
        event(new SubscriptionActivated($this));
    }

    public function markPastDue(): void
    {
        $this->update(['status' => 'past_due']);
        app(EntitlementService::class)->flush($this->workspace_id);
        event(new PaymentFailed($this));
    }

    public function cancel(bool $immediately = false): void
    {
        if ($immediately) {
            $this->update(['status' => 'canceled', 'canceled_at' => now(), 'cancel_at_period_end' => false]);
        } else {
            $this->update(['cancel_at_period_end' => true, 'canceled_at' => $this->current_period_end]);
        }
        event(new SubscriptionCanceled($this));
    }

    public function resume(): void
    {
        $this->update(['status' => 'active', 'cancel_at_period_end' => false, 'canceled_at' => null]);
        app(EntitlementService::class)->flush($this->workspace_id);
    }

    public function expire(): void
    {
        $this->update(['status' => 'expired']);
        app(EntitlementService::class)->flush($this->workspace_id);
    }
}
