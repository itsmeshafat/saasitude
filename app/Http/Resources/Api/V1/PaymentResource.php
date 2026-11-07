<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property Payment $resource
 * @property int $id
 * @property string|null $gateway
 * @property int|null $amount
 * @property string|null $currency
 * @property string|null $status
 * @property Carbon $created_at
 */
class PaymentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'gateway' => $this->gateway,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
