<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class AccionDashboard extends Model
{
    use HasFactory;

    protected $table = 'acciones_dashboard';

    protected $fillable = [
        'nombre',
        'url',
        'abrir_nueva_pestana',
        'tipo_visual',
        'icono_clase',
        'icono_color',
        'imagen',
        'visible_todos',
        'orden',
        'activo',
    ];

    protected $casts = [
        'abrir_nueva_pestana' => 'boolean',
        'visible_todos' => 'boolean',
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    protected $appends = [
        'imagen_url',
        'icono_completo',
        'url_resuelta',
    ];

    /**
     * Relación muchos-a-muchos con los Tipos de Usuario permitidos para ver esta acción.
     */
    public function tiposUsuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoUsuario::class,
            'accion_dashboard_tipo_usuario',
            'accion_dashboard_id',
            'tipo_usuario_id'
        )->withTimestamps();
    }

    /**
     * Relación muchos-a-muchos con los Roles de plataforma permitidos para ver esta acción.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'accion_dashboard_role',
            'accion_dashboard_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Obtiene la URL de la imagen aplicando el patrón Dual Storage:
     * 1. Si el archivo existe en el almacenamiento del tenant ('img/acciones-dashboard/'), usa tenant_asset().
     * 2. Si es un archivo global por defecto (ej. 'Funcionalidad-escuelas.png'), usa global_media.
     */
    public function getImagenUrlAttribute(): ?string
    {
        if (empty($this->imagen)) {
            return null;
        }

        // 1. Verificar si es una imagen propia subida por el tenant en el disco public
        $rutaTenant = 'img/acciones-dashboard/'.$this->imagen;
        if (Storage::disk('public')->exists($rutaTenant)) {
            return tenant_asset($rutaTenant);
        }

        // 2. Fallback al disco global_media para las imágenes predeterminadas del sistema
        return Storage::disk('global_media')->url($this->imagen);
    }

    /**
     * Normaliza la clase del ícono Tabler para asegurar el prefijo 'ti ti-'.
     */
    public function getIconoCompletoAttribute(): string
    {
        $clase = trim($this->icono_clase ?? '');
        if ($clase === '') {
            return 'ti ti-apps';
        }

        if (str_starts_with($clase, 'ti-') && ! str_starts_with($clase, 'ti ')) {
            return 'ti '.$clase;
        }

        if (! str_contains($clase, 'ti') && ! str_contains($clase, 'fa') && ! str_contains($clase, 'bx')) {
            return 'ti ti-'.$clase;
        }

        return $clase;
    }

    /**
     * Resuelve el enlace de destino admitiendo URLs absolutas, nombres de ruta de Laravel o rutas relativas.
     */
    public function getUrlResueltaAttribute(): string
    {
        $enlace = trim($this->url ?? '');
        if ($enlace === '' || $enlace === '#') {
            return 'javascript:void(0);';
        }

        // 1. Si es una URL absoluta (http/https), retornarla directamente
        if (str_starts_with($enlace, 'http://') || str_starts_with($enlace, 'https://')) {
            return $enlace;
        }

        // 2. Si coincide con el nombre de una ruta registrada en Laravel (ej. 'escuelas.dashboard')
        if (! str_starts_with($enlace, '/') && Route::has($enlace)) {
            try {
                return route($enlace);
            } catch (\Throwable) {
                return url($enlace);
            }
        }

        // 3. Ruta relativa interna (ej. '/grupos' o '/escuelas/dashboard')
        return url($enlace);
    }

    /**
     * Scope que filtra las acciones activas y visibles según el Tipo de Usuario y el Rol Activo del usuario.
     */
    public function scopePermitidasParaUsuario(Builder $query, User $usuario, ?Role $rolActivo = null): Builder
    {
        $tipoUsuarioId = $usuario->tipo_usuario_id;
        $rolActivoId = $rolActivo?->id;

        return $query
            ->where('activo', true)
            ->where(function (Builder $q) use ($tipoUsuarioId, $rolActivoId) {
                // 1. Si está marcado como visible para todos, se muestra sin restricción
                $q->where('visible_todos', true)
                    ->orWhere(function (Builder $qRestringido) use ($tipoUsuarioId, $rolActivoId) {
                        $qRestringido->where('visible_todos', false)
                            ->where(function (Builder $qSegmento) use ($tipoUsuarioId, $rolActivoId) {
                                // 2. Coincidencia por Tipo de Usuario asignado
                                if ($tipoUsuarioId) {
                                    $qSegmento->orWhereHas('tiposUsuarios', function (Builder $sq) use ($tipoUsuarioId) {
                                        $sq->where('tipo_usuarios.id', $tipoUsuarioId);
                                    });
                                }

                                // 3. Coincidencia por Rol Activo asignado
                                if ($rolActivoId) {
                                    $qSegmento->orWhereHas('roles', function (Builder $sq) use ($rolActivoId) {
                                        $sq->where('roles.id', $rolActivoId);
                                    });
                                }
                            });
                    });
            })
            ->orderBy('orden', 'asc')
            ->orderBy('id', 'asc');
    }
}
