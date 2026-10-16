<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TareaConsolidacion extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'tareas_consolidacion';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'default', // Añadido el nuevo campo
        'todos_los_roles',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * Esto asegura que 'default' siempre sea un booleano (true/false).
     *
     * @var array
     */
    protected $casts = [
        'default' => 'boolean',
        'todos_los_roles' => 'boolean',
    ];

    public function usuarios(): BelongsToMany
    {

        /*$usuario->load(
          'asignaciones.tareaConsolidacion',
          'asignaciones.estado',
          'asignaciones.historial'
        );*/

        return $this->belongsToMany(User::class, 'tarea_consolidacion_usuario', 'tarea_consolidacion_id', 'user_id')
            ->using(TareaConsolidacionUsuario::class)
            ->withPivot('id', 'estado_tarea_consolidacion_id', 'fecha')
            ->withTimestamps();
    }

    public function tipoConsejerias(): BelongsToMany
    {
        return $this->belongsToMany(TipoConsejeria::class, 'tarea_consolidacion_tipo_consejeria', 'tarea_consolidacion_id', 'tipo_consejeria_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'rol_tarea_consolidacion', 'tarea_consolidacion_id', 'rol_id')
            ->withTimestamps();
    }

    /**
     * Scope para filtrar tareas visibles/gestionables por un rol específico.
     */
    public function scopePermitidasParaRol($query, Role|int|null $rol)
    {
        $rolId = $rol instanceof Role ? $rol->id : $rol;
        if (! $rolId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($rolId) {
            $q->where('todos_los_roles', true)
                ->orWhereHas('roles', function ($subQuery) use ($rolId) {
                    $subQuery->where('roles.id', $rolId);
                });
        });
    }

    /**
     * Determina si la tarea puede ser gestionada por un rol determinado.
     */
    public function puedeSerGestionadaPorRol(Role|int|null $rol): bool
    {
        $rolId = $rol instanceof Role ? $rol->id : $rol;
        if (! $rolId) {
            return false;
        }

        if ($this->todos_los_roles) {
            return true;
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('id', $rolId);
        }

        return $this->roles()->where('roles.id', $rolId)->exists();
    }

    // Relaciones con Cursos (LMS)

    public function cursosRequisito(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_tarea_requisito', 'tarea_consolidacion_id', 'curso_id')
            ->withPivot('estado_tarea_consolidacion_id', 'indice');
    }

    public function cursosCulminar(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_tarea_culminar', 'tarea_consolidacion_id', 'curso_id')
            ->withPivot('estado_tarea_consolidacion_id', 'indice');
    }
}
