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
    use HasCrud;

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
