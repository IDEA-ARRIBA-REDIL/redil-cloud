<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Curso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cursos';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion_corta',
        'descripcion_larga',
        'imagen_portada',
        'video_preview_url',
        'categoria_id', // Para categorias antiguas si se usan, o la nueva relacion
        'nivel_dificultad',
        'es_obligatorio',
        'estado',
        'orden_destacado',
        'cupos_totales',
        'dias_acceso_limitado',
        'duracion_estimada_dias',
        'fecha_inicio',
        'es_gratuito',
        'precio',
        'precio_comparacion',
        'moneda_id',
        'carrera_id',
        'genero',
        'vinculacion_grupo',
        'actividad_grupo',
        'excluyente',
        'mensaje_bienvenida',
        'mensaje_aprobacion',
        'limite_reintentos',
        'dias_castigo',
        'terminos_condiciones',
    ];

    protected $appends = [
        'portada_url',
    ];

    // Relaciones
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function getPortadaUrlAttribute(): string
    {
        if ($this->imagen_portada && $this->imagen_portada !== '' && $this->imagen_portada !== 'default.png') {
            return tenant_asset('img/cursos/portadas/'.$this->imagen_portada);
        }

        return Storage::disk('global_media')->url('cursos/default.png');
    }

    public function getPortadaCampusUrlAttribute(): string
    {
        $tenantPath = 'img/cursos/portadas/portada-campus.png';

        // 1. Verificamos si el archivo existe en el disco del tenant
        if (Storage::disk('tenant')->exists($tenantPath)) {
            // Si existe, retornamos la URL del tenant
            return tenant_asset($tenantPath);
        }

        // 2. Si no existe, usamos la portada por defecto del disco global
        return Storage::disk('global_media')->url('cursos/default-campus.png');
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(CategoriaCurso::class, 'curso_categoria', 'curso_id', 'categoria_curso_id');
    }

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'es_gratuito' => 'boolean',
        'fecha_inicio' => 'datetime',
    ];

    // Relaciones Directas

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }

    // Relaciones Pivot

    public function rolesRestringidos(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'curso_roles_restriccion', 'curso_id', 'role_id')
            ->withTimestamps();
    }

    public function tiposPago(): BelongsToMany
    {
        return $this->belongsToMany(TipoPago::class, 'curso_tipos_pago', 'curso_id', 'tipo_pago_id')
            ->withTimestamps();
    }

    // Relaciones con Pasos de Crecimiento

    public function pasosRequisito(): BelongsToMany
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'curso_paso_requisito', 'curso_id', 'paso_crecimiento_id')
            ->withPivot('estado', 'estado_paso_crecimiento_usuario_id', 'indice')
            ->withTimestamps();
    }

    public function pasosIniciar(): BelongsToMany
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'curso_paso_iniciar', 'curso_id', 'paso_crecimiento_id')
            ->withPivot('estado', 'estado_paso_crecimiento_usuario_id', 'indice')
            ->withTimestamps();
    }

    public function pasosCulminar(): BelongsToMany
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'curso_paso_culminar', 'curso_id', 'paso_crecimiento_id')
            ->withPivot('estado', 'estado_paso_crecimiento_usuario_id', 'indice')
            ->withTimestamps();
    }

    // Relaciones con Tareas de Consolidación

    public function tareasRequisito(): BelongsToMany
    {
        return $this->belongsToMany(TareaConsolidacion::class, 'curso_tarea_requisito', 'curso_id', 'tarea_consolidacion_id')
            ->withPivot('estado_tarea_consolidacion_id', 'indice')
            ->withTimestamps();
    }

    public function tareasCulminar(): BelongsToMany
    {
        return $this->belongsToMany(TareaConsolidacion::class, 'curso_tarea_culminar', 'curso_id', 'tarea_consolidacion_id')
            ->withPivot('estado_tarea_consolidacion_id', 'indice')
            ->withTimestamps();
    }

    // --- RESTRICCIONES GENERALES ---

    public function sedes()
    {
        return $this->belongsToMany(Sede::class, 'curso_sede')->withTimestamps();
    }

    public function rangosEdad()
    {
        return $this->belongsToMany(RangoEdad::class, 'curso_rango_edad')->withTimestamps();
    }

    public function estadosCiviles()
    {
        return $this->belongsToMany(EstadoCivil::class, 'curso_estado_civil')->withTimestamps();
    }

    public function tipoServicios()
    {
        return $this->belongsToMany(TipoServicioGrupo::class, 'curso_tipo_servicio', 'curso_id', 'tipo_servicio_id')->withTimestamps();
    }

    // Contenido Detallado
    public function aprendizajes()
    {
        return $this->hasMany(CursoAprendizaje::class)->orderBy('orden');
    }

    // Módulos del Curso
    public function modulos()
    {
        return $this->hasMany(CursoModulo::class)->orderBy('orden');
    }

    // Equipo del Curso (Asesores, Creadores, etc.)
    public function equipo()
    {
        return $this->hasMany(CursoUsuarioCargo::class, 'curso_id');
    }

    // Estudiantes del Curso
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'curso_users', 'curso_id', 'user_id')
            ->withPivot('estado', 'fecha_inscripcion', 'fecha_vencimiento_acceso', 'porcentaje_progreso', 'numero_reintentos', 'ultimo_reintento_at')
            ->withTimestamps();
    }

    // --- MOTOR DE VALIDACION PARA USUARIO (LMS) ---
    /**
     * Valida si un usuario cumple con todas las restricciones del curso.
     * Retorna un arreglo estructurado indicando el éxito y las razones de fallo.
     */
    public function validarRequisitosUsuarioCurso(User $usuario): array
    {
        $razones = [];
        $cumple = true;

        // 1. Verificación: ¿Ya está inscrito?
        $inscripcion = CursoUser::where('curso_id', $this->id)
            ->where('user_id', $usuario->id)
            ->first();

        if ($inscripcion) {
            // Si está activo y NO ha superado el límite de reintentos, ya está inscrito
            if ($inscripcion->estado === 'activo') {
                if ($this->limite_reintentos == 0 || $inscripcion->numero_reintentos < $this->limite_reintentos) {
                    return [
                        'cumple' => false,
                        'codigo' => 'YA_INSCRITO',
                        'razones' => ['Ya te encuentras inscrito en este curso.'],
                    ];
                }
            }

            // Si superó el límite, verificar tiempo de castigo
            if ($this->limite_reintentos > 0 && $inscripcion->numero_reintentos >= $this->limite_reintentos) {
                if ($this->dias_castigo > 0 && $inscripcion->ultimo_reintento_at) {
                    $fechaLiberacion = \Carbon\Carbon::parse($inscripcion->ultimo_reintento_at)->addDays($this->dias_castigo);
                    if (now()->lessThan($fechaLiberacion)) {
                        $diasRestantes = now()->diffInDays($fechaLiberacion, false);

                        return [
                            'cumple' => false,
                            'codigo' => 'EN_PENALIZACION',
                            'razones' => ["Has agotado tus intentos. Debes esperar {$diasRestantes} días más para volver a inscribirte."],
                        ];
                    }
                }

                // Si el tiempo de castigo ya pasó, permitimos que se vuelva a inscribir (lo cual creará/actualizará la inscripción más adelante)
                // Nota: La lógica de re-inscripción debería resetear el contador si es una "nueva compra" o "nueva entrada".
            }
        }

        /*
         * OPTIMIZACIÓN N+1 (Fix #2):
         * Antes se llamaba a ->rangosEdad()->count(), ->estadosCiviles()->count(), etc. por separado.
         * Cada llamada generaba un COUNT(*) a la BD, y luego al iterar con $this->rangosEdad se lanzaba
         * otro SELECT — resultado: hasta 8 queries de BD por cada usuario que revisaba un curso.
         *
         * Ahora usamos loadMissing() para cargar las 4 relaciones en memoria de una sola vez (máximo
         * 4 queries en total, y solo si aún no estaban cargadas por eager loading previo). Después,
         * isNotEmpty() y foreach operan sobre las colecciones PHP ya en memoria — sin tocar la BD.
         */
        $this->loadMissing(['rangosEdad', 'estadosCiviles', 'pasosRequisito', 'tareasRequisito']);

        // 2. Verificación de Género
        // En la UI de cursos: 1 = Masculino, 2 = Femenino, 3 = Ambos (o null)
        if ($this->genero && $this->genero != 3) {
            // Asumimos que User->genero guarda 'M' / 'F' o similar, o adaptamos según tu BD
            // Si en User se guarda 1/2 también, la validación directa es más fácil,
            // pero si en User es char 'M'/'F', lo mapeamos temporalmente para la verificación:
            $generoUsuarioNumerico = ($usuario->genero == 'M') ? 1 : (($usuario->genero == 'F') ? 2 : 3);

            if ($generoUsuarioNumerico != $this->genero) {
                $cumple = false;
                $generoRequerido = $this->genero == 1 ? 'Masculino' : 'Femenino';
                $razones[] = 'Este curso es exclusivo para el género '.$generoRequerido.'.';
            }
        }

        // 3. Verificación de Edad — isNotEmpty() opera sobre la colección ya cargada en memoria, sin query adicional
        if ($this->rangosEdad->isNotEmpty()) {
            $edadUsuario = $usuario->edad();
            $cumpleEdad = false;
            foreach ($this->rangosEdad as $rango) {
                if ($edadUsuario >= $rango->edad_minima && $edadUsuario <= $rango->edad_maxima) {
                    $cumpleEdad = true;
                    break;
                }
            }
            if (! $cumpleEdad) {
                $cumple = false;
                $nombresRangos = $this->rangosEdad->pluck('nombre')->join(', ');
                $razones[] = 'Tu edad no entra en los rangos permitidos para este curso ('.$nombresRangos.').';
            }
        }

        // 4. Verificación de Estado Civil — colección ya en memoria, sin query adicional
        if ($this->estadosCiviles->isNotEmpty()) {
            $estadosPermitidos = $this->estadosCiviles->pluck('id')->toArray();
            if (! in_array($usuario->estado_civil_id, $estadosPermitidos)) {
                $cumple = false;
                $nombresEstados = $this->estadosCiviles->pluck('nombre')->join(' o ');
                $razones[] = 'Este curso requiere estado civil: '.$nombresEstados.'.';
            }
        }

        // 5. Verificación de Pasos de Crecimiento — colección ya en memoria, sin query adicional
        if ($this->pasosRequisito->isNotEmpty()) {
            $pasosUsuario = $usuario->pasosCrecimiento()->get()->keyBy('id');
            foreach ($this->pasosRequisito as $pasoRequerido) {
                $estadoPasoExigido = $pasoRequerido->pivot->estado_paso_crecimiento_usuario_id;

                // Si el usuario no tiene el paso o no lo tiene en el estado requerido
                if (! isset($pasosUsuario[$pasoRequerido->id]) || $pasosUsuario[$pasoRequerido->id]->pivot->estado_id != $estadoPasoExigido) {
                    $cumple = false;
                    $estadoExigidoNombre = EstadoPasoCrecimientoUsuario::find($estadoPasoExigido)?->nombre ?? 'completado';
                    $razones[] = "Debes tener el proceso '".$pasoRequerido->nombre."' en estado ".strtolower($estadoExigidoNombre).'.';
                }
            }
        }

        // 6. Verificación de Tareas de Consolidación — colección ya en memoria, sin query adicional
        if ($this->tareasRequisito->isNotEmpty()) {
            $tareasUsuario = $usuario->tareasConsolidacion()->get()->keyBy('id');
            foreach ($this->tareasRequisito as $tareaRequerida) {
                $estadoTareaExigida = $tareaRequerida->pivot->estado_tarea_consolidacion_id;

                if (! isset($tareasUsuario[$tareaRequerida->id]) || $tareasUsuario[$tareaRequerida->id]->pivot->estado_tarea_consolidacion_id != $estadoTareaExigida) {
                    $cumple = false;
                    $estadoExigidoNombre = EstadoTareaConsolidacion::find($estadoTareaExigida)?->nombre ?? 'completado';
                    $razones[] = "Debes tener la tarea '".$tareaRequerida->nombre."' en estado ".strtolower($estadoExigidoNombre).'.';
                }
            }
        }

        return [
            'cumple' => $cumple,
            'codigo' => $cumple ? 'OK' : 'NO_CUMPLE_REQUISITOS',
            'razones' => $razones,
        ];
    }

    // --- RELACIONES PARA FORO COMUNITARIO (LMS) ---
    public function hilosForo()
    {
        return $this->hasMany(CursoForoHilo::class, 'curso_id')->orderBy('created_at', 'desc');
    }
}
