<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'is_secret',
    ];

    protected $casts = [
        'is_secret' => 'boolean',
    ];

    public static function groupValues(string $group, array $defaults = []): array
    {
        $values = $defaults;

        static::query()
            ->where('group', $group)
            ->get()
            ->each(function (AppSetting $setting) use (&$values): void {
                $values[$setting->key] = $setting->decodedValue();
            });

        return $values;
    }

    public static function value(string $group, string $key, mixed $default = null): mixed
    {
        $setting = static::query()
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        return $setting ? $setting->decodedValue() : $default;
    }

    public static function bool(string $group, string $key, bool $default = false): bool
    {
        $value = static::value($group, $key, $default ? '1' : '0');

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function hasStoredValue(string $group, string $key): bool
    {
        return static::query()
            ->where('group', $group)
            ->where('key', $key)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->exists();
    }

    public static function setValue(string $group, string $key, mixed $value, bool $secret = false): void
    {
        static::query()->updateOrCreate([
            'group' => $group,
            'key' => $key,
        ], [
            'value' => $value === null ? null : ($secret ? Crypt::encryptString((string) $value) : (string) $value),
            'is_secret' => $secret,
        ]);
    }

    public static function setMany(string $group, array $values, array $secretKeys = []): void
    {
        foreach ($values as $key => $value) {
            static::setValue($group, $key, $value, in_array($key, $secretKeys, true));
        }
    }

    public function decodedValue(): ?string
    {
        if ($this->value === null || $this->value === '') {
            return $this->value;
        }

        if (! $this->is_secret) {
            return $this->value;
        }

        try {
            return Crypt::decryptString($this->value);
        } catch (\Throwable) {
            return null;
        }
    }
}
