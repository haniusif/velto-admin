<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'label',
        'value',
        'type',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function group(string $group): array
    {
        return static::query()
            ->where('group', $group)
            ->pluck('value', 'key')
            ->all();
    }
}
