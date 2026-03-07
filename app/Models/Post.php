<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_path',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'visualizar_siempre',
        'visible_todos',
        'genero',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'visualizar_siempre' => 'boolean',
        'visible_todos' => 'boolean',
    ];

    /**
     * El autor de la publicación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Los likes recibidos por la publicación.
     */
    /**
     * Relación de muchos a muchos con los usuarios que han dado "like".
     */
    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_user_likes', 'post_id', 'user_id')
            ->withTimestamps();
    }

    // RELACIONES DE RESTRICCIONES

    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'post_sedes')->withTimestamps();
    }

    public function estadosCiviles(): BelongsToMany
    {
        return $this->belongsToMany(EstadoCivil::class, 'post_estados_civiles')->withTimestamps();
    }

    public function rangosEdad(): BelongsToMany
    {
        return $this->belongsToMany(RangoEdad::class, 'post_rangos_edad')->withTimestamps();
    }

    public function tiposUsuarios(): BelongsToMany
    {
        return $this->belongsToMany(TipoUsuario::class, 'post_tipos_usuarios')->withTimestamps();
    }

    public function procesosRequisito(): BelongsToMany
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'post_procesos_requisito', 'post_id', 'paso_crecimiento_id')
            ->withPivot('estado_paso_crecimiento_usuario_id', 'indice')
            ->withTimestamps();
    }

    public function tareasRequisito(): BelongsToMany
    {
        return $this->belongsToMany(TareaConsolidacion::class, 'post_tareas_requisito', 'post_id', 'tarea_consolidacion_id')
            ->withPivot('estado_tarea_consolidacion_id', 'indice')
            ->withTimestamps();
    }

    /**
     * Scope para filtrar publicaciones pertinentes a un usuario específico.
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
                     ->where(function($qSede) use ($user) {
                         $qSede->whereDoesntHave('sedes')
                               ->orWhereHas('sedes', fn($sq) => $sq->where('sedes.id', $user->sede_id));
                     })
                     // Filtro Estado Civil
                     ->where(function($qEstado) use ($user) {
                         $qEstado->whereDoesntHave('estadosCiviles')
                               ->orWhereHas('estadosCiviles', fn($sq) => $sq->where('estados_civiles.id', $user->estado_civil_id));
                     })
                     // Filtro Rango Edad
                     ->where(function($qRango) use ($rangoEdadId) {
                         $qRango->whereDoesntHave('rangosEdad')
                               ->orWhereHas('rangosEdad', fn($sq) => $sq->where('rangos_edad.id', $rangoEdadId));
                     })
                     // Filtro Tipo Usuario
                     ->where(function($qTipo) use ($user) {
                         $qTipo->whereDoesntHave('tiposUsuarios')
                               ->orWhereHas('tiposUsuarios', fn($sq) => $sq->where('tipo_usuarios.id', $user->tipo_usuario_id));
                     })
                     // Filtro Pasos de Crecimiento
                     ->where(function($qPaso) use ($user) {
                         $qPaso->whereDoesntHave('procesosRequisito')
                               ->orWhereHas('procesosRequisito', function($sq) use ($user) {
                                   $sq->whereExists(function($qSub) use ($user) {
                                       $qSub->select(DB::raw(1))
                                            ->from('crecimiento_usuario')
                                            ->whereColumn('crecimiento_usuario.paso_crecimiento_id', 'post_procesos_requisito.paso_crecimiento_id')
                                            ->whereColumn('crecimiento_usuario.estado_id', 'post_procesos_requisito.estado_paso_crecimiento_usuario_id')
                                            ->where('crecimiento_usuario.user_id', $user->id);
                                   });
                               });
                     })
                     // Filtro Tareas de Consolidación
                     ->where(function($qTarea) use ($user) {
                         $qTarea->whereDoesntHave('tareasRequisito')
                               ->orWhereHas('tareasRequisito', function($sq) use ($user) {
                                   $sq->whereExists(function($qSub) use ($user) {
                                       $qSub->select(DB::raw(1))
                                            ->from('tarea_consolidacion_usuario')
                                            ->whereColumn('tarea_consolidacion_usuario.tarea_consolidacion_id', 'post_tareas_requisito.tarea_consolidacion_id')
                                            ->whereColumn('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', 'post_tareas_requisito.estado_tarea_consolidacion_id')
                                            ->where('tarea_consolidacion_usuario.user_id', $user->id);
                                   });
                               });
                     });
              });
        });
    }
}
