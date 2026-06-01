<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\EstadoCivil;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\EstadoTareaConsolidacion;
use App\Models\PasoCrecimiento;
use App\Models\PlanLector;
use App\Models\PlanLectorCategoria;
use App\Models\RangoEdad;
use App\Models\Sede;
use App\Models\TareaConsolidacion;
use App\Models\TipoUsuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlanLectorController extends Controller
{
    /**
     * Muestra el portal de inicio para los estudiantes (Sus planes actuales y el explorador).
     */
    public function inicio(Request $request)
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('planes_lectores.mis_planes_lectores');

        return view('contenido.paginas.plan-lector.inicio', compact('configuracion'));
    }

    /**
     * Muestra el visor de lectura para un plan específico.
     */
    public function lectura(PlanLector $plan, $dia = null)
    {
        $configuracion = Configuracion::find(1);

        return view('contenido.paginas.plan-lector.lectura', [
            'plan' => $plan,
            'dia_inicial' => $dia,
            'configuracion' => $configuracion,
        ]);
    }

    /**
     * Permite a un estudiante inscribirse silenciosamente en un plan lector.
     */
    public function inscribirse(Request $request, PlanLector $plan)
    {
        $usuario = auth()->user();

        // Evitar que el usuario se inscriba si no cumple con las restricciones (Validación del backend)
        $puedeVer = PlanLector::forUser($usuario)->where('planes_lectores.id', $plan->id)->exists();

        if (! $puedeVer || ! $plan->estado) {
            return back()->with('error', 'No tienes permitido acceder a este plan lector en estos momentos.');
        }

        // Crear la relación (attach lanza error si el unique la rechaza pero validamos antes)
        if (! $usuario->planesLectoresInscritos()->where('plan_lector_id', $plan->id)->exists()) {
            $usuario->planesLectoresInscritos()->attach($plan->id, [
                'estado' => 'inscrito',
                'fecha_inscripcion' => now(),
                'porcentaje_progreso' => 0,
            ]);
        }

        // Redirigir directamente al visor de lectura.
        return redirect()->route('planes-lectores.lectura', $plan->slug)->with('success', '¡Te has inscrito exitosamente a '.$plan->titulo.'!');
    }

    /**
     * Muestra la lista de planes lectores para administración.
     */
    public function gestionar(Request $request)
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('planes_lectores.subitem_gestionar_planes_lectores');

        $buscar = $request->get('buscar');
        $categoriasSeleccionadas = $request->get('categorias', []);
        $estado = $request->get('estado'); // '' = Todos, 1 = Activos, 0 = Inactivos

        $planesQuery = PlanLector::with(['autor', 'categorias']);

        if (trim($buscar) !== '') {
            $palabras = explode(' ', trim($buscar));

            $planesQuery->where(function ($query) use ($palabras) {
                foreach ($palabras as $palabra) {
                    if (trim($palabra) !== '') {
                        $term = '%'.trim($palabra).'%';

                        // Normalizamos acentos tanto en la columna como en el término de búsqueda para PostgreSQL
                        $query->where(DB::raw("TRANSLATE(planes_lectores.titulo, 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU')"), 'ILIKE', DB::raw("TRANSLATE('".$term."', 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU')"));
                    }
                }
            });
        }

        if (! empty($categoriasSeleccionadas)) {
            $planesQuery->whereHas('categorias', function ($q) use ($categoriasSeleccionadas) {
                $q->whereIn('plan_lector_categorias.id', $categoriasSeleccionadas);
            });
        }

        if ($estado !== null && $estado !== '') {
            $planesQuery->where('estado', $estado);
        }

        // Restricciones según permisos
        if (! $rolActivo->hasPermissionTo('planes_lectores.listar_todos_planes_lectores')) {
            if ($rolActivo->hasPermissionTo('planes_lectores.listar_solo_mis_planes_lectores')) {
                $planesQuery->where('autor_id', auth()->id());
            } else {
                // Si no tiene ninguno de los dos permisos de listado, restringir a ninguno (lista vacía)
                $planesQuery->whereRaw('1 = 0');
            }
        }

        $planes = $planesQuery->orderBy('created_at', 'desc')
            ->paginate(15);

        $categorias = PlanLectorCategoria::orderBy('nombre')->get();
        // Generar tags para los filtros activos
        $tagsBusqueda = [];
        $bandera = 0;

        if ($buscar) {
            $tagsBusqueda[] = (object) [
                'field' => 'buscar_plan',
                'fieldAux' => 'buscar_plan_offcanvas',
                'value' => $buscar,
                'label' => 'Búsqueda: '.$buscar,
            ];
            $bandera = 1;
        }

        if (! empty($categoriasSeleccionadas)) {
            foreach ($categoriasSeleccionadas as $catId) {
                $cat = $categorias->where('id', $catId)->first();
                if ($cat) {
                    $tagsBusqueda[] = (object) [
                        'field' => 'categorias',
                        'fieldAux' => null,
                        'value' => $cat->id,
                        'label' => 'Categoría: '.$cat->nombre,
                    ];
                }
            }
            $bandera = 1;
        }

        if ($estado !== null && $estado !== '') {
            $estadoStr = $estado == 1 ? 'Activos' : 'Inactivos';
            $tagsBusqueda[] = (object) [
                'field' => 'estado',
                'fieldAux' => null,
                'value' => $estado,
                'label' => 'Estado: '.$estadoStr,
            ];
            $bandera = 1;
        }

        return view('contenido.paginas.plan-lector.gestionar', compact('planes', 'configuracion', 'buscar', 'rolActivo', 'categorias', 'categoriasSeleccionadas', 'estado', 'tagsBusqueda', 'bandera'));
    }

    /**
     * Muestra el formulario para crear un nuevo plan lector.
     */
    public function crear()
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('planes_lectores.subitem_nuevo_plan_lector');

        $sedes = Sede::all();
        $estadosCiviles = EstadoCivil::all();
        $rangosEdad = RangoEdad::all();
        $tiposUsuario = TipoUsuario::all();
        $pasosCrecimiento = PasoCrecimiento::all();
        $estadosPasos = EstadoPasoCrecimientoUsuario::all();
        $tareasConsolidacion = TareaConsolidacion::all();
        $estadosTareas = EstadoTareaConsolidacion::all();
        $categorias = PlanLectorCategoria::all();

        return view('contenido.paginas.plan-lector.crear', compact(
            'configuracion',
            'rolActivo',
            'sedes',
            'estadosCiviles',
            'rangosEdad',
            'tiposUsuario',
            'pasosCrecimiento',
            'estadosPasos',
            'tareasConsolidacion',
            'estadosTareas',
            'categorias'
        ));
    }

    /**
     * Almacena un nuevo plan lector.
     */
    public function nuevo(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen_base64' => 'nullable|string',
            'visible_todos' => 'nullable',
            'genero' => 'nullable|integer',
            'categorias' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $plan = new PlanLector;
            $plan->titulo = $request->titulo;
            $plan->slug = Str::slug($request->titulo);
            $plan->descripcion = $request->descripcion;
            $plan->autor_id = auth()->id();
            $plan->visible_todos = $request->has('visible_todos');
            $plan->genero = $request->genero ?? 3;
            $plan->estado = true;

            // Manejo de la imagen base64 (recortada)
            if ($request->imagen_base64) {
                $imagenPartes = explode(';base64,', $request->imagen_base64);
                if (isset($imagenPartes[1])) {
                    $imagenBase64 = base64_decode($imagenPartes[1]);

                    $extension = 'jpg';
                    if (preg_match('/^data:image\/(\w+);base64/', $request->imagen_base64, $type)) {
                        $extension = strtolower($type[1]);
                    }

                    $nombreFoto = 'plan-'.time().'.'.$extension;
                    $pathPlanLector = 'img/plan-lector';

                    // Guardar usando Storage en la carpeta del tenant
                    Storage::put($pathPlanLector.'/'.$nombreFoto, $imagenBase64);

                    // Guardamos únicamente el nombre del archivo en la base de datos
                    $plan->imagen_url = $nombreFoto;
                }
            }

            $plan->save();

            // Guardar categorías
            if ($request->categorias) {
                $plan->categorias()->sync($request->categorias);
            }

            // Guardar restricciones
            if (! $plan->visible_todos) {
                if ($request->sedes) {
                    $plan->sedes()->sync($request->sedes);
                }
                if ($request->estadosCiviles) {
                    $plan->estadosCiviles()->sync($request->estadosCiviles);
                }
                if ($request->rangosEdad) {
                    $plan->rangosEdad()->sync($request->rangosEdad);
                }
                if ($request->tiposUsuario) {
                    $plan->tiposUsuario()->sync($request->tiposUsuario);
                }

                // Pasos de Crecimiento
                if ($request->has('pasos')) {
                    foreach ($request->pasos as $index => $paso) {
                        if (isset($paso['id']) && isset($paso['estado'])) {
                            $plan->procesosRequisito()->attach($paso['id'], [
                                'estado_paso_crecimiento_usuario_id' => $paso['estado'],
                                'indice' => $index,
                            ]);
                        }
                    }
                }

                // Tareas de Consolidación
                if ($request->has('tareas')) {
                    foreach ($request->tareas as $index => $tarea) {
                        if (isset($tarea['id']) && isset($tarea['estado'])) {
                            $plan->tareasRequisito()->attach($tarea['id'], [
                                'estado_tarea_consolidacion_id' => $tarea['estado'],
                                'indice' => $index,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('planes-lectores.gestionar')->with('success', 'Plan lector creado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error al crear el plan lector: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Muestra el formulario para editar un plan lector.
     */
    public function editar(PlanLector $plan)
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('planes_lectores.opcion_modificar_plan_lector');

        $sedes = Sede::all();
        $estadosCiviles = EstadoCivil::all();
        $rangosEdad = RangoEdad::all();
        $tiposUsuario = TipoUsuario::all();
        $pasosCrecimiento = PasoCrecimiento::all();
        $estadosPasos = EstadoPasoCrecimientoUsuario::all();
        $tareasConsolidacion = TareaConsolidacion::all();
        $estadosTareas = EstadoTareaConsolidacion::all();
        $categorias = PlanLectorCategoria::all();

        // Obtener IDs para preseleccionar
        $sedesPlan = $plan->sedes->pluck('id')->toArray();
        $estadosCivilesPlan = $plan->estadosCiviles->pluck('id')->toArray();
        $rangosEdadPlan = $plan->rangosEdad->pluck('id')->toArray();
        $tiposUsuarioPlan = $plan->tiposUsuario->pluck('id')->toArray();
        $categoriasPlan = $plan->categorias->pluck('id')->toArray();
        $pasosPlan = $plan->procesosRequisito;
        $tareasPlan = $plan->tareasRequisito;

        return view('contenido.paginas.plan-lector.editar', compact(
            'plan',
            'configuracion',
            'rolActivo',
            'sedes',
            'estadosCiviles',
            'rangosEdad',
            'tiposUsuario',
            'pasosCrecimiento',
            'estadosPasos',
            'tareasConsolidacion',
            'estadosTareas',
            'categorias',
            'sedesPlan',
            'estadosCivilesPlan',
            'rangosEdadPlan',
            'tiposUsuarioPlan',
            'categoriasPlan',
            'pasosPlan',
            'tareasPlan'
        ));
    }

    /**
     * Actualiza un plan lector.
     */
    public function actualizar(Request $request, PlanLector $plan)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen_base64' => 'nullable|string',
            'visible_todos' => 'nullable',
            'genero' => 'nullable|integer',
            'categorias' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $plan->titulo = $request->titulo;
            $plan->slug = Str::slug($request->titulo);
            $plan->descripcion = $request->descripcion;
            $plan->visible_todos = $request->has('visible_todos');
            $plan->genero = $request->genero ?? 3;

            // Manejo de la imagen base64 (recortada)
            if ($request->imagen_base64) {
                // Borrar imagen anterior si existe y no es el placeholder
                if ($plan->imagen_url && strpos($plan->imagen_url, 'placeholder') === false) {
                    $oldFilename = basename($plan->imagen_url);

                    // Intentar borrar de img/plan-lector
                    if (Storage::exists('img/plan-lector/'.$oldFilename)) {
                        Storage::delete('img/plan-lector/'.$oldFilename);
                    }
                    // Intentar borrar de img/planes_lectores (ruta heredada)
                    if (Storage::exists('img/planes_lectores/'.$oldFilename)) {
                        Storage::delete('img/planes_lectores/'.$oldFilename);
                    }

                    // Intentar borrar por compatibilidad física directa en public_path si existiese
                    $oldPath = public_path($plan->imagen_url);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $imagenPartes = explode(';base64,', $request->imagen_base64);
                if (isset($imagenPartes[1])) {
                    $imagenBase64 = base64_decode($imagenPartes[1]);

                    $extension = 'jpg';
                    if (preg_match('/^data:image\/(\w+);base64/', $request->imagen_base64, $type)) {
                        $extension = strtolower($type[1]);
                    }

                    $nombreFoto = 'plan-'.time().'.'.$extension;
                    $pathPlanLector = 'img/plan-lector';

                    // Guardar usando Storage en la carpeta del tenant
                    Storage::put($pathPlanLector.'/'.$nombreFoto, $imagenBase64);

                    // Guardamos únicamente el nombre del archivo en la base de datos
                    $plan->imagen_url = $nombreFoto;
                }
            }

            $plan->save();

            // Sincronizar categorías
            $plan->categorias()->sync($request->categorias ?? []);

            // Guardar restricciones
            if (! $plan->visible_todos) {
                $plan->sedes()->sync($request->sedes ?? []);
                $plan->estadosCiviles()->sync($request->estadosCiviles ?? []);
                $plan->rangosEdad()->sync($request->rangosEdad ?? []);
                $plan->tiposUsuario()->sync($request->tiposUsuario ?? []);

                // Limpiar previamente las relaciones pivot anidadas para reemplazarlas
                $plan->procesosRequisito()->detach();
                $plan->tareasRequisito()->detach();

                // Pasos de Crecimiento
                if ($request->has('pasos')) {
                    foreach ($request->pasos as $index => $paso) {
                        if (isset($paso['id']) && isset($paso['estado'])) {
                            $plan->procesosRequisito()->attach($paso['id'], [
                                'estado_paso_crecimiento_usuario_id' => $paso['estado'],
                                'indice' => $index,
                            ]);
                        }
                    }
                }

                // Tareas de Consolidación
                if ($request->has('tareas')) {
                    foreach ($request->tareas as $index => $tarea) {
                        if (isset($tarea['id']) && isset($tarea['estado'])) {
                            $plan->tareasRequisito()->attach($tarea['id'], [
                                'estado_tarea_consolidacion_id' => $tarea['estado'],
                                'indice' => $index,
                            ]);
                        }
                    }
                }
            } else {
                // Si es visible para todos, limpiar restricciones
                $plan->sedes()->detach();
                $plan->estadosCiviles()->detach();
                $plan->rangosEdad()->detach();
                $plan->tiposUsuario()->detach();
                $plan->procesosRequisito()->detach();
                $plan->tareasRequisito()->detach();
            }

            DB::commit();

            return redirect()->route('planes-lectores.gestionar')->with('success', 'Plan lector actualizado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error al actualizar el plan lector: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Elimina un plan lector.
     */
    public function eliminar(PlanLector $plan)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $rolActivo->verificacionDelPermiso('planes_lectores.opcion_eliminar_plan_lector');

        try {
            // Borrar imagen física
            if ($plan->imagen_url && strpos($plan->imagen_url, 'placeholder') === false) {
                $filename = basename($plan->imagen_url);

                // Intentar borrar de img/plan-lector
                if (Storage::exists('img/plan-lector/'.$filename)) {
                    Storage::delete('img/plan-lector/'.$filename);
                }
            }

            $plan->delete();

            return redirect()->route('planes-lectores.gestionar')->with('success', 'Plan lector eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el plan lector: '.$e->getMessage());
        }
    }

    /**
     * Muestra la vista con el componente Livewire para gestionar los días y contenidos.
     */
    public function gestionarContenido(PlanLector $plan)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        // Puedes cambiar esto a un permiso específico si es necesario,
        // de momento reutiliza el permiso de modificar plan lector.
        $rolActivo->verificacionDelPermiso('planes_lectores.opcion_modificar_plan_lector');

        return view('contenido.paginas.plan-lector.gestionar-contenido', compact('plan'));
    }

    /**
     * Alterna el estado (Activo/Inactivo) de un plan lector.
     */
    public function cambiarEstado(PlanLector $plan)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        // Se requiere permiso para modificar
        $rolActivo->verificacionDelPermiso('planes_lectores.opcion_modificar_plan_lector');

        try {
            $plan->estado = ! $plan->estado;
            $plan->save();

            $mensaje = $plan->estado ? 'Plan lector activado correctamente.' : 'Plan lector inactivado correctamente.';

            return back()->with('success', $mensaje);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cambiar el estado del plan lector: '.$e->getMessage());
        }
    }

    /**
     * Muestra el dashboard de estadísticas de planes lectores.
     */
    public function dashboard(Request $request)
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('planes_lectores.dashboard');

        // 1. Manejo de Filtros de Fecha (Estilo Consolidación)
        $rangoFechas = $request->get('rango_fechas');
        if ($rangoFechas && str_contains($rangoFechas, ' a ')) {
            $fechas = explode(' a ', $rangoFechas);
            $fechaInicio = Carbon::parse($fechas[0])->startOfDay();
            $fechaFin = isset($fechas[1]) ? Carbon::parse($fechas[1])->endOfDay() : Carbon::parse($fechas[0])->endOfDay();
        } else {
            $fechaInicio = now()->startOfMonth();
            $fechaFin = now()->endOfDay();
            $rangoFechas = $fechaInicio->format('Y-m-d').' a '.$fechaFin->format('Y-m-d');
        }

        // 2. Filtro de Sede
        $sedeId = $request->get('sede_id');
        $sedes = Sede::orderBy('nombre')->get();

        // 3. Consultas de KPIs
        $queryInscripciones = DB::table('plan_lector_users')
            ->join('users', 'plan_lector_users.user_id', '=', 'users.id')
            ->whereBetween('plan_lector_users.fecha_inscripcion', [$fechaInicio, $fechaFin]);

        if ($sedeId) {
            $queryInscripciones->where('users.sede_id', $sedeId);
        }

        $totalInscritos = (clone $queryInscripciones)->count();
        $totalFinalizados = (clone $queryInscripciones)->where('plan_lector_users.estado', 'completado')->count();

        // Lecturas Realizadas
        $queryLecturas = DB::table('plan_lector_dia_users')
            ->join('users', 'plan_lector_dia_users.user_id', '=', 'users.id')
            ->whereBetween('plan_lector_dia_users.fecha_completado', [$fechaInicio, $fechaFin]);

        if ($sedeId) {
            $queryLecturas->where('users.sede_id', $sedeId);
        }
        $totalLecturas = $queryLecturas->count();

        // 4. Datos para Gráficos
        $esMensual = $fechaInicio->diffInDays($fechaFin) > 30;
        $formatoAgrupacionSql = $esMensual ? "TO_CHAR(fecha_inscripcion, 'YYYY-MM')" : "TO_CHAR(fecha_inscripcion, 'YYYY-MM-DD')";
        $formatoAgrupacionLecturaSql = $esMensual ? "TO_CHAR(fecha_completado, 'YYYY-MM')" : "TO_CHAR(fecha_completado, 'YYYY-MM-DD')";

        // Gráfico de Actividad (Línea)
        $actividadDiaria = (clone $queryInscripciones)
            ->select(DB::raw($formatoAgrupacionSql.' as fecha'), DB::raw('COUNT(*) as total'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $lecturasDiariasQuery = DB::table('plan_lector_dia_users')
            ->join('users', 'plan_lector_dia_users.user_id', '=', 'users.id')
            ->whereBetween('plan_lector_dia_users.fecha_completado', [$fechaInicio, $fechaFin]);

        if ($sedeId) {
            $lecturasDiariasQuery->where('users.sede_id', $sedeId);
        }

        $lecturasDiarias = $lecturasDiariasQuery->select(DB::raw($formatoAgrupacionLecturaSql.' as fecha'), DB::raw('COUNT(*) as total'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Formatear datos para la gráfica de línea
        $labelsGrafica = [];
        $dataSerieInscripciones = [];
        $dataSerieLecturas = [];

        $tempInicio = $esMensual ? $fechaInicio->copy()->startOfMonth() : $fechaInicio->copy();
        $tempFin = $fechaFin->copy();

        $intervalo = $esMensual ? 'P1M' : 'P1D';
        $formatoPHP = $esMensual ? 'Y-m' : 'Y-m-d';

        $periodo = new \DatePeriod($tempInicio, new \DateInterval($intervalo), $tempFin->addSecond());
        foreach ($periodo as $dt) {
            $f = $dt->format($formatoPHP);
            // Label amigable para el frontend
            $labelsGrafica[] = $esMensual ? ucfirst(Carbon::instance($dt)->translatedFormat('M Y')) : $dt->format('d-M');
            $dataSerieInscripciones[] = $actividadDiaria->firstWhere('fecha', $f)?->total ?? 0;
            $dataSerieLecturas[] = $lecturasDiarias->firstWhere('fecha', $f)?->total ?? 0;
        }

        // Top 10 Planes
        $topPlanes = (clone $queryInscripciones)
            ->join('planes_lectores', 'plan_lector_users.plan_lector_id', '=', 'planes_lectores.id')
            ->select(
                'planes_lectores.titulo',
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(plan_lector_users.porcentaje_progreso) as progreso_promedio')
            )
            ->groupBy('planes_lectores.titulo')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Top 10 Categorías
        $distribucionCategoriasQuery = DB::table('plan_lector_users')
            ->join('users', 'plan_lector_users.user_id', '=', 'users.id')
            ->join('categoria_plan_lector', 'plan_lector_users.plan_lector_id', '=', 'categoria_plan_lector.plan_lector_id')
            ->join('plan_lector_categorias', 'categoria_plan_lector.plan_lector_categoria_id', '=', 'plan_lector_categorias.id')
            ->whereBetween('plan_lector_users.fecha_inscripcion', [$fechaInicio, $fechaFin]);

        if ($sedeId) {
            $distribucionCategoriasQuery->where('users.sede_id', $sedeId);
        }

        $topCategorias = (clone $distribucionCategoriasQuery)
            ->select('plan_lector_categorias.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('plan_lector_categorias.nombre')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Top 10 Autores
        $topAutores = (clone $queryInscripciones)
            ->join('planes_lectores', 'plan_lector_users.plan_lector_id', '=', 'planes_lectores.id')
            ->join('users as autores', 'planes_lectores.autor_id', '=', 'autores.id')
            ->select(
                'autores.id',
                'autores.foto',
                'autores.primer_nombre',
                'autores.primer_apellido',
                DB::raw("CONCAT(autores.primer_nombre, ' ', autores.primer_apellido) as name"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('autores.id', 'autores.foto', 'autores.primer_nombre', 'autores.primer_apellido')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return view('contenido.paginas.plan-lector.dashboard', compact(
            'configuracion',
            'rangoFechas',
            'sedes',
            'sedeId',
            'totalInscritos',
            'totalLecturas',
            'totalFinalizados',
            'labelsGrafica',
            'dataSerieInscripciones',
            'dataSerieLecturas',
            'topPlanes',
            'topCategorias',
            'topAutores'
        ));
    }
}
