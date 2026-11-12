<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }

    public static function getValue($key, $default = null)
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('setting_' . $key, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function setValue($key, $value)
    {
        \Illuminate\Support\Facades\Cache::forget('setting_' . $key);
        
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
