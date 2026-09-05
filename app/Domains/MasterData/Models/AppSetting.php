<?php

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Penyimpanan pengaturan aplikasi generik (key-value), dipakai untuk
 * Pengaturan Jaringan Lokal (LAN) dan Pengaturan Printer (Sesi 12).
 *
 * Nilai selalu disimpan dalam bentuk JSON `{ "v": <nilai asli> }` supaya
 * bisa menyimpan string, angka, boolean, atau array tanpa perlu kolom
 * terpisah per tipe data.
 */
class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    public static function available(): bool
    {
        try {
            return Schema::hasTable('app_settings');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (! self::available()) {
            return $default;
        }

        $row = static::query()->find($key);

        if (! $row) {
            return $default;
        }

        return $row->value['v'] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => ['v' => $value]]
        );
    }

    /**
     * @param array<int, string> $keys
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    public static function getMany(array $keys, array $defaults = []): array
    {
        $result = [];

        if (! self::available()) {
            foreach ($keys as $key) {
                $result[$key] = $defaults[$key] ?? null;
            }

            return $result;
        }

        $rows = static::query()->whereIn('key', $keys)->get()->keyBy('key');

        foreach ($keys as $key) {
            $result[$key] = $rows->has($key)
                ? ($rows[$key]->value['v'] ?? ($defaults[$key] ?? null))
                : ($defaults[$key] ?? null);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $pairs
     */
    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::set($key, $value);
        }
    }
}
