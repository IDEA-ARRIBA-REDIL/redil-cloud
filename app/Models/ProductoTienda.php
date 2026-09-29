<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoTienda extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos_tienda';

    protected $guarded = [];

    protected $casts = [
        'costo_puntos' => 'integer',
        'stock' => 'integer',
        'limite_por_usuario' => 'integer',
        'orden' => 'integer',
        'visible_todos' => 'boolean',
        'genero' => 'integer',
    ];

    public function solicitudesCanje(): HasMany
    {
        return $this->hasMany(SolicitudCanje::class, 'producto_id');
    }

    // =========================================================================
    // RELACIONES DE RESTRICCIONES / SEGMENTACIÓN
    // =========================================================================

    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'producto_tienda_sedes', 'producto_id', 'sede_id')
            ->withTimestamps();
    }

    public function estadosCiviles(): BelongsToMany
    {
        return $this->belongsToMany(EstadoCivil::class, 'producto_tienda_estados_civiles', 'producto_id', 'estado_civil_id')
            ->withTimestamps();
    }

    public function rangosEdad(): BelongsToMany
    {
        return $this->belongsToMany(RangoEdad::class, 'producto_tienda_rangos_edad', 'producto_id', 'rango_edad_id')
            ->withTimestamps();
    }

    public function tiposUsuarios(): BelongsToMany
    {
        return $this->belongsToMany(TipoUsuario::class, 'producto_tienda_tipos_usuarios', 'producto_id', 'tipo_usuario_id')
            ->withTimestamps();
    }

    public function procesosRequisito(): BelongsToMany
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'producto_tienda_procesos_requisito', 'producto_id', 'paso_crecimiento_id')
            ->withPivot('estado_paso_crecimiento_usuario_id', 'indice')
            ->withTimestamps();
    }

    public function tareasRequisito(): BelongsToMany
    {
        return $this->belongsToMany(TareaConsolidacion::class, 'producto_tienda_tareas_requisito', 'producto_id', 'tarea_consolidacion_id')
            ->withPivot('estado_tarea_consolidacion_id', 'indice')
            ->withTimestamps();
    }

    // =========================================================================
    // SCOPES Y ACCESSORS
    // =========================================================================

    /**
     * Scope para filtrar productos visibles para un usuario según segmentación y restricciones.
     */
    public function scopeForUser($query, User $user)
    {
        $rangoEdadId = $user->rangoEdad() ? $user->rangoEdad()->id : null;

        return $query->where(function ($q) use ($user, $rangoEdadId) {
            $q->where('visible_todos', true)
                ->orWhere(function ($q2) use ($user, $rangoEdadId) {
                    $q2->where('visible_todos', false)
                        // Filtro Género (1: Masc, 2: Fem, 3: Ambos)
                        ->whereIn('genero', [$user->genero == 0 ? 1 : 2, 3])
                        // Filtro Sede
                        ->where(function ($qSede) use ($user) {
                            $qSede->whereDoesntHave('sedes')
                                ->orWhereHas('sedes', fn ($sq) => $sq->where('sedes.id', $user->sede_id));
                        })
                        // Filtro Estado Civil
                        ->where(function ($qEstado) use ($user) {
                            $qEstado->whereDoesntHave('estadosCiviles')
                                ->orWhereHas('estadosCiviles', fn ($sq) => $sq->where('estados_civiles.id', $user->estado_civil_id));
                        })
                        // Filtro Rango Edad
                        ->where(function ($qRango) use ($rangoEdadId) {
                            $qRango->whereDoesntHave('rangosEdad')
                                ->orWhereHas('rangosEdad', fn ($sq) => $sq->where('rangos_edad.id', $rangoEdadId));
                        })
                        // Filtro Tipo Usuario
                        ->where(function ($qTipo) use ($user) {
                            $qTipo->whereDoesntHave('tiposUsuarios')
                                ->orWhereHas('tiposUsuarios', fn ($sq) => $sq->where('tipo_usuarios.id', $user->tipo_usuario_id));
                        })
                        // Filtro Pasos de Crecimiento
                        ->where(function ($qPaso) use ($user) {
                            $qPaso->whereDoesntHave('procesosRequisito')
                                ->orWhereHas('procesosRequisito', function ($sq) use ($user) {
                                    $sq->whereExists(function ($qSub) use ($user) {
                                        $qSub->select(DB::raw(1))
                                            ->from('crecimiento_usuario')
                                            ->whereColumn('crecimiento_usuario.paso_crecimiento_id', 'producto_tienda_procesos_requisito.paso_crecimiento_id')
                                            ->whereColumn('crecimiento_usuario.estado_id', 'producto_tienda_procesos_requisito.estado_paso_crecimiento_usuario_id')
                                            ->where('crecimiento_usuario.user_id', $user->id);
                                    });
                                });
                        })
                        // Filtro Tareas de Consolidación
                        ->where(function ($qTarea) use ($user) {
                            $qTarea->whereDoesntHave('tareasRequisito')
                                ->orWhereHas('tareasRequisito', function ($sq) use ($user) {
                                    $sq->whereExists(function ($qSub) use ($user) {
                                        $qSub->select(DB::raw(1))
                                            ->from('tarea_consolidacion_usuario')
                                            ->whereColumn('tarea_consolidacion_usuario.tarea_consolidacion_id', 'producto_tienda_tareas_requisito.tarea_consolidacion_id')
                                            ->whereColumn('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', 'producto_tienda_tareas_requisito.estado_tarea_consolidacion_id')
                                            ->where('tarea_consolidacion_usuario.user_id', $user->id);
                                    });
                                });
                        });
                });
        });
    }

    public function getImagenUrlAttribute(): ?string
    {
        if ($this->imagen_ruta && $this->imagen_ruta !== '') {
            return tenant_asset('img/tienda/'.$this->imagen_ruta);
        }

        return Storage::disk('global_media')->url('tienda/default.png');
    }
}
