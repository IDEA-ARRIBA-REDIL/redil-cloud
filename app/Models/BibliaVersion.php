<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BibliaVersion extends Model
{
    protected $connection = 'central';

    protected $table = 'biblia_versiones';

    protected $fillable = [
        'slug',
        'nombre',
        'abreviatura',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function versiculos(): HasMany
    {
        return $this->hasMany(BibliaVersiculo::class, 'version_id');
    }
}
