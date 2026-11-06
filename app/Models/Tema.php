<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Tema extends Model
{
    use HasFactory;

    protected $table = 'temas';

    protected $guarded = [];

    protected $appends = ['portada_url'];

    public function getPortadaUrlAttribute(): string
    {
        if ($this->portada && $this->portada !== '' && $this->portada !== 'default.png') {
            return tenant_asset('img/temas/'.$this->portada);
        }

        return Storage::disk('global_media')->url('temas/default.png');
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(CategoriaTema::class, 'temas_categorias', 'tema_id', 'categoria_tema_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'sedes_temas', 'tema_id', 'sede_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    public function tiposUsuarios(): BelongsToMany
    {
        return $this->belongsToMany(TipoUsuario::class, 'tipos_usuarios_temas', 'tema_id', 'tipo_usuario_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    public function tiposGrupos(): BelongsToMany
    {
        return $this->belongsToMany(TipoGrupo::class, 'tipos_grupos_temas', 'tema_id', 'tipo_grupo_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    public function temasGrupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupos_temas', 'tema_id', 'grupo_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    /**
     * Filtra los temas permitidos para un usuario según su rol activo y restricciones.
     */
    public static function filtrarTemasPermitidos($usuario, $rolActivo)
    {
        if (! $usuario) {
            return self::whereRaw('1=2');
        }

        if ($rolActivo && method_exists($rolActivo, 'hasPermissionTo') && $rolActivo->hasPermissionTo('temas.ver_todos_los_temas')) {
            return self::query();
        }

        $grupos = $usuario->gruposDondeAsiste()->select('grupos.id')->pluck('grupos.id')->toArray();
        $tiposGrupo = $usuario->gruposDondeAsiste()->select('grupos.tipo_grupo_id')->pluck('grupos.tipo_grupo_id')->toArray();
        $sedeId = $usuario->sede_id ?? $usuario->sede?->id;
        $tipoUsuarioId = $usuario->tipo_usuario_id ?? $usuario->tipoUsuario?->id;

        $temasCollection = self::leftJoin('sedes_temas', 'temas.id', '=', 'sedes_temas.tema_id')
            ->leftJoin('tipos_usuarios_temas', 'temas.id', '=', 'tipos_usuarios_temas.tema_id')
            ->leftJoin('tipos_grupos_temas', 'temas.id', '=', 'tipos_grupos_temas.tema_id')
            ->leftJoin('grupos_temas', 'temas.id', '=', 'grupos_temas.tema_id')
            ->where(function ($query) use ($sedeId, $tipoUsuarioId, $tiposGrupo, $grupos) {
                $query->where(function ($subQ) {
                    $subQ->whereNull('sedes_temas.sede_id')
                        ->whereNull('tipos_usuarios_temas.tipo_usuario_id');
                });

                if ($sedeId) {
                    $query->orWhere('sedes_temas.sede_id', $sedeId);
                }

                if ($tipoUsuarioId) {
                    $query->orWhere('tipos_usuarios_temas.tipo_usuario_id', $tipoUsuarioId);
                }

                if (! empty($tiposGrupo)) {
                    $query->orWhereIn('tipos_grupos_temas.tipo_grupo_id', $tiposGrupo);
                }

                if (! empty($grupos)) {
                    $query->orWhereIn('grupos_temas.grupo_id', $grupos);
                }
            })
            ->select('temas.*', 'sedes_temas.sede_id', 'tipos_usuarios_temas.tipo_usuario_id', 'tipos_grupos_temas.tipo_grupo_id', 'grupos_temas.grupo_id')
            ->get();

        $idsPermitidos = $temasCollection->filter(function ($tema) use ($sedeId, $grupos, $tipoUsuarioId, $tiposGrupo) {
            $bandera = true;
            if ($tema->sede_id && $tema->sede_id != $sedeId) {
                $bandera = false;
            }
            if ($tema->grupo_id && ! in_array($tema->grupo_id, $grupos)) {
                $bandera = false;
            }
            if ($tema->tipo_usuario_id && $tema->tipo_usuario_id != $tipoUsuarioId) {
                $bandera = false;
            }
            if ($tema->tipo_grupo_id && ! in_array($tema->tipo_grupo_id, $tiposGrupo)) {
                $bandera = false;
            }

            return $bandera;
        })->pluck('id')->unique()->toArray();

        if (count($idsPermitidos) > 0) {
            return self::whereIn('id', $idsPermitidos);
        }

        return self::whereRaw('1=2');
    }
}
