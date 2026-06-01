<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlanLector extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'planes_lectores';

    protected $fillable = [
        'titulo',
        'slug',
        'descripcion',
        'autor_id',
        'calificacion',
        'imagen_url',
        'estado',
        'visible_todos',
        'genero',
    ];

    protected $appends = [
        'portada_url',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
            'visible_todos' => 'boolean',
            'calificacion' => 'decimal:2',
            'genero' => 'integer',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function dias(): HasMany
    {
        return $this->hasMany(PlanLectorDia::class, 'plan_lector_id');
    }

    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'plan_lector_sedes');
    }

    public function estadosCiviles(): BelongsToMany
    {
        return $this->belongsToMany(EstadoCivil::class, 'plan_lector_estados_civiles', 'plan_lector_id', 'estado_civil_id');
    }

    public function rangosEdad(): BelongsToMany
    {
        return $this->belongsToMany(RangoEdad::class, 'plan_lector_rangos_edad', 'plan_lector_id', 'rango_edad_id');
    }

    public function tiposUsuario(): BelongsToMany
    {
        return $this->belongsToMany(TipoUsuario::class, 'plan_lector_tipos_usuarios', 'plan_lector_id', 'tipo_usuario_id');
    }

    public function procesosRequisito(): BelongsToMany
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'plan_lector_procesos_requisito', 'plan_lector_id', 'paso_crecimiento_id')
            ->withPivot('estado_paso_crecimiento_usuario_id', 'indice')
            ->withTimestamps();
    }

    public function tareasRequisito(): BelongsToMany
    {
        return $this->belongsToMany(TareaConsolidacion::class, 'plan_lector_tareas_requisito', 'plan_lector_id', 'tarea_consolidacion_id')
            ->withPivot('estado_tarea_consolidacion_id', 'indice')
            ->withTimestamps();
    }

    public function scopeForUser($query, $user)
    {
        // Encontrar el rango de edad del usuario
        $rangoEdad = $user->rangoEdad();
        $rangoEdadId = $rangoEdad ? $rangoEdad->id : null;

        return $query->where(function ($q) use ($user, $rangoEdadId) {
            $q->where('visible_todos', true)
                ->orWhere(function ($q2) use ($user, $rangoEdadId) {
                    $q2->where('visible_todos', false)
                        // Filtro Género (1: Masc, 2: Fem, 3: Ambos)
                        ->whereIn('genero', [$user->genero == 0 ? 1 : 2, 3])
                        // Filtro Sede
                        ->where(function ($qSede) use ($user) {
                            $qSede->whereDoesntHave('sedes')
                                ->orWhereHas('sedes', fn($sq) => $sq->where('sedes.id', $user->sede_id));
                        })
                        // Filtro Estado Civil
                        ->where(function ($qEstado) use ($user) {
                            $qEstado->whereDoesntHave('estadosCiviles')
                                ->orWhereHas('estadosCiviles', fn($sq) => $sq->where('estados_civiles.id', $user->estado_civil_id));
                        })
                        // Filtro Rango Edad
                        ->where(function ($qRango) use ($rangoEdadId) {
                            $qRango->whereDoesntHave('rangosEdad')
                                ->orWhereHas('rangosEdad', fn($sq) => $sq->where('rangos_edad.id', $rangoEdadId));
                        })
                        // Filtro Tipo Usuario
                        ->where(function ($qTipo) use ($user) {
                            $qTipo->whereDoesntHave('tiposUsuario')
                                ->orWhereHas('tiposUsuario', fn($sq) => $sq->where('tipo_usuarios.id', $user->tipo_usuario_id));
                        })
                        // Filtro Pasos de Crecimiento
                        ->where(function ($qPaso) use ($user) {
                            $qPaso->whereDoesntHave('procesosRequisito')
                                ->orWhereHas('procesosRequisito', function ($sq) use ($user) {
                                    $sq->whereExists(function ($qSub) use ($user) {
                                        $qSub->select(\DB::raw(1))
                                            ->from('crecimiento_usuario')
                                            ->whereColumn('crecimiento_usuario.paso_crecimiento_id', 'plan_lector_procesos_requisito.paso_crecimiento_id')
                                            ->whereColumn('crecimiento_usuario.estado_id', 'plan_lector_procesos_requisito.estado_paso_crecimiento_usuario_id')
                                            ->where('crecimiento_usuario.user_id', $user->id);
                                    });
                                });
                        })
                        // Filtro Tareas de Consolidación
                        ->where(function ($qTarea) use ($user) {
                            $qTarea->whereDoesntHave('tareasRequisito')
                                ->orWhereHas('tareasRequisito', function ($sq) use ($user) {
                                    $sq->whereExists(function ($qSub) use ($user) {
                                        $qSub->select(\DB::raw(1))
                                            ->from('tarea_consolidacion_usuario')
                                            ->whereColumn('tarea_consolidacion_usuario.tarea_consolidacion_id', 'plan_lector_tareas_requisito.tarea_consolidacion_id')
                                            ->whereColumn('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', 'plan_lector_tareas_requisito.estado_tarea_consolidacion_id')
                                            ->where('tarea_consolidacion_usuario.user_id', $user->id);
                                    });
                                });
                        });
                });
        });
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(PlanLectorCategoria::class, 'categoria_plan_lector', 'plan_lector_id', 'plan_lector_categoria_id');
    }

    /**
     * Usuarios inscritos en este plan.
     */
    public function usuariosInscritos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'plan_lector_users', 'plan_lector_id', 'user_id')
            ->withPivot('estado', 'fecha_inscripcion', 'porcentaje_progreso', 'calificacion_usuario')
            ->withTimestamps();
    }

    public function getPortadaUrlAttribute(): ?string
    {
        if (!$this->imagen_url) {
            return null;
        }      
        
        $filename = basename($this->imagen_url);

        return tenant_asset('img/plan-lector/' . $filename);
    }

    /**
     * Recalcula el promedio de calificación del plan basado en las notas de los usuarios.
     */
    public function recalcularPromedio(): void
    {
        $promedio = DB::table('plan_lector_users')
            ->where('plan_lector_id', $this->id)
            ->whereNotNull('calificacion_usuario')
            ->avg('calificacion_usuario');
            
        $this->update(['calificacion' => $promedio ?? 0]);
    }
}
