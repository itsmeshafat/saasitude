<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $coupon_id
 * @property int $workspace_id
 * @property string|null $gateway_coupon_id
 * @property int|null $amount_off_minor
 * @property Carbon|null $applied_at
 * @property-read Coupon $coupon
 * @property-read Workspace $workspace
 */
class CouponRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id', 'workspace_id', 'gateway_coupon_id', 'amount_off_minor', 'applied_at',
    ];

    protected $casts = [
        'amount_off_minor' => 'integer',
        'applied_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
