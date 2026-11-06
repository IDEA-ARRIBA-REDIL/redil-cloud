<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BibliaLibro extends Model
{
    protected $connection = 'central';

    protected $table = 'biblia_libros';

    protected $fillable = [
        'slug',
        'nombre',
        'abreviatura',
        'usfm',
        'testamento',
        'capitulos',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'capitulos' => 'integer',
            'orden' => 'integer',
        ];
    }

    public function versiculos(): HasMany
    {
        return $this->hasMany(BibliaVersiculo::class, 'libro_id');
    }
}
