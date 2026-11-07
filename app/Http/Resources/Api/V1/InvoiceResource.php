<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property Invoice $resource
 * @property int $id
 * @property string|null $number
 * @property int|null $amount
 * @property string|null $currency
 * @property string|null $status
 * @property Carbon|null $issued_at
 */
class InvoiceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'issued_at' => $this->issued_at?->toIso8601String(),
        ];
    }
}
