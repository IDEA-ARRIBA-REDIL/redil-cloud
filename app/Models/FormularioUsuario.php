<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
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

    public function sedesExcluidas(): BelongsToMany
    {
        return $this->belongsToMany(
            Sede::class,
            'formulario_usuario_sede_excluida',
            'formulario_usuario_id',
            'sede_id'
        )->withTimestamps();
    }

    /**
     * Obtiene los IDs de las sedes excluidas para este formulario,
     * preservando opcionalmente una sede actual ya asignada al usuario.
     *
     * @return array<int, int>
     */
    public function obtenerSedesExcluidasIds(?int $sedeActualId = null): array
    {
        $excluidasIds = $this->sedesExcluidas()
            ->pluck('sedes.id')
            ->map(fn ($id): int => (int) $id)
            ->toArray();

        if ($sedeActualId !== null) {
            $excluidasIds = array_filter(
                $excluidasIds,
                fn (int $id): bool => $id !== $sedeActualId
            );
        }

        return array_values($excluidasIds);
    }

    /**
     * Retorna las sedes disponibles para mostrar en el selector del formulario,
     * omitiendo las configuradas como excluidas (salvo la sede actual del usuario si se indica).
     */
    public function sedesDisponibles(?int $sedeActualId = null): Collection
    {
        $excluidasIds = $this->obtenerSedesExcluidasIds($sedeActualId);

        return Sede::with(['paises:id', 'municipios:id', 'localidades:id'])
            ->when(! empty($excluidasIds), function ($query) use ($excluidasIds): void {
                $query->whereNotIn('id', $excluidasIds);
            })
            ->orderBy('nombre', 'asc')
            ->get();
    }
}
