<?php

namespace App\Models;

use HasinHayder\TyroDashboard\Concerns\HasCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $plan_id
 * @property string|null $gateway
 * @property string|null $interval
 * @property string|null $currency
 * @property int|null $amount_minor
 * @property string|null $gateway_price_id
 * @property-read Plan $plan
 */
class PlanPrice extends Model
{
    use HasCrud { getResourceConfig as baseGetResourceConfig; }

    /**
     * Append a sidebar icon to the HasCrud resource config so the Data
     * section shows a distinct menu icon per resource.
     *
     * @return array<string, mixed>
     */
    public static function getResourceConfig(): array
    {
        return array_merge(static::baseGetResourceConfig(), [
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2H2v10l9.3 9.3a2 2 0 002.8 0l7.2-7.2a2 2 0 000-2.8L12 2z"/><circle cx="7" cy="7" r="1"/></svg>',
        ]);
    }

    protected $fillable = ['plan_id', 'gateway', 'interval', 'currency', 'amount_minor', 'gateway_price_id'];

    protected $casts = [
        'amount_minor' => 'integer',
    ];

    protected $resourceTitle = 'Plan Prices';

    protected $resourceTitleSingular = 'Plan Price';

    protected $resourceRoles = ['admin', 'super-admin'];

    protected $resourceFieldOverrides = [
        'plan_id' => [
            'type' => 'select',
            'label' => 'Plan',
            'rules' => 'required|exists:plans,id',
            'relationship' => 'plan',
            'option_label' => 'name',
        ],
        'gateway' => [
            'type' => 'select',
            'label' => 'Gateway',
            'rules' => 'required|in:stripe,paddle,sslcommerz',
            'options' => ['stripe' => 'Stripe', 'paddle' => 'Paddle', 'sslcommerz' => 'SSLCommerz'],
        ],
        'interval' => [
            'type' => 'select',
            'label' => 'Interval',
            'rules' => 'required|in:monthly,annual,one_time',
            'options' => ['monthly' => 'Monthly', 'annual' => 'Annual', 'one_time' => 'One-time'],
        ],
        'currency' => ['type' => 'text', 'label' => 'Currency (ISO)', 'rules' => 'required|size:3'],
        'amount_minor' => ['type' => 'text', 'label' => 'Amount (minor units)', 'rules' => 'required|integer|min:0', 'help_text' => 'e.g. 999 = $9.99 or ৳999'],
        'gateway_price_id' => [
            'type' => 'text',
            'label' => 'Gateway Price ID',
            'rules' => 'nullable|string',
            // Not a local BelongsTo: HasCrud guesses "gatewayPrice" from
            // the *_id suffix and the index eager-load then 500s. Null it.
            'relationship' => null,
        ],
    ];

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    protected static function booted(): void
    {
        $bumpVersion = function (PlanPrice $price): void {
            Plan::where('id', $price->plan_id)
                ->increment('price_version');
        };

        static::saved($bumpVersion);
        static::deleted($bumpVersion);
    }
}
