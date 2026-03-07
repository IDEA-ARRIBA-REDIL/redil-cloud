<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'value', 'category', 'description', 'is_active'];

    public static function getColorValue($name, $default = null)
    {
        return static::where('name', $name)
            ->where('is_active', true)
            ->value('value') ?? $default;
    }
}
