<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\CursoUsuarioCargo;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index()
    {
       
        return view('contenido.paginas.cursos.gestionar-cursos');
    }

    public function campus()
    {
        return view('contenido.paginas.cursos.catalogo');
    }

    public function checkout()
    {
        $usuario = auth()->user();
        $carrito = \App\Models\CarritoCursoUser::where('user_id', $usuario->id)
            ->where('estado', 'pendiente')
            ->first();

        return view('contenido.paginas.cursos.checkout', compact('carrito'));
    }

    public function carrito()
    {
        return view('contenido.paginas.cursos.carrito');
    }

    public function compraFinalizada(\App\Models\CarritoCursoUser $carrito)
    {
        return view('contenido.paginas.cursos.compra-finalizada', compact('carrito'));
    }

    public function crear()
    {
        return view('contenido.paginas.cursos.crear-curso');
    }

    public function editar(Curso $curso)
    {
        return view('contenido.paginas.cursos.editar-curso', compact('curso'));
    }

    public function restricciones(Curso $curso)
    {
        return view('contenido.paginas.cursos.restricciones', compact('curso'));
    }

    public function detalle(Curso $curso)
    {
        return view('contenido.paginas.cursos.gestionar-detalle', compact('curso'));
    }

    public function contenido(Curso $curso)
    {
        return view('contenido.paginas.cursos.gestionar-contenido', compact('curso'));
    }

    public function actualizarDescripcion(Request $request, Curso $curso)
    {
        $request->validate([
            'descripcion_larga' => 'nullable|string',
            'mensaje_bienvenida' => 'nullable|string',
            'mensaje_aprobacion' => 'nullable|string',
        ]);

        $curso->update([
            'descripcion_larga' => $request->descripcion_larga,
            'mensaje_bienvenida' => $request->mensaje_bienvenida,
            'mensaje_aprobacion' => $request->mensaje_aprobacion,
        ]);

        return redirect()->route('cursos.detalle', $curso)->with('success', 'Descripción actualizada correctamente.');
    }

    public function inscritos(Curso $curso)
    {
        $configuracion = \App\Models\Configuracion::first();

        return view('contenido.paginas.cursos.gestionar-estudiantes', compact('curso', 'configuracion'));
    }

    // --- GESTIÓN DE EQUIPO DEL CURSO ---

    // --- RUTAS PÚBLICAS / FRONT-END ---

    /**
     * Muestra la vista de detalle público de un curso.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function previsualizar($slug)
    {
        // Buscar el curso por su slug, asegurarse de que esté activo (estado = 1), y cargar relaciones básicas
        $curso = Curso::with(['equipo.user', 'equipo.tipoCargo', 'aprendizajes', 'pasosRequisito', 'tareasRequisito', 'rangosEdad', 'estadosCiviles', 'categorias'])
            ->where('slug', $slug)
            ->where('estado', 'Publicado')
            ->firstOrFail();

        // Obtener la configuración general para los logos y URLs base de imágenes
        $configuracion = \App\Models\Configuracion::first();

        return view('contenido.paginas.cursos.previsualizar', compact('curso', 'configuracion'));
    }

    // --- GESTIÓN DE EQUIPO ---

    public function equipo(Curso $curso)
    {
        $equipo = $curso->equipo()->with(['user', 'tipoCargo'])->paginate(15);
        $tiposCargo = \App\Models\TipoCargoCurso::all();
        $configuracion = \App\Models\Configuracion::find(1); // Requisito para las rutas de imágenes

        return view('contenido.paginas.cursos.gestionar-equipo', compact('curso', 'equipo', 'tiposCargo', 'configuracion'));
    }

    public function guardarEquipo(Request $request, Curso $curso)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'tipo_cargo_curso_id' => 'required|exists:tipos_cargo_cursos,id',
            'activo' => 'boolean',
        ]);

        // Verificar si ya existe esta combinación
        $existe = CursoUsuarioCargo::where('curso_id', $curso->id)
            ->where('usuario_id', $request->usuario_id)
            ->where('tipo_cargo_curso_id', $request->tipo_cargo_curso_id)
            ->first();

        if ($existe) {
            return redirect()->back()->with('error', 'El usuario ya tiene asignado este cargo en el curso.');
        }

        CursoUsuarioCargo::create([
            'curso_id' => $curso->id,
            'usuario_id' => $request->usuario_id,
            'tipo_cargo_curso_id' => $request->tipo_cargo_curso_id,
            'activo' => $request->input('activo', 1),
        ]);

        return redirect()->back()->with('success', 'Miembro del equipo asignado correctamente.');
    }


    public function activarEquipo(\App\Models\CursoUsuarioCargo $miembro)
    {
        $miembro->update(['activo' => true]);

        return redirect()->back()->with('success', 'Miembro activado correctamente.');
    }

    public function desactivarEquipo(\App\Models\CursoUsuarioCargo $miembro)
    {
        $miembro->update(['activo' => false]);

        return redirect()->back()->with('success', 'Miembro desactivado correctamente.');
    }

    public function eliminarEquipo(Request $request)
    {
        $request->validate([
            'miembro_id' => 'required|exists:curso_usuario_cargo,id',
        ]);

        $miembro = CursoUsuarioCargo::findOrFail($request->miembro_id);
        $miembro->delete();

        return redirect()->back()->with('success', 'Miembro del equipo removido del curso correctamente.');
    }

    // --- PANEL DE MODERACIÓN DE FORO (ASESOR) ---
    public function foro()
    {
        return view('contenido.paginas.cursos.foro');
    }

    /**
     * Muestra el dashboard general de cursos con métricas y gráficos.
     */
    public function dashboard(Request $request): \Illuminate\View\View
    {
        // Filtros (valores por defecto)
        $fechaInicio = $request->query('fecha_inicio', \Carbon\Carbon::now()->subMonth()->format('Y-m-d'));
        $fechaFin = $request->query('fecha_fin', \Carbon\Carbon::now()->format('Y-m-d'));
        $carreraId = $request->query('carrera_id', '');

        // 1. Jerarquía Superior: Permisos Globales (Spatie)
        $user = auth()->user();
        $tieneAccesoTotal = false;
        $carrerasPermitidasIds = [];

        if ($user->can('cursos.listar_todos_cursos')) {
            $tieneAccesoTotal = true;
        } elseif ($user->can('cursos.listar_solo_cursos_asignados')) {
            // Acceso restringido -> Aplicamos el filtro de Cargos de Curso granular
            $usuarioId = $user->id;
            $cargosUsuario = \App\Models\CursoUsuarioCargo::with('tipoCargo')
                ->where('usuario_id', $usuarioId)
                ->where('activo', true)
                ->get();
               

            if ($cargosUsuario->isNotEmpty()) {
                // Verificar si algún cargo le da acceso total dentro del módulo
                $tieneAccesoTotal = $cargosUsuario->contains(function ($cargo) {
                    return $cargo->tipoCargo && $cargo->tipoCargo->puede_ver_todos_los_cursos;
                });

                if (!$tieneAccesoTotal) {
                    foreach ($cargosUsuario as $cargo) {
                        if ($cargo->tipoCargo && $cargo->tipoCargo->limita_carreras) {
                            $permitidas = $cargo->tipoCargo->carreras_permitidas ?? [];
                            if (is_array($permitidas)) {
                                $carrerasPermitidasIds = array_merge($carrerasPermitidasIds, $permitidas);
                            }
                        }
                    }
                    $carrerasPermitidasIds = array_unique($carrerasPermitidasIds);
                    
                    // Si no tiene "Ver Todos" y no limita carreras en ningún cargo, 
                    // se asume que solo ve lo asignado (pero el dashboard es por carrera, 
                    // así que si no hay carreras limitadas y tampoco acceso total, 
                    // mostramos vacío o podrías decidir mostrar todo lo asignado... 
                    // Por simplicidad, si limita_carreras es false en todos sus cargos 
                    // le damos acceso total para el dashboard si tiene el permiso de Spatie)
                    if (empty($carrerasPermitidasIds) && !$cargosUsuario->contains(fn($c) => $c->tipoCargo?->limita_carreras)) {
                        $tieneAccesoTotal = true;
                    }
                }
            }
        }

        // Obtener el query base de inscripciones según el rango de fecha
        $queryInscripciones = \App\Models\CursoUser::whereBetween('fecha_inscripcion', [
            $fechaInicio.' 00:00:00',
            $fechaFin.' 23:59:59',
        ]);

        // Aplicar la restricción de visibilidad de carreras según los permisos del asesor
        if (!$tieneAccesoTotal) {
            if (empty($carrerasPermitidasIds)) {
                $queryInscripciones->whereRaw('1 = 0');
            } else {
                $queryInscripciones->whereHas('curso', function ($q) use ($carrerasPermitidasIds) {
                    $q->whereIn('carrera_id', $carrerasPermitidasIds);
                });
            }
        }

       

        // Filtrar por carrera si se selecciona una
        if ($carreraId) {
            $queryInscripciones->whereHas('curso', function ($q) use ($carreraId) {
                $q->where('carrera_id', $carreraId);
            });
        }

        // 1. Total de nuevos inscritos
        $totalInscritos = (clone $queryInscripciones)->count();

        // 2. Promedio de avance
        $promedioAvance = (clone $queryInscripciones)->avg('porcentaje_progreso') ?? 0;

        // 3. Total que completaron el 100% (Global)
        $totalCompletados = (clone $queryInscripciones)->where('porcentaje_progreso', 100)->count();

        // 4. Datos por Género
        $datosGenero = (clone $queryInscripciones)
            ->join('users', 'curso_users.user_id', '=', 'users.id')
            ->select('users.genero', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('users.genero')
            ->get();

        // 5. Datos por Roles
        $datosRoles = (clone $queryInscripciones)
            ->join('model_has_roles', 'curso_users.user_id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name as rol', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->get();

        // 6. Desglose por curso (Tabla General)
        $inscritosPorCurso = (clone $queryInscripciones)
            ->join('cursos', 'curso_users.curso_id', '=', 'cursos.id')
            ->select('cursos.nombre', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('cursos.id', 'cursos.nombre')
            ->orderBy('total', 'desc')
            ->get();

        // 7. Estadísticas detalladas para cada curso (para el acordeón)
        $cursosDetalle = \App\Models\Curso::where(function ($q) use ($carreraId, $tieneAccesoTotal, $carrerasPermitidasIds) {
            if (!$tieneAccesoTotal) {
                if (empty($carrerasPermitidasIds)) {
                    $q->whereRaw('1 = 0');
                } else {
                    $q->whereIn('carrera_id', $carrerasPermitidasIds);
                }
            }

            if ($carreraId) {
                $q->where('carrera_id', $carreraId);
            }
        })->get()->map(function ($curso) use ($fechaInicio, $fechaFin) {
            // Filtro base de inscripciones para este curso específico
            $queryCurso = \App\Models\CursoUser::where('curso_id', $curso->id)
                ->whereBetween('fecha_inscripcion', [
                    $fechaInicio.' 00:00:00',
                    $fechaFin.' 23:59:59',
                ]);

            // Totales básicos
            $curso->stats_count = (clone $queryCurso)->count();
            $curso->stats_progreso = round((clone $queryCurso)->avg('porcentaje_progreso') ?? 0, 2);
            $curso->stats_completados = (clone $queryCurso)->where('porcentaje_progreso', 100)->count();

            // Distribución por Género para este curso
            $curso->stats_genero = (clone $queryCurso)
                ->join('users', 'curso_users.user_id', '=', 'users.id')
                ->select('users.genero', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('users.genero')
                ->get();

            // Distribución por Roles para este curso
            $curso->stats_roles = (clone $queryCurso)
                ->join('model_has_roles', 'curso_users.user_id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name as rol', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('roles.name')
                ->get();

            return $curso;
        });

        if ($tieneAccesoTotal) {
            $carreras = \App\Models\Carrera::orderBy('nombre')->get();
        } else {
            $carreras = \App\Models\Carrera::whereIn('id', $carrerasPermitidasIds)->orderBy('nombre')->get();
        }

        return view('contenido.paginas.cursos.dashboard', [
            'totalInscritos' => $totalInscritos,
            'promedioAvance' => round($promedioAvance, 2),
            'totalCompletados' => $totalCompletados,
            'datosGenero' => $datosGenero,
            'datosRoles' => $datosRoles,
            'inscritosPorCurso' => $inscritosPorCurso,
            'cursosDetalle' => $cursosDetalle,
            'carreras' => $carreras,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'carreraId' => $carreraId,
        ]);
    }

    /**
     * Exporta los alumnos inscritos a un archivo Excel filtrado.
     */
    public function exportarInscritos(Request $request, ?Curso $curso = null)
    {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');
        $carreraId = $request->query('carrera_id');
        $cursoId = $curso ? $curso->id : null;

        // 1. Jerarquía Superior: Permisos Globales (Spatie)
        $user = auth()->user();
        $tieneAccesoTotal = false;
        $carrerasPermitidasIds = [];

        if ($user->can('cursos.listar_todos_cursos')) {
            $tieneAccesoTotal = true;
        } elseif ($user->can('cursos.listar_solo_cursos_asignados')) {
            // Acceso restringido -> Aplicamos el filtro de Cargos de Curso granular
            $usuarioId = $user->id;
            $cargosUsuario = \App\Models\CursoUsuarioCargo::with('tipoCargo')
                ->where('usuario_id', $usuarioId)
                ->where('activo', true)
                ->get();

            if ($cargosUsuario->isNotEmpty()) {
                // Verificar si algún cargo le da acceso total dentro del módulo
                $tieneAccesoTotal = $cargosUsuario->contains(function ($cargo) {
                    return $cargo->tipoCargo && $cargo->tipoCargo->puede_ver_todos_los_cursos;
                });

                if (!$tieneAccesoTotal) {
                    foreach ($cargosUsuario as $cargo) {
                        if ($cargo->tipoCargo && $cargo->tipoCargo->limita_carreras) {
                            $permitidas = $cargo->tipoCargo->carreras_permitidas ?? [];
                            if (is_array($permitidas)) {
                                $carrerasPermitidasIds = array_merge($carrerasPermitidasIds, $permitidas);
                            }
                        }
                    }
                    $carrerasPermitidasIds = array_unique($carrerasPermitidasIds);

                    if (empty($carrerasPermitidasIds) && !$cargosUsuario->contains(fn($c) => $c->tipoCargo?->limita_carreras)) {
                        $tieneAccesoTotal = true;
                    }
                }
            }
        }

        $nombreArchivo = 'inscritos_cursos_'.now()->format('Ymd_His').'.xlsx';
        if ($curso) {
            $nombreArchivo = 'inscritos_'.\Illuminate\Support\Str::slug($curso->nombre).'_'.now()->format('Ymd_His').'.xlsx';
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CursosInscritosExport($cursoId, $carreraId, $fechaInicio, $fechaFin, $tieneAccesoTotal, $carrerasPermitidasIds),
            $nombreArchivo
        );
    }
}
