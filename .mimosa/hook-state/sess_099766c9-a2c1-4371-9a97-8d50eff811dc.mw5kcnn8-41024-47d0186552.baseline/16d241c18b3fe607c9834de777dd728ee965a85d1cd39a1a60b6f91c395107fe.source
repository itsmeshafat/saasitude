<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * @param  string|null  $currency  filter prices to one currency when given
     * @return array<string, mixed>
     */
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
                ->filter(fn ($pf) => $pf->feature !== null)
                ->mapWithKeys(fn ($pf) => [
                    $pf->feature->key => $pf->feature->type === 'boolean'
                        ? filter_var($pf->value, FILTER_VALIDATE_BOOLEAN)
                        : (int) $pf->value,
                ])),
        ];
    }
}
