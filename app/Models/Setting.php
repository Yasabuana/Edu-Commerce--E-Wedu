<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Konfigurasi key-value (Rancangan §1.2) — hindari hardcode tarif ongkir,
 * identitas situs, dan data QRIS. Nilai di-cache 1 jam (Rancangan §3.1).
 */
class Setting extends Model
{
    public const TYPE_STRING = 'string';

    public const TYPE_INTEGER = 'integer';

    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_JSON = 'json';

    /**
     * Key cache untuk seluruh pengaturan.
     */
    public const CACHE_KEY = 'ewedu.settings';

    public const CACHE_TTL = 3600; // detik (1 jam)

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => 'string',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /* ---------------------------------------------------------------------
     |  API statis — dipakai ShippingCalculator, controller, dan Blade
     --------------------------------------------------------------------- */

    /**
     * Seluruh setting dalam bentuk koleksi ter-key (dari cache 1 jam).
     *
     * @return Collection<string, self>
     */
    public static function cached(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn () => static::query()->get()->keyBy('key'),
        );
    }

    /**
     * Ambil nilai setting bertipe asli (integer/boolean/json otomatis di-cast).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::cached()->get($key);

        if (! $setting instanceof self) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type) ?? $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::get($key, $default);
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        return (float) static::get($key, $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        return filter_var(static::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Simpan/ubah satu setting (idempoten) lalu bersihkan cache.
     */
    public static function set(string $key, mixed $value, string $type = self::TYPE_STRING, ?string $group = null): self
    {
        $setting = static::query()->firstOrNew(['key' => $key]);

        $setting->value = static::serializeValue($value, $type);
        $setting->type = $type;

        if ($group !== null) {
            $setting->group = $group;
        }

        $setting->save(); // booted() otomatis flush cache

        return $setting;
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /* ---------------------------------------------------------------------
     |  Casting nilai
     --------------------------------------------------------------------- */

    protected static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            self::TYPE_INTEGER => (int) $value,
            self::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_JSON => json_decode($value, true),
            default => $value,
        };
    }

    protected static function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            self::TYPE_JSON => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            self::TYPE_BOOLEAN => $value ? '1' : '0',
            default => (string) $value,
        };
    }
}
