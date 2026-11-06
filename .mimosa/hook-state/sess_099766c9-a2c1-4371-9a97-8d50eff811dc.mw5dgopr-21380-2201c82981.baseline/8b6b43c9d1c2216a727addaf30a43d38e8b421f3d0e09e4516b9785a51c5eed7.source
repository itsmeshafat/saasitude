<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatewayCustomer extends Model
{
    protected $fillable = ['workspace_id', 'gateway', 'gateway_customer_id', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
