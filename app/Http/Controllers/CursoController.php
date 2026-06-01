<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\CursoUsuarioCargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        // OPTIMIZACIÓN (Fix #9): Configuracion cacheada 10 minutos
        $configuracion = Cache::remember('configuracion_global', 600, fn () => \App\Models\Configuracion::first());

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

        /*
         * OPTIMIZACIÓN (Fix #9):
         * Configuracion::first() se cachea 10 minutos en Valkey (Redis).
         * Este registro casi nunca cambia. Sin cache, cada request al servidor
         * ejecutaba una query SELECT a la tabla configuraciones innecesariamente.
         */
        $configuracion = Cache::remember('configuracion_global', 600, fn () => \App\Models\Configuracion::first());

        return view('contenido.paginas.cursos.previsualizar', compact('curso', 'configuracion'));
    }

    // --- GESTIÓN DE EQUIPO ---

    public function equipo(Curso $curso)
    {
        $equipo = $curso->equipo()->with(['user', 'tipoCargo'])->paginate(15);
        $tiposCargo = \App\Models\TipoCargoCurso::all();

        // OPTIMIZACIÓN (Fix #9): Configuracion cacheada 10 minutos
        $configuracion = Cache::remember('configuracion_global', 600, fn () => \App\Models\Configuracion::find(1));

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
        $rangoFechas = $request->query('rango_fechas');
        if ($rangoFechas) {
            $fechas = explode(' a ', $rangoFechas);
            if (count($fechas) >= 2) {
                $fechaInicio = \Carbon\Carbon::parse(trim($fechas[0]))->format('Y-m-d');
                $fechaFin = \Carbon\Carbon::parse(trim($fechas[1]))->format('Y-m-d');
            } else {
                $fechaInicio = \Carbon\Carbon::parse(trim($fechas[0]))->format('Y-m-d');
                $fechaFin = \Carbon\Carbon::parse(trim($fechas[0]))->format('Y-m-d');
            }
        } else {
            $fechaInicio = $request->query('fecha_inicio', \Carbon\Carbon::now()->subMonth()->format('Y-m-d'));
            $fechaFin = $request->query('fecha_fin', \Carbon\Carbon::now()->format('Y-m-d'));
            $rangoFechas = $fechaInicio.' a '.$fechaFin;
        }

        $carreraId = $request->query('carrera_id', '');

        /*
         * OPTIMIZACIÓN (Fix #8):
         * La lógica de verificación de permisos de cargos estaba duplicada literalmente entre
         * dashboard() y exportarInscritos(). Cualquier cambio había que hacerlo en dos lugares.
         * Se extrajo al método privado resolverPermisosAccesoCursos() para eliminar la duplicación.
         */
        [$tieneAccesoTotal, $carrerasPermitidasIds] = $this->resolverPermisosAccesoCursos();

        // Obtener el query base de inscripciones según el rango de fecha
        $queryInscripciones = \App\Models\CursoUser::whereBetween('fecha_inscripcion', [
            $fechaInicio.' 00:00:00',
            $fechaFin.' 23:59:59',
        ]);

        // Aplicar la restricción de visibilidad de carreras según los permisos del asesor
        if (! $tieneAccesoTotal) {
            if (empty($carrerasPermitidasIds)) {
                $queryInscripciones->whereRaw('1 = 0');
            } else {
                $queryInscripciones->whereHas('curso', function ($q) use ($carrerasPermitidasIds) {
                    $q->whereIn('carrera_id', $carrerasPermitidasIds);
                });
            }
        }

        // Filtrar por carrera si se selecciona una o varias
        if ($carreraId) {
            $queryInscripciones->whereHas('curso', function ($q) use ($carreraId) {
                if (is_array($carreraId)) {
                    $q->whereIn('carrera_id', array_filter($carreraId));
                } else {
                    $q->where('carrera_id', $carreraId);
                }
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

        // 5.1 Datos por Entidad Apilados por Carrera
        $queryCarrerasEntidades = (clone $queryInscripciones)
            ->join('cursos', 'curso_users.curso_id', '=', 'cursos.id')
            ->join('carreras', 'cursos.carrera_id', '=', 'carreras.id')
            ->join('users', 'curso_users.user_id', '=', 'users.id')
            ->join('tipo_usuarios', 'users.tipo_usuario_id', '=', 'tipo_usuarios.id')
            ->leftJoin('entidades_relacionadas', 'tipo_usuarios.entidad_relacionada_id', '=', 'entidades_relacionadas.id')
            ->select(
                'carreras.nombre as carrera',
                \Illuminate\Support\Facades\DB::raw("COALESCE(entidades_relacionadas.nombre, 'Sin Entidad') as entidad"),
                \Illuminate\Support\Facades\DB::raw('count(*) as total')
            )
            ->groupBy('carrera', 'entidad')
            ->orderBy('carrera')
            ->get();

        $carrerasLabels = $queryCarrerasEntidades->pluck('carrera')->unique()->values()->toArray();
        $todasEntidades = $queryCarrerasEntidades->pluck('entidad')->unique()->values()->toArray();

        $seriesEntidades = [];
        foreach ($todasEntidades as $entidad) {
            $dataEntidad = [];
            foreach ($carrerasLabels as $carrera) {
                $valor = $queryCarrerasEntidades->where('carrera', $carrera)->where('entidad', $entidad)->first();
                $dataEntidad[] = $valor ? $valor->total : 0;
            }
            $seriesEntidades[] = [
                'name' => $entidad,
                'data' => $dataEntidad,
            ];
        }

        $datosCarrerasEntidades = [
            'labels' => $carrerasLabels,
            'series' => $seriesEntidades,
        ];

        // 6. Desglose por curso (Tabla General)
        $inscritosPorCurso = (clone $queryInscripciones)
            ->join('cursos', 'curso_users.curso_id', '=', 'cursos.id')
            ->select('cursos.nombre', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('cursos.id', 'cursos.nombre')
            ->orderBy('total', 'desc')
            ->get();

        // 7. Estadísticas detalladas para cada curso (para el acordeón)
        /*
         * OPTIMIZACIÓN (Fix #1): Reescritura completa del bloque cursosDetalle.
         *
         * ANTES: Se hacía Curso::get()->map(function($curso) { ... }) y dentro del map() se lanzaban
         * 6 queries separadas POR CADA CURSO (count, avg, count completados, join género, join roles,
         * join entidades). Con 20 cursos = 120 queries; con 50 cursos = 300 queries.
         *
         * AHORA: Se reemplazan esas 6×N queries por 5 queries agregadas totales que traen los datos
         * de TODOS los cursos a la vez usando groupBy('curso_id'). Luego se asignan a cada curso
         * en memoria usando keyBy() — sin tocar más la BD.
         */

        // Obtener los IDs de los cursos visibles según los permisos del usuario
        $cursoIdsVisibles = Curso::query()
            ->when(! $tieneAccesoTotal && empty($carrerasPermitidasIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->when(! $tieneAccesoTotal && ! empty($carrerasPermitidasIds), fn ($q) => $q->whereIn('carrera_id', $carrerasPermitidasIds))
            ->when($carreraId, function ($q) use ($carreraId) {
                is_array($carreraId)
                    ? $q->whereIn('carrera_id', array_filter($carreraId))
                    : $q->where('carrera_id', $carreraId);
            })
            ->pluck('id');

        // Query 1: Totales básicos (count, promedio y completados) por curso — una sola query
        $statsTotales = DB::table('curso_users')
            ->selectRaw('curso_id, COUNT(*) as stats_count, AVG(porcentaje_progreso) as stats_progreso, SUM(CASE WHEN porcentaje_progreso = 100 THEN 1 ELSE 0 END) as stats_completados')
            ->whereIn('curso_id', $cursoIdsVisibles)
            ->whereBetween('fecha_inscripcion', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->groupBy('curso_id')
            ->get()
            ->keyBy('curso_id');

        // Query 2: Distribución por género por curso — una sola query
        $statsGenero = DB::table('curso_users')
            ->join('users', 'curso_users.user_id', '=', 'users.id')
            ->selectRaw('curso_users.curso_id, users.genero, COUNT(*) as total')
            ->whereIn('curso_users.curso_id', $cursoIdsVisibles)
            ->whereBetween('curso_users.fecha_inscripcion', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->groupBy('curso_users.curso_id', 'users.genero')
            ->get()
            ->groupBy('curso_id');

        // Query 3: Distribución por roles por curso — una sola query
        $statsRoles = DB::table('curso_users')
            ->join('model_has_roles', 'curso_users.user_id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->selectRaw('curso_users.curso_id, roles.name as rol, COUNT(*) as total')
            ->whereIn('curso_users.curso_id', $cursoIdsVisibles)
            ->whereBetween('curso_users.fecha_inscripcion', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->groupBy('curso_users.curso_id', 'roles.name')
            ->get()
            ->groupBy('curso_id');

        // Query 4: Distribución por entidades por curso — una sola query
        $statsEntidades = DB::table('curso_users')
            ->join('users', 'curso_users.user_id', '=', 'users.id')
            ->join('tipo_usuarios', 'users.tipo_usuario_id', '=', 'tipo_usuarios.id')
            ->leftJoin('entidades_relacionadas', 'tipo_usuarios.entidad_relacionada_id', '=', 'entidades_relacionadas.id')
            ->selectRaw("curso_users.curso_id, COALESCE(entidades_relacionadas.nombre, 'Sin Entidad') as entidad, COUNT(*) as total")
            ->whereIn('curso_users.curso_id', $cursoIdsVisibles)
            ->whereBetween('curso_users.fecha_inscripcion', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->groupBy('curso_users.curso_id', 'entidad')
            ->get()
            ->groupBy('curso_id');

        // Ahora cargamos los cursos y asignamos las stats desde las colecciones en memoria (sin más queries)
        $cursosDetalle = Curso::whereIn('id', $cursoIdsVisibles)->get()->map(function ($curso) use ($statsTotales, $statsGenero, $statsRoles, $statsEntidades) {
            $totales = $statsTotales->get($curso->id);
            $curso->stats_count = $totales?->stats_count ?? 0;
            $curso->stats_progreso = round($totales?->stats_progreso ?? 0, 2);
            $curso->stats_completados = $totales?->stats_completados ?? 0;

            // Estas colecciones ya están en memoria — solo hacemos get() sobre PHP arrays
            $curso->stats_genero = $statsGenero->get($curso->id, collect());
            $curso->stats_roles = $statsRoles->get($curso->id, collect());
            $curso->stats_entidades = $statsEntidades->get($curso->id, collect());

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
            'datosCarrerasEntidades' => $datosCarrerasEntidades,
            'inscritosPorCurso' => $inscritosPorCurso,
            'cursosDetalle' => $cursosDetalle,
            'carreras' => $carreras,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'carreraId' => $carreraId,
            'rangoFechas' => $rangoFechas,
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

        /*
         * OPTIMIZACIÓN (Fix #8):
         * La lógica de permisos se extrajo aquí al mismo método privado resolverPermisosAccesoCursos()
         * que usa dashboard(). Antes era código duplicado palabra por palabra.
         */
        [$tieneAccesoTotal, $carrerasPermitidasIds] = $this->resolverPermisosAccesoCursos();

        $nombreArchivo = 'inscritos_cursos_'.now()->format('Ymd_His').'.xlsx';
        if ($curso) {
            $nombreArchivo = 'inscritos_'.\Illuminate\Support\Str::slug($curso->nombre).'_'.now()->format('Ymd_His').'.xlsx';
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CursosInscritosExport($cursoId, $carreraId, $fechaInicio, $fechaFin, $tieneAccesoTotal, $carrerasPermitidasIds),
            $nombreArchivo
        );
    }

    /**
     * Recibe un archivo del contenido del curso vía Alpine.js fetch (POST).
     * Evita usar Livewire WithFileUploads para prevenir problemas de multi-tenancy.
     */
    public function uploadArchivoContenido(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'archivo' => 'required|file|mimes:pdf,pptx,ppt,jpg,jpeg,png,gif,webp|max:10240', // 10MB max
                'curso_id' => 'required|exists:cursos,id',
            ]);

            $configuracion = Cache::remember('configuracion_global', 600, fn () => \App\Models\Configuracion::first());

            $file = $request->file('archivo');
            $cursoId = $request->input('curso_id');

            $nombreLimpio = preg_replace('/[^A-Za-z0-9.\-\_]/', '', $file->getClientOriginalName());
            $nombre = time().'_'.$nombreLimpio;
            $directorio = 'archivos/cursos/'.$cursoId;

            // Almacenar en el disco 'public' respetando la estructura del tenant
            $path = \Illuminate\Support\Facades\Storage::disk('public')->putFileAs($directorio, $file, $nombre);

            return response()->json([
                'success' => true,
                'nombre' => $nombre,
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el archivo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * OPTIMIZACIÓN (Fix #8): Método privado que centraliza la resolución de permisos de acceso
     * a cursos por cargo. Antes este bloque estaba duplicado en dashboard() y exportarInscritos().
     *
     * Retorna un array: [bool $tieneAccesoTotal, array $carrerasPermitidasIds]
     */
    private function resolverPermisosAccesoCursos(): array
    {
        $user = auth()->user();
        $tieneAccesoTotal = false;
        $carrerasPermitidasIds = [];

        if ($user->can('cursos.listar_todos_cursos')) {
            $tieneAccesoTotal = true;
        } elseif ($user->can('cursos.listar_solo_cursos_asignados')) {
            $cargosUsuario = CursoUsuarioCargo::with('tipoCargo')
                ->where('usuario_id', $user->id)
                ->where('activo', true)
                ->get();

            if ($cargosUsuario->isNotEmpty()) {
                $tieneAccesoTotal = $cargosUsuario->contains(
                    fn ($cargo) => $cargo->tipoCargo?->puede_ver_todos_los_cursos
                );

                if (! $tieneAccesoTotal) {
                    foreach ($cargosUsuario as $cargo) {
                        if ($cargo->tipoCargo?->limita_carreras) {
                            $permitidas = $cargo->tipoCargo->carreras_permitidas ?? [];
                            if (is_array($permitidas)) {
                                $carrerasPermitidasIds = array_merge($carrerasPermitidasIds, $permitidas);
                            }
                        }
                    }
                    $carrerasPermitidasIds = array_unique($carrerasPermitidasIds);

                    if (empty($carrerasPermitidasIds) && ! $cargosUsuario->contains(fn ($c) => $c->tipoCargo?->limita_carreras)) {
                        $tieneAccesoTotal = true;
                    }
                }
            }
        }

        return [$tieneAccesoTotal, $carrerasPermitidasIds];
    }
}
