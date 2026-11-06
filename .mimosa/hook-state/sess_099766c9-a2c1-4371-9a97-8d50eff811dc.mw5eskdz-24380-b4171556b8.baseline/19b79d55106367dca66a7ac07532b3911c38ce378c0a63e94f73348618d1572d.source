<?php

namespace App\Actions;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\PlanPrice;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

class ApplyCoupon
{
    /**
     * Validate a coupon code against a workspace and plan price.
     *
     * @param  PlanPrice|null  $price  The price being checked out; required
     *                                 for currency/plan eligibility checks.
     *
     * @throws \RuntimeException when the coupon fails any validation rule.
     */
    public function execute(Workspace $workspace, string $code, ?PlanPrice $price = null): Coupon
    {
        $coupon = Coupon::query()
            ->where('code', strtoupper(trim($code)))
            ->first();

        if (! $coupon) {
            throw new \RuntimeException('This coupon code is not valid.');
        }

        if (! $coupon->is_active) {
            throw new \RuntimeException('This coupon is no longer active.');
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw new \RuntimeException('This coupon has expired.');
        }

        if ($coupon->max_redemptions !== null && $coupon->redeemed_count >= $coupon->max_redemptions) {
            throw new \RuntimeException('This coupon has reached its redemption limit.');
        }

        if ($coupon->redemptions()->where('workspace_id', $workspace->id)->exists()) {
            throw new \RuntimeException('This coupon has already been used by this workspace.');
        }

        if ($price) {
            if ($coupon->currency && $coupon->currency !== strtoupper($price->currency)) {
                throw new \RuntimeException('This coupon does not apply to the selected currency.');
            }

            if ($coupon->plan_ids && ! in_array($price->plan_id, $coupon->plan_ids)) {
                throw new \RuntimeException('This coupon is not valid for the selected plan.');
            }
        }

        return $coupon;
    }

    /** Record a redemption and bump the counter atomically. */
    public function record(Coupon $coupon, Workspace $workspace, ?int $amountOffMinor = null, ?string $gatewayCouponId = null): CouponRedemption
    {
        return DB::transaction(function () use ($coupon, $workspace, $amountOffMinor, $gatewayCouponId) {
            $coupon->redemptions()->create([
                'workspace_id' => $workspace->id,
                'gateway_coupon_id' => $gatewayCouponId,
                'amount_off_minor' => $amountOffMinor,
                'applied_at' => now(),
            ]);

            $coupon->increment('redeemed_count');

            return $coupon->redemptions()->latest('id')->first();
        });
    }
}
