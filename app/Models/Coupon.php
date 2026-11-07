<?php

namespace App\Models;

use Database\Factories\CouponFactory;
use HasinHayder\Tyro\Support\TyroAudit;
use HasinHayder\TyroDashboard\Concerns\HasCrud;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string|null $code
 * @property string|null $type
 * @property int|null $value
 * @property string|null $currency
 * @property array|null $plan_ids
 * @property int|null $max_redemptions
 * @property int|null $redeemed_count
 * @property Carbon|null $expires_at
 * @property bool $is_active
 * @property-read Collection<int, CouponRedemption> $redemptions
 */
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasCrud, HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'currency', 'plan_ids',
        'max_redemptions', 'redeemed_count', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'integer',
        'plan_ids' => 'array',
        'max_redemptions' => 'integer',
        'redeemed_count' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $resourceTitle = 'Coupons';

    protected $resourceTitleSingular = 'Coupon';

    protected $resourceRoles = ['admin', 'super-admin'];

    protected $resourceFieldOverrides = [
        'code' => ['type' => 'text', 'label' => 'Code', 'rules' => 'required|string|max:50|unique:coupons,code', 'searchable' => true],
        'type' => ['type' => 'select', 'label' => 'Type', 'rules' => 'required|in:percent,fixed'],
        'value' => ['type' => 'number', 'label' => 'Value', 'rules' => 'required|integer|min:0', 'help_text' => 'Percent (0-100) or fixed amount in minor units (e.g. cents).'],
        'currency' => ['type' => 'text', 'label' => 'Currency', 'rules' => 'nullable|string|size:3', 'help_text' => 'ISO code. Required for fixed-amount coupons.'],
        'max_redemptions' => ['type' => 'number', 'label' => 'Max Redemptions', 'rules' => 'nullable|integer|min:1', 'help_text' => 'Leave empty for unlimited.'],
        'redeemed_count' => ['type' => 'number', 'label' => 'Redeemed', 'readonly' => true, 'hide_in_create' => true],
        'expires_at' => ['type' => 'datetime-local', 'label' => 'Expires At', 'rules' => 'nullable|date'],
        'is_active' => ['type' => 'boolean', 'label' => 'Active'],
    ];

    /**
     * @return HasMany<CouponRedemption, $this>
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    /** Discount for an amount, in integer minor units. Never floats. */
    public function discountFor(int $amountMinor, string $currency): int
    {
        if ($this->type === 'percent') {
            return intdiv($amountMinor * $this->value, 100);
        }

        return $this->currency === strtoupper($currency)
            ? min($this->value, $amountMinor)
            : 0;
    }

    protected static function booted(): void
    {
        static::saving(function (Coupon $coupon): void {
            if ($coupon->type === 'fixed' && blank($coupon->currency)) {
                throw new \RuntimeException('Fixed-amount coupons require a currency.');
            }
        });

        static::updating(function (Coupon $coupon): void {
            TyroAudit::log('coupon.updated', $coupon, $coupon->getOriginal(), $coupon->getDirty());
        });

        static::deleting(function (Coupon $coupon): void {
            if ($coupon->redemptions()->exists()) {
                throw new \RuntimeException("Cannot delete coupon \"{$coupon->code}\" — it has redemptions. Deactivate it instead.");
            }

            TyroAudit::log('coupon.deleted', $coupon, $coupon->toArray(), null);
        });
    }
}
