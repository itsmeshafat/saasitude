<?php

namespace App\Models;

use HasinHayder\TyroDashboard\Concerns\HasCrud;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasCrud;

    protected $fillable = ['key', 'value', 'group', 'is_secret'];

    protected $casts = [
        'is_secret' => 'boolean',
    ];

    protected $resourceTitle = 'Settings';

    protected $resourceTitleSingular = 'Setting';

    protected $resourceRoles = ['admin', 'super-admin'];

    protected $resourceFieldOverrides = [
        'key' => [
            'type' => 'text',
            'label' => 'Key',
            'rules' => 'required|string|max:255',
            'readonly' => true,
            'hide_in_create' => false,
            'searchable' => true,
        ],
        'value' => [
            'type' => 'textarea',
            'label' => 'Value',
            'rules' => 'nullable|string',
            'help_text' => 'Secret values are encrypted automatically.',
        ],
        'group' => [
            'type' => 'select',
            'label' => 'Group',
            'rules' => 'required|string',
            'default' => 'general',
            'options' => ['general' => 'General', 'billing' => 'Billing', 'secret' => 'Secret', 'email' => 'Email'],
        ],
        'is_secret' => [
            'type' => 'boolean',
            'label' => 'Encrypted',
            'help_text' => 'Enable to store this value encrypted at rest.',
            'readonly' => true,
            'hide_in_create' => true,
        ],
    ];
}
