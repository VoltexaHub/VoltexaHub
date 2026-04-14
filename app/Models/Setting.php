<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'key';
    protected $keyType = 'string';

    protected $fillable = ['key', 'value', 'encrypted'];
    protected $casts = ['encrypted' => 'boolean'];

    private const CACHE_KEY = 'settings.all';

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::allCached();
        if (! array_key_exists($key, $all)) {
            return $default;
        }

        $row = $all[$key];
        $value = $row['value'];
        if ($row['encrypted'] && $value !== null && $value !== '') {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Throwable $e) {
                return $default;
            }
        }

        return $value;
    }

    public static function put(string $key, ?string $value, bool $encrypted = false): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $encrypted && $value !== null && $value !== '' ? Crypt::encryptString($value) : $value,
                'encrypted' => $encrypted,
            ],
        );
        Cache::forget(self::CACHE_KEY);
    }

    public static function forget(string $key): void
    {
        self::where('key', $key)->delete();
        Cache::forget(self::CACHE_KEY);
    }

    private static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::query()->get(['key', 'value', 'encrypted'])
                ->keyBy('key')
                ->map(fn ($s) => ['value' => $s->value, 'encrypted' => (bool) $s->encrypted])
                ->all();
        });
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
