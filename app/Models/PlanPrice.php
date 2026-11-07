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
    use HasCrud;

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
        'gateway_price_id' => ['type' => 'text', 'label' => 'Gateway Price ID', 'rules' => 'nullable|string'],
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
