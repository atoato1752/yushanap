<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $fillable = [
        'key_name',
        'value',
        'description'
    ];

    public static function getValue($key, $default = null)
    {
        $config = static::where('key_name', $key)->first();
        return $config ? $config->value : $default;
    }

    public static function setValue($key, $value, $description = null)
    {
        return static::updateOrCreate(
            ['key_name' => $key],
            [
                'value' => $value,
                'description' => $description
            ]
        );
    }
} 