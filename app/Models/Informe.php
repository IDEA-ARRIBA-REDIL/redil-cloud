<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Informe extends Model
{
    use HasFactory;

    protected $table = 'informes';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'usa_plantilla' => 'boolean',
            'seleccione_dia_corte' => 'boolean',
            'clasificaciones' => 'boolean',
            'visible_solo_administradores' => 'boolean',
            'informe_numerico' => 'boolean',
            'add_id_a_la_url' => 'boolean',
        ];
    }

    public function tipoInforme(): BelongsTo
    {
        return $this->belongsTo(TipoInforme::class, 'tipo_informe_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'informe_rol',
            'informe_id',
            'rol_id'
        );
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(SeccionInforme::class, 'informe_id')
            ->orderBy('orden', 'asc');
    }

    public function bloques(): HasMany
    {
        return $this->hasMany(BloqueInforme::class, 'informe_id');
    }

    public function informesEnCola(): HasMany
    {
        return $this->hasMany(InformeEnCola::class, 'informe_id')->latest();
    }
}
