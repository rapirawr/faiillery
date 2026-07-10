<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key, with 5-minute cache.
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("cms.{$key}", 300, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value and bust the cache.
     */
    public static function set($key, $value)
    {
        Cache::forget("cms.{$key}");
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Check if a boolean setting is enabled (value === '1').
     */
    public static function enabled($key, $default = true): bool
    {
        return static::get($key, $default ? '1' : '0') === '1';
    }
}
