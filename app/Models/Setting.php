<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Key/value application settings. Managed exclusively through
 * SettingsService (typed access, automatic encryption for secrets) and
 * the System Settings hub — deliberately NOT a HasCrud resource, so raw
 * table editing (and a second confusing "Settings" menu entry) is gone.
 */
/**
 * @property int $id
 * @property string|null $key
 * @property string|null $value
 * @property string|null $group
 * @property bool $is_secret
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'is_secret'];

    protected $casts = [
        'is_secret' => 'boolean',
    ];
}
