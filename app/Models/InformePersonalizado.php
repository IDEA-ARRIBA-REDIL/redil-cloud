<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InformePersonalizado extends Model
{
    protected $table = 'informes_personalizados';

    protected $fillable = [
        'nombre',
        'descripcion',
        'link',
        'activo',
        'seleccione_dia_corte',
        'clasificaciones',
        'visible_solo_administradores',
        'informe_numerico',
        'add_id_a_la_url',
        'nombre_boton',
        'tipo_informe_id',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'seleccione_dia_corte' => 'boolean',
            'clasificaciones' => 'boolean',
            'visible_solo_administradores' => 'boolean',
            'informe_numerico' => 'boolean',
            'add_id_a_la_url' => 'boolean',
        ];
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(SeccionInforme::class, 'informe_personalizado_id')->orderBy('orden', 'asc');
    }

    public function bloques(): HasMany
    {
        return $this->hasMany(BloqueInforme::class, 'informe_personalizado_id');
    }

    public function informesEnCola(): HasMany
    {
        return $this->hasMany(InformeEnCola::class, 'informe_personalizado_id')->latest();
    }

    public function tiposUsuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoUsuario::class,
            'informe_personalizado_tipo_usuario',
            'informe_personalizado_id',
            'tipo_usuario_id'
        );
    }
}
