<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Hito extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hitos';

    protected $guarded = [];

    protected $casts = [
        'permite_fotos_usuario' => 'boolean',
        'requiere_sesion' => 'boolean',
        'activo' => 'boolean',
        'requiere_asistencia' => 'boolean',
        'fecha_evento' => 'date',
        'trigger_config' => 'array',
    ];

    // ============================================
    // RELACIONES
    // ============================================

    public function tipoHito(): BelongsTo
    {
        return $this->belongsTo(TipoHito::class, 'tipo_hito_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'actividad_id');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(HitoFoto::class)->orderBy('orden');
    }

    public function fotosAdmin(): HasMany
    {
        return $this->hasMany(HitoFoto::class)->where('es_admin', true)->orderBy('orden');
    }

    public function fotosUsuario(): HasMany
    {
        return $this->hasMany(HitoFoto::class)->where('es_admin', false)->orderBy('orden');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(HitoLike::class);
    }

    public function denuncias(): HasMany
    {
        return $this->hasMany(HitoDenuncia::class);
    }

    public function usuariosAsignados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'hito_usuario', 'hito_id', 'user_id')
            ->withPivot('fecha', 'asistio', 'origen_tipo', 'origen_id', 'asignado_por', 'nota_personalizada')
            ->withTimestamps();
    }

    // Pivotes de restricción / segmentación
    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'hito_sedes')->withTimestamps();
    }

    public function estadosCiviles(): BelongsToMany
    {
        return $this->belongsToMany(EstadoCivil::class, 'hito_estados_civiles')->withTimestamps();
    }

    public function rangosEdad(): BelongsToMany
    {
        return $this->belongsToMany(RangoEdad::class, 'hito_rangos_edad')->withTimestamps();
    }

    public function tiposUsuarios(): BelongsToMany
    {
        return $this->belongsToMany(TipoUsuario::class, 'hito_tipos_usuarios')->withTimestamps();
    }

    public function grupoTipos(): BelongsToMany
    {
        return $this->belongsToMany(TipoGrupo::class, 'hito_grupo_tipos')->withTimestamps();
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorSlugTipo($query, string $slug)
    {
        return $query->whereHas('tipoHito', fn ($q) => $q->where('slug', $slug));
    }

    /**
     * Scope dinámico: Hitos visibles para un usuario específico.
     * Contempla:
     * 1. Hitos asignados explícitamente en hito_usuario (Automáticos cumplidos / Reconocimientos manuales).
     * 2. Hitos automáticos de Escuelas comprobados en tiempo real (materia o nivel aprobado/homologado).
     * 3. Hitos de actividad (con o sin requerimiento de asistencia).
     * 4. Hitos generales de la congregación (sin trigger automático de módulo).
     */
    public function scopeForUser($query, User $user)
    {
        // Obtener IDs de materias y niveles aprobados u homologados por el usuario usando sus modelos
        $materiaIds = MateriaAprobadaUsuario::where('user_id', $user->id)
            ->where('aprobado', 1)
            ->pluck('materia_id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $nivelIds = NivelAprobadoUsuario::where('user_id', $user->id)
            ->where('aprobado', 1)
            ->pluck('nivel_id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return $query->where('activo', true)
            ->where(function ($qPrincipal) use ($user, $materiaIds, $nivelIds) {
                // 1. Hitos de Reconocimiento / Asignación Manual
                // (SOLO se muestran si el usuario está explícitamente en la lista de asignados)
                $qPrincipal->where(function ($qMan) use ($user) {
                    $qMan->whereHas('tipoHito', fn ($th) => $th->where('slug', 'manual'))
                        ->whereHas('usuariosAsignados', fn ($uq) => $uq->where('users.id', $user->id));
                })
                // 2. Hitos Automáticos de Escuelas (Validados contra materias/niveles aprobados del usuario)
                    ->orWhere(function ($qEsc) use ($materiaIds, $nivelIds) {
                        $qEsc->where('trigger_modulo', 'escuelas')
                            ->where(function ($qEscVal) use ($materiaIds, $nivelIds) {
                                $tieneFiltro = false;

                                if (! empty($materiaIds)) {
                                    $qEscVal->where(function ($qMat) use ($materiaIds) {
                                        foreach ($materiaIds as $mId) {
                                            $qMat->orWhere('trigger_config->materia_id', $mId);
                                        }
                                    });
                                    $tieneFiltro = true;
                                }

                                if (! empty($nivelIds)) {
                                    $condNivel = function ($qNiv) use ($nivelIds) {
                                        foreach ($nivelIds as $nId) {
                                            $qNiv->orWhere('trigger_config->nivel_id', $nId);
                                        }
                                    };
                                    if ($tieneFiltro) {
                                        $qEscVal->orWhere($condNivel);
                                    } else {
                                        $qEscVal->where($condNivel);
                                        $tieneFiltro = true;
                                    }
                                }

                                if (! $tieneFiltro) {
                                    $qEscVal->whereRaw('1 = 0');
                                }
                            });
                    })
                // 3. Hitos vinculados a Actividades
                    ->orWhere(function ($qAct) use ($user) {
                        $qAct->whereNotNull('actividad_id')
                            ->where('actividad_id', '>', 0)
                            ->where(function ($qAsist) use ($user) {
                                $qAsist->where('requiere_asistencia', false)
                                    ->orWhereHas('actividad.asistencias', fn ($asq) => $asq->where('user_id', $user->id));
                            });
                    })
                // 4. Hitos Generales de la comunidad (SOLO tipo General, sin módulo automático y sin actividad)
                    ->orWhere(function ($qGen) {
                        $qGen->whereHas('tipoHito', fn ($th) => $th->where('slug', 'general'))
                            ->where(function ($qNull) {
                                $qNull->whereNull('actividad_id')->orWhere('actividad_id', 0);
                            })
                            ->where(function ($qNullMod) {
                                $qNullMod->whereNull('trigger_modulo')->orWhere('trigger_modulo', '');
                            });
                    })
                // 5. Hitos Automáticos de Grupos, Pasos o Consolidación (Registrados con fecha en hito_usuario)
                    ->orWhere(function ($qAutoAsig) use ($user) {
                        $qAutoAsig->whereIn('trigger_modulo', ['grupos', 'pasos_crecimiento', 'tareas_consolidacion'])
                            ->whereHas('usuariosAsignados', fn ($uq) => $uq->where('users.id', $user->id));
                    });
            });
    }

    // ============================================
    // ACCESSORS Y MUTATORS
    // ============================================

    public function getPortadaUrlAttribute(): string
    {
        if ($this->portada_path && $this->portada_path !== '') {
            return tenant_asset('img/hitos/portadas/'.$this->portada_path);
        }

        return Storage::disk('global_media')->url('hitos/default-portada.jpg');
    }

    public function getMensajeParaUsuarioAttribute(): string
    {
        if ($this->mensaje_usuario && trim($this->mensaje_usuario) !== '') {
            return $this->mensaje_usuario;
        }

        $slug = $this->tipoHito->slug ?? 'general';

        return match ($slug) {
            'automatico' => '¡Felicidades por este gran logro en tu camino de fe y crecimiento!',
            'actividad' => 'Gracias por ser parte activa de este momento especial en la congregación.',
            'manual' => 'Reconocimiento especial de parte del equipo pastoral.',
            default => $this->descripcion ?? 'Este es un momento muy especial para ti.',
        };
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $this->video_url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}";
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $this->video_url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}";
        }

        return $this->video_url;
    }

    public function getTotalLikesAttribute(): int
    {
        return $this->likes()->count();
    }

    public function getTotalFotosAttribute(): int
    {
        return $this->fotos()->count();
    }

    public function esGeneral(): bool
    {
        return $this->tipoHito?->slug === 'general';
    }

    public function esAutomatico(): bool
    {
        return $this->tipoHito?->slug === 'automatico';
    }

    public function esDeActividad(): bool
    {
        return $this->tipoHito?->slug === 'actividad';
    }

    public function esManual(): bool
    {
        return $this->tipoHito?->slug === 'manual';
    }

    public function esManualIndividual(): bool
    {
        return $this->tipoHito?->slug === 'manual';
    }
}
