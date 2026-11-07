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
