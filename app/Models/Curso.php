<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'mensaje_aprobacion'
    ];

    // Relaciones
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
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
            ->withPivot('estado', 'fecha_inscripcion', 'fecha_vencimiento_acceso', 'porcentaje_progreso')
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

        // 1. Verificación: ¿Ya está inscrito de forma activa?
        $inscrito = CursoUser::where('curso_id', $this->id)
            ->where('user_id', $usuario->id)
            ->where('estado', 'activo')
            ->exists();

        if ($inscrito) {
            return [
                'cumple' => false,
                'codigo' => 'YA_INSCRITO',
                'razones' => ['Ya te encuentras inscrito en este curso.']
            ];
        }

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
                $razones[] = "Este curso es exclusivo para el género " . $generoRequerido . ".";
            }
        }

        // 3. Verificación de Edad
        if ($this->rangosEdad()->count() > 0) {
            $edadUsuario = $usuario->edad();
            $cumpleEdad = false;
            foreach ($this->rangosEdad as $rango) {
                if ($edadUsuario >= $rango->edad_minima && $edadUsuario <= $rango->edad_maxima) {
                    $cumpleEdad = true;
                    break;
                }
            }
            if (!$cumpleEdad) {
                $cumple = false;
                $nombresRangos = $this->rangosEdad->pluck('nombre')->join(', ');
                $razones[] = "Tu edad no entra en los rangos permitidos para este curso (" . $nombresRangos . ").";
            }
        }

        // 4. Verificación de Estado Civil
        if ($this->estadosCiviles()->count() > 0) {
            $estadosPermitidos = $this->estadosCiviles->pluck('id')->toArray();
            if (!in_array($usuario->estado_civil_id, $estadosPermitidos)) {
                $cumple = false;
                $nombresEstados = $this->estadosCiviles->pluck('nombre')->join(' o ');
                $razones[] = "Este curso requiere estado civil: " . $nombresEstados . ".";
            }
        }

        // 5. Verificación de Pasos de Crecimiento
        if ($this->pasosRequisito()->count() > 0) {
            $pasosUsuario = $usuario->pasosCrecimiento()->get()->keyBy('id');
            foreach ($this->pasosRequisito as $pasoRequerido) {
                $estadoPasoExigido = $pasoRequerido->pivot->estado_paso_crecimiento_usuario_id;

                // Si el usuario no tiene el paso o no lo tiene en el estado requerido
                if (!isset($pasosUsuario[$pasoRequerido->id]) || $pasosUsuario[$pasoRequerido->id]->pivot->estado_id != $estadoPasoExigido) {
                    $cumple = false;
                    $estadoExigidoNombre = EstadoPasoCrecimientoUsuario::find($estadoPasoExigido)?->nombre ?? 'completado';
                    $razones[] = "Debes tener el proceso '" . $pasoRequerido->nombre . "' en estado " . strtolower($estadoExigidoNombre) . ".";
                }
            }
        }

        // 6. Verificación de Tareas de Consolidación
        if ($this->tareasRequisito()->count() > 0) {
            $tareasUsuario = $usuario->tareasConsolidacion()->get()->keyBy('id');
            foreach ($this->tareasRequisito as $tareaRequerida) {
                $estadoTareaExigida = $tareaRequerida->pivot->estado_tarea_consolidacion_id;

                if (!isset($tareasUsuario[$tareaRequerida->id]) || $tareasUsuario[$tareaRequerida->id]->pivot->estado_tarea_consolidacion_id != $estadoTareaExigida) {
                    $cumple = false;
                    $estadoExigidoNombre = EstadoTareaConsolidacion::find($estadoTareaExigida)?->nombre ?? 'completado';
                    $razones[] = "Debes tener la tarea '" . $tareaRequerida->nombre . "' en estado " . strtolower($estadoExigidoNombre) . ".";
                }
            }
        }

        return [
            'cumple' => $cumple,
            'codigo' => $cumple ? 'OK' : 'NO_CUMPLE_REQUISITOS',
            'razones' => $razones
        ];
    }

    // --- RELACIONES PARA FORO COMUNITARIO (LMS) ---
    public function hilosForo()
    {
        return $this->hasMany(CursoForoHilo::class, 'curso_id')->orderBy('created_at', 'desc');
    }
}
