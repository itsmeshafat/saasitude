<?php

namespace App\Models;

use HasinHayder\Tyro\Support\TyroAudit;
use HasinHayder\TyroDashboard\Concerns\HasCrud;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $description
 * @property bool $is_public
 * @property bool $is_archived
 * @property int|null $price_version
 * @property-read Collection<int, PlanPrice> $prices
 * @property-read Collection<int, Subscription> $subscriptions
 */
class Plan extends Model
{
    use HasCrud { getResourceConfig as baseGetResourceConfig; }
    use HasFactory;

    /**
     * Append a sidebar icon to the HasCrud resource config so the Data
     * section shows a distinct menu icon per resource.
     *
     * @return array<string, mixed>
     */
    public static function getResourceConfig(): array
    {
        return array_merge(static::baseGetResourceConfig(), [
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 17l10 5 10-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 12l10 5 10-5"/></svg>',
        ]);
    }

    protected $fillable = ['name', 'slug', 'description', 'is_public', 'is_archived', 'price_version'];

    protected $casts = [
        'is_public' => 'boolean',
        'is_archived' => 'boolean',
        'price_version' => 'integer',
    ];

    protected $resourceTitle = 'Plans';

    protected $resourceTitleSingular = 'Plan';

    protected $resourceRoles = ['admin', 'super-admin'];

    protected $resourceFieldOverrides = [
        'name' => ['type' => 'text', 'label' => 'Name', 'rules' => 'required|string|max:255', 'searchable' => true],
        'slug' => ['type' => 'text', 'label' => 'Slug', 'rules' => 'required|string|max:100'],
        'description' => ['type' => 'textarea', 'label' => 'Description', 'rules' => 'nullable|string'],
        'is_public' => ['type' => 'boolean', 'label' => 'Public', 'help_text' => 'Visible on pricing page.'],
        'is_archived' => ['type' => 'boolean', 'label' => 'Archived', 'help_text' => 'Hidden from new signups.'],
        'price_version' => ['type' => 'text', 'label' => 'Price Version', 'readonly' => true, 'hide_in_create' => true],
    ];

    /**
     * @return HasMany<PlanPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class);
    }

    /**
     * @return HasMany<PlanFeature, $this>
     */
    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    /**
     * @return BelongsToMany<Feature, $this>
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot('value')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    protected static function booted(): void
    {
        static::updating(function (Plan $plan): void {
            TyroAudit::log('plan.updated', $plan, $plan->getOriginal(), $plan->getDirty());
        });

        static::deleting(function (Plan $plan): void {
            if (Schema::hasTable('subscriptions')
                && $plan->newQuery()->whereHas('subscriptions', fn ($q) => $q->whereIn('status', ['active', 'trialing']))->where('id', $plan->id)->exists()
            ) {
                throw new \RuntimeException("Cannot delete plan \"{$plan->name}\" — it has active subscribers.");
            }

            TyroAudit::log('plan.deleted', $plan, $plan->toArray(), null);
        });
    }
}
