<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\PlanPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property Plan $resource
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $description
 * @property-read Collection<int, PlanPrice> $prices
 * @property-read Collection<int, PlanFeature> $planFeatures
 */
class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currency = $request->query('currency');
        $prices = $this->whenLoaded('prices', function () use ($currency) {
            $filtered = $currency
                ? $this->prices->where('currency', strtoupper($currency))->values()
                : $this->prices;

            return $filtered->map(fn ($price) => [
                'id' => $price->id,
                'gateway' => $price->gateway,
                'interval' => $price->interval,
                'currency' => $price->currency,
                'amount' => $price->amount_minor,
            ]);
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'prices' => $prices,
            'features' => $this->whenLoaded('planFeatures', fn () => $this->planFeatures
                ->mapWithKeys(fn (PlanFeature $pf) => [
                    $pf->feature->key => $pf->feature->type === 'boolean'
                        ? filter_var($pf->value, FILTER_VALIDATE_BOOLEAN)
                        : (int) $pf->value,
                ])),
        ];
    }
}
