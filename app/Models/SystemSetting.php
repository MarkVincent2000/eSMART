<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemSetting extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',      // Unique identifier (e.g., 'site.logo')
        'name',     // Human-readable name (e.g., 'Site Logo')
        'value',
        'type',
        'group',
        'is_locked'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_locked' => 'boolean',
    ];

    /**
     * Get a system setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get a system setting value with asset URL for file types.
     *
     * @param string $key
     * @param mixed $default
     * @return string
     */
    public static function getAsset(string $key, $default = null)
    {
        $value = self::getValue($key, $default);
        if ($value && !str_starts_with($value, 'http')) {
            return asset($value);
        }
        return $value ?: $default;
    }
}
