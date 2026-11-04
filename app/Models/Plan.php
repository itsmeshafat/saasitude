<?php

namespace App\Models;

use HasinHayder\Tyro\Support\TyroAudit;
use HasinHayder\TyroDashboard\Concerns\HasCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Plan extends Model
{
    use HasCrud;

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

    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class);
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot('value')
            ->withTimestamps();
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
