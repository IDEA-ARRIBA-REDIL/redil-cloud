<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    use HasFactory;

    // protected $fillable = ['name', 'value', 'category', 'description', 'is_active'];
    protected $guarded = [];

    protected function casts(): array
    {
        return ['gradient' => 'boolean', 'is_active' => 'boolean'];
    }

    public static function getColorValue($name, $default = null)
    {
        return static::where('name', $name)
            ->where('is_active', true)
            ->value('value') ?? $default;
    }
}
