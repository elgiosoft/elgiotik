<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Scopes
     */

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeGeneral($query)
    {
        return $query->where('group', 'general');
    }

    public function scopeHotspot($query)
    {
        return $query->where('group', 'hotspot');
    }

    public function scopeBilling($query)
    {
        return $query->where('group', 'billing');
    }

    /**
     * Helper Methods
     */

    public function getValue()
    {
        return match($this->type) {
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    public function setValue($value): void
    {
        $formattedValue = match($this->type) {
            'integer' => (string) (int) $value,
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        $this->update(['value' => $formattedValue]);
    }

    /**
     * Static Helper Methods
     */

    public static function get(string $key, $default = null)
    {
        $setting = static::byKey($key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->getValue();
    }

    public static function set(string $key, $value, ?string $type = null, ?string $group = 'general'): void
    {
        $setting = static::byKey($key)->first();

        if ($setting) {
            $setting->setValue($value);
        } else {
            $type = $type ?? static::guessType($value);
            $formattedValue = static::formatValue($value, $type);

            static::create([
                'key' => $key,
                'value' => $formattedValue,
                'type' => $type,
                'group' => $group,
            ]);
        }
    }

    public static function has(string $key): bool
    {
        return static::byKey($key)->exists();
    }

    public static function forget(string $key): void
    {
        static::byKey($key)->delete();
    }

    public static function getGroup(string $group): array
    {
        return static::byGroup($group)
            ->get()
            ->pluck('value', 'key')
            ->map(function ($value, $key) {
                $setting = static::byKey($key)->first();
                return $setting ? $setting->getValue() : $value;
            })
            ->toArray();
    }

    private static function guessType($value): string
    {
        return match(true) {
            is_int($value) => 'integer',
            is_bool($value) => 'boolean',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    public static function formatValue($value, string $type): string
    {
        return match($type) {
            'integer' => (string) (int) $value,
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }
}
