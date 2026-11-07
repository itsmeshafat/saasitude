<?php

namespace App\Models;

use HasinHayder\TyroDashboard\Concerns\HasCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string|null $key
 * @property string|null $name
 * @property string|null $type
 * @property string|null $unit
 */
class Feature extends Model
{
    use HasCrud { getResourceConfig as baseGetResourceConfig; }

    /**
     * Append a sidebar icon to the HasCrud resource config so the Data
     * section shows a distinct menu icon per resource.
     *
     * @return array<string, mixed>
     */
    public static function getResourceConfig(): array
    {
        return array_merge(static::baseGetResourceConfig(), [
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6h11"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 12h11"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 18h11"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l1.5 1.5L7 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l1.5 1.5L7 11"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 18l1.5 1.5L7 17"/></svg>',
        ]);
    }

    protected $fillable = ['key', 'name', 'type', 'unit'];

    protected $resourceTitle = 'Features';

    protected $resourceTitleSingular = 'Feature';

    protected $resourceRoles = ['admin', 'super-admin'];

    protected $resourceFieldOverrides = [
        'key' => ['type' => 'text', 'label' => 'Key', 'rules' => 'required|string|max:100', 'searchable' => true, 'help_text' => 'e.g. max_seats, api_access'],
        'name' => ['type' => 'text', 'label' => 'Display Name', 'rules' => 'required|string|max:255'],
        'type' => [
            'type' => 'select',
            'label' => 'Type',
            'rules' => 'required|in:boolean,limit,metered',
            'options' => ['boolean' => 'Boolean (on/off)', 'limit' => 'Limit (integer cap)', 'metered' => 'Metered (usage tracked)'],
        ],
        'unit' => ['type' => 'text', 'label' => 'Unit', 'rules' => 'nullable|string|max:50', 'help_text' => 'e.g. seats, calls/month'],
    ];

    /**
     * @return HasMany<PlanFeature, $this>
     */
    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    /**
     * @return BelongsToMany<Plan, $this>
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->withPivot('value')
            ->withTimestamps();
    }
}
