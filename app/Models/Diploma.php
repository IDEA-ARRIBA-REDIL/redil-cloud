<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diploma extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'logo1',
        'logo2',
        'encabezado1',
        'encabezado2',
        'encabezado3',
        'titulo',
        'descripcion1',
        'descripcion2',
        'firma1',
        'nombre_firma_1',
        'cargo_firma_1',
        'firma2',
        'nombre_firma_2',
        'cargo_firma_2',
        'fondo',
    ];

    /**
     * Un diploma tiene muchas escuelas.
     */
    public function escuelas(): HasMany
    {
        return $this->hasMany(Escuela::class);
    }
}