<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * In-memory cache for settings within a single request.
     *
     * @var array<string, mixed>|null
     */
    private static ?array $cache = null;

    /**
     * Get a setting value by key, with type casting.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (self::$cache === null) {
            self::loadCache();
        }

        if (! array_key_exists($key, self::$cache)) {
            return $default;
        }

        return self::$cache[$key];
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, mixed $value): void
    {
        $setting = self::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => self::encodeValue($value, $setting->type)]);
        }

        // Bust cache
        self::$cache = null;
    }

    /**
     * Load all settings into memory cache with proper type casting.
     */
    private static function loadCache(): void
    {
        self::$cache = [];

        $settings = self::all();

        foreach ($settings as $setting) {
            self::$cache[$setting->key] = self::castValue($setting->value, $setting->type);
        }
    }

    /**
     * Clear the in-memory cache (useful for testing).
     */
    public static function clearCache(): void
    {
        self::$cache = null;
    }

    /**
     * Cast a stored value to its proper PHP type.
     */
    private static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Encode a PHP value to a string for storage.
     */
    private static function encodeValue(mixed $value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };
    }
}
