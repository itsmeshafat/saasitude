<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $workspace_id
 * @property string|null $gateway
 * @property string|null $gateway_customer_id
 * @property array|null $metadata
 * @property-read Workspace $workspace
 */
class GatewayCustomer extends Model
{
    protected $fillable = ['workspace_id', 'gateway', 'gateway_customer_id', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
