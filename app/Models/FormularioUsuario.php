<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormularioUsuario extends Model
{
    use HasFactory;

    protected $table = 'formularios_usuario';

    protected $guarded = [];

    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'validar_edad' => 'boolean',
            'validar_edad_operador' => 'boolean',
            'sugerir_en_recomendaciones' => 'boolean',
            'edad_minima_operador' => 'integer',
            'edad_maxima_operador' => 'integer',
            'edad_minima' => 'integer',
            'edad_maxima' => 'integer',
        ];
    }

    public function puedeSerOperadoPor(?User $usuario): bool
    {
        // Si el formulario es exterior, no requiere operador autenticado.
        if ($this->tipo && $this->tipo->es_formulario_exterior) {
            return true;
        }

        if ($usuario === null) {
            return false;
        }

        // Si no tiene activa la validación de edad del operador, se permite.
        if (! $this->validar_edad_operador) {
            return true;
        }

        // Si se valida edad pero el usuario no tiene fecha de nacimiento registrada, no cumple.
        if (empty($usuario->fecha_nacimiento)) {
            return false;
        }

        $edad = $usuario->edad();

        if ($this->edad_minima_operador !== null && $edad < $this->edad_minima_operador) {
            return false;
        }

        if ($this->edad_maxima_operador !== null && $edad > $this->edad_maxima_operador) {
            return false;
        }

        return true;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'formulario_usuario_rol',
            'formulario_usuario_id',
            'rol_id'
        )->withTimestamps();
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(SeccionFormularioUsuario::class)->orderBy('orden', 'asc');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoFormularioUsuario::class, 'tipo_formulario_id');
    }

    public function camposExtras(): BelongsToMany
    {
        return $this->belongsToMany(
            CampoExtra::class,
            'campos_extras_formularios',
            'formulario_id',
            'campo_extra_id'
        )->withPivot('visible', 'required');
    }
}
