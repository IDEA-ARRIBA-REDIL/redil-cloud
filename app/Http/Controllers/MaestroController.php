<?php

namespace App\Http\Controllers;

use App\Models\AlumnoRespuestaItem;
use App\Models\BannerEscuela;
use App\Models\Configuracion;
use App\Models\CortePeriodo;
use App\Models\Escuela;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\Maestro; // Para el filtro de Sede
use App\Models\Matricula; // Para el filtro de Escuela
use App\Models\MatriculaHorarioMateriaPeriodo; // No se usa directamente aquí, pero lo tenías importado
use App\Models\MatriculaHorarioMateriaPeriodo as EstadoAcademico;
use App\Models\Periodo; // No se usa directamente aquí
use App\Models\ReporteAsistenciaAlumnos;    // No se usa directamente aquí
use App\Models\ReporteAsistenciaClase; // <--- IMPORTANTE
use App\Models\Role; // <--- IMPORTANTE
use App\Models\Sede; // <--- IMPORTANTE
use App\Models\User; // <--- IMPORTANTE
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Para transacciones si fuera necesario
use Illuminate\Support\Facades\Log; // Para obtener el usuario autenticado
use Illuminate\Validation\Rule; // Para manejo de fechas

// Para los tags

class MaestroController extends Controller
{
    /**
     * Muestra la lista de maestros.
     */
    public function gestionar(Request $request)
    {
        $configuracion = Configuracion::find(1);

        // 1. OBTENCIÓN DE DATOS PARA FILTROS
        // Se obtienen todas las colecciones necesarias una sola vez.
        $sedesParaFiltro = Sede::orderBy('nombre')->get();
        $periodosParaFiltro = Periodo::orderBy('nombre', 'desc')->get();
        $escuelasParaFiltro = Escuela::orderBy('nombre')->get();

        // 2. INICIALIZACIÓN Y CAPTURA DE FILTROS
        $tagsBusqueda = [];
        $banderaFiltros = false;
        $filtros = $request->only([
            'filtro_busqueda_general',
            'filtro_estado_maestro',
            'filtro_sede',
            'filtro_periodo',
            'filtro_escuela',
        ]);

        // 3. CONSTRUCCIÓN DE LA CONSULTA BASE
        // Eager loading del usuario para evitar problemas N+1 al mostrar la lista.
        $queryMaestros = Maestro::query()->with('user');

        // 4. APLICACIÓN DE FILTROS Y CREACIÓN DE TAGS

        // Filtro de Búsqueda General
        if (! empty($filtros['filtro_busqueda_general'])) {
            $termino = strtolower($filtros['filtro_busqueda_general']);
            $queryMaestros->whereHas('user', function ($qUser) use ($termino) {
                $qUser->where(
                    fn ($q) => $q->whereRaw('LOWER(primer_nombre) LIKE ?', ["%{$termino}%"])
                        ->orWhereRaw('LOWER(primer_apellido) LIKE ?', ["%{$termino}%"])
                        ->orWhereRaw('LOWER(identificacion) LIKE ?', ["%{$termino}%"])
                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$termino}%"])
                );
            });
            $tagsBusqueda[] = (object) ['label' => $filtros['filtro_busqueda_general'], 'field' => 'filtro_busqueda_general'];
            $banderaFiltros = true;
        }

        // Filtro por Estado
        if (isset($filtros['filtro_estado_maestro']) && $filtros['filtro_estado_maestro'] !== '') {
            $queryMaestros->where('activo', (bool) $filtros['filtro_estado_maestro']);
            $label = 'Estado: '.((bool) $filtros['filtro_estado_maestro'] ? 'Activo' : 'Inactivo');
            $tagsBusqueda[] = (object) ['label' => $label, 'field' => 'filtro_estado_maestro'];
            $banderaFiltros = true;
        }

        // Filtro por Sede
        if (! empty($filtros['filtro_sede'])) {
            $queryMaestros->whereHas('horariosMateriaPeriodo.horarioBase.aula.sede', function ($qSede) use ($filtros) {
                $qSede->where('sedes.id', $filtros['filtro_sede']);
            });
            // ✅ OPTIMIZACIÓN: Buscamos en la colección ya cargada, no en la BD.
            $sedeModel = $sedesParaFiltro->firstWhere('id', $filtros['filtro_sede']);
            if ($sedeModel) {
                $tagsBusqueda[] = (object) ['label' => 'Sede: '.$sedeModel->nombre, 'field' => 'filtro_sede'];
                $banderaFiltros = true;
            }
        }

        // Filtro por Periodo
        if (! empty($filtros['filtro_periodo'])) {
            $queryMaestros->whereHas('horariosMateriaPeriodo.materiaPeriodo.periodo', function ($qPeriodo) use ($filtros) {
                $qPeriodo->where('periodos.id', $filtros['filtro_periodo']);
            });
            // ✅ OPTIMIZACIÓN: Buscamos en la colección ya cargada.
            $periodoModel = $periodosParaFiltro->firstWhere('id', $filtros['filtro_periodo']);
            if ($periodoModel) {
                $tagsBusqueda[] = (object) ['label' => 'Periodo: '.$periodoModel->nombre, 'field' => 'filtro_periodo'];
                $banderaFiltros = true;
            }
        }

        // Filtro por Escuela
        if (! empty($filtros['filtro_escuela'])) {
            $queryMaestros->whereHas('horariosMateriaPeriodo.materiaPeriodo.periodo.escuela', function ($qEscuela) use ($filtros) {
                $qEscuela->where('escuelas.id', $filtros['filtro_escuela']);
            });
            // ✅ OPTIMIZACIÓN: Buscamos en la colección ya cargada.
            $escuelaModel = $escuelasParaFiltro->firstWhere('id', $filtros['filtro_escuela']);
            if ($escuelaModel) {
                $tagsBusqueda[] = (object) ['label' => 'Escuela: '.$escuelaModel->nombre, 'field' => 'filtro_escuela'];
                $banderaFiltros = true;
            }
        }

        // 5. EJECUCIÓN FINAL DE LA CONSULTA
        $maestros = $queryMaestros->latest('created_at')->paginate(16);

        // --- CAMBIO SOLICITADO: Filtrar roles que tengan el permiso 'escuelas.es_maestro' ---

        // Usamos el scope de Spatie 'permission' para filtrar los roles que tienen ese permiso asignado.
        $rolesMaestro = Role::permission('escuelas.es_maestro')->get();

        // 6. DEVOLVER LA VISTA CON TODOS LOS DATOS
        return view('contenido.paginas.escuelas.maestros.gestionar-maestros', [
            'maestros' => $maestros,
            'configuracion' => $configuracion,
            'sedesParaFiltro' => $sedesParaFiltro,
            'periodosParaFiltro' => $periodosParaFiltro,
            'escuelasParaFiltro' => $escuelasParaFiltro,
            'tagsBusqueda' => $tagsBusqueda,
            'banderaFiltros' => $banderaFiltros,
            'rolesMaestro' => $rolesMaestro,
            // Renombrado para mayor claridad, ahora pasamos el array completo de filtros
            'filtrosActuales' => $filtros,
        ]);
    }

    public function guardar(Request $request)
    {
        // El nombre del input del Livewire es 'buscador-usuario'
        $validados = $request->validate([
            'buscador-usuario' => ['required', 'integer', \Illuminate\Validation\Rule::unique('maestros', 'user_id')],
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'required|boolean',
            'role_id' => 'required|integer|exists:roles,id', // Validación mejorada
        ], [
            'buscador-usuario.required' => 'Debes seleccionar un usuario.',
            'buscador-usuario.unique' => 'Este usuario ya ha sido asignado como maestro.',
            'activo.required' => 'Debes indicar si el maestro estará activo.',
            'descripcion.max' => 'La descripción no puede exceder los 1000 caracteres.',
            'role_id.required' => 'Debes seleccionar un rol para el maestro.',
            'role_id.exists' => 'El rol seleccionado no es válido.',

        ]);

        try {
            DB::beginTransaction(); // Usar transacción para asegurar integridad

            $usuario = User::findOrFail($request->input('buscador-usuario'));

            // --- LÓGICA DE ASIGNACIÓN DE ROL MEJORADA ---
            // Verificamos si el usuario ya tiene este rol (aunque sea con activo=0)
            if ($usuario->roles()->where('roles.id', $request->role_id)->exists()) {
                // Si existe, actualizamos el pivot para activarlo
                $usuario->roles()->updateExistingPivot($request->role_id, [
                    'activo' => 1,
                    // 'dependiente' => 0, // Opcional: si quisieras resetearlo
                    // 'model_type' no es necesario en updateExistingPivot si la relación ya resuelve la llave compuesta,
                    // pero Laravel estándar no maneja 'model_type' en la PK compuesta bien con belongsToMany puro.
                    // Sin embargo, updateExistingPivot usa los IDs definidos en la relación.
                ]);
            } else {
                // Si no existe, lo adjuntamos con los campos extra
                $usuario->roles()->attach($request->role_id, [
                    'activo' => 1,
                    'dependiente' => 0,
                    'model_type' => 'App\Models\User', // NECESARIO porque la tabla pivote lo requiere y es parte de la PK
                ]);
            }

            Maestro::create([
                // Usamos la clave correcta del request
                'user_id' => $request->input('buscador-usuario'),
                'descripcion' => $request->descripcion,
                'activo' => $request->activo,
            ]);

            DB::commit();

            return redirect()->route('maestros.gestionar')
                ->with('mensaje_exito', 'Maestro creado y rol asignado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear maestro: '.$e->getMessage());

            return back()->with('mensaje_error', 'Ocurrió un error al crear el maestro. Inténtalo de nuevo.')
                ->withInput();
        }
    }

    /**
     * Elimina un maestro específico y desvincula su rol de maestro asociado al usuario.
     * NO elimina el registro del usuario.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function eliminar(Request $request)
    {
        // Validación básica de la entrada
        $request->validate(['maestro_id' => 'required|integer|exists:maestros,id']);
        $maestroId = $request->input('maestro_id');

        // Usamos una transacción para asegurar la integridad de los datos
        DB::beginTransaction();
        try {
            // 1. Encuentra el registro Maestro (con su Usuario asociado) o falla si no existe
            $maestro = Maestro::with('user')->findOrFail($maestroId); // Carga el usuario asociado
            $usuario = $maestro->user; // Accede al usuario

            // Guarda el nombre para el mensaje antes de cualquier eliminación
            $nombreUsuario = optional($usuario)->nombre(3) ?? 'Maestro ID '.$maestro->id;

            // 2. Verifica si el usuario existe antes de intentar quitar roles
            if ($usuario) {
                // ----> CORRECCIÓN: Lógica para quitar el rol <----

                // a) Encuentra TODOS los roles marcados como 'es_maestro' que tenga el usuario.
                //    Esto es más seguro si un usuario pudiera tener varios roles de maestro (aunque sea raro).
                $rolesMaestroParaQuitar = $usuario->roles()->where('es_maestro', true)->pluck('id');

                // b) Si se encontraron roles de maestro, desvincúlalos (detach).
                if ($rolesMaestroParaQuitar->isNotEmpty()) {
                    $usuario->roles()->detach($rolesMaestroParaQuitar);
                    Log::info("Roles de maestro [IDs: {$rolesMaestroParaQuitar->implode(',')}] desvinculados del usuario ID {$usuario->id} al eliminar maestro ID {$maestro->id}.");
                } else {
                    Log::info("Usuario ID {$usuario->id} no tenía roles marcados como 'es_maestro' al eliminar maestro ID {$maestro->id}.");
                }
            } else {
                Log::warning("No se encontró usuario asociado al maestro ID {$maestro->id} durante la eliminación. No se pudieron quitar roles.");
            }

            // 3. Elimina el registro del Maestro (Soft Delete o Hard Delete según tu modelo)
            $maestro->delete();

            // 4. Si todo salió bien, confirma la transacción
            DB::commit();

            return redirect()->route('maestros.gestionar') // Asegúrate que 'maestros.gestionar' sea tu ruta correcta
                ->with('mensaje_success', "Maestro '{$nombreUsuario}' eliminado y rol desvinculado correctamente.");
        } catch (\Exception $e) {
            // 5. Si algo falla, revierte la transacción
            DB::rollBack();
            Log::error("Error al eliminar maestro ID {$maestroId}: ".$e->getMessage());

            return back()->with('mensaje_error', 'Ocurrió un error al eliminar el maestro.');
        }
    }

    /**
     * Muestra los horarios asignados a un maestro específico (para administración).
     */
    public function horariosAsignados(Maestro $maestro)
    {
        $horariosAsignados = $maestro->horariosMateriaPeriodo()
            ->with([
                'materiaPeriodo.materia:id,nombre',
                'materiaPeriodo.periodo:id,nombre',
                'horarioBase.aula:id,nombre,sede_id',
                'horarioBase.aula.sede:id,nombre',
            ])
            ->orderByPivot('created_at', 'desc')
            ->paginate(10);

        return view('contenido.paginas.escuelas.maestros.horarios-maestro', [
            'maestro' => $maestro,
            'horariosAsignados' => $horariosAsignados,
        ]);
    }

    /**
     * Elimina la asignación de un horario a un maestro.
     */
    public function eliminarHorarioAsignado(Maestro $maestro, HorarioMateriaPeriodo $horarioMateriaPeriodo)
    {
        try {
            $maestro->horariosMateriaPeriodo()->detach($horarioMateriaPeriodo->id);

            return redirect()->route('maestros.horariosAsignados', $maestro)
                ->with('mensaje_exito', 'Asignación de horario eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al eliminar asignación del horario {$horarioMateriaPeriodo->id} para el maestro {$maestro->id}: ".$e->getMessage());

            return back()->with('mensaje_error', 'Ocurrió un error al eliminar la asignación del horario.');
        }
    }

    /**
     * Muestra los horarios que el maestro tiene asignados (vista del maestro).
     */
    public function misHorarios(Request $request, User $user) // <-- 1. Añadimos Request
    {
        $maestro = Maestro::where('user_id', $user->id)->first();

        // 2. Obtenemos los parámetros de ordenamiento de la URL.
        // Por defecto (carga inicial), ordena por 'materia_nombre' en 'asc'.
        $sortField = $request->input('sort', 'materia_nombre');
        $sortDirection = $request->input('direction', 'asc');

        if (isset($maestro->horariosMateriaPeriodo)) {
            $query = $maestro->horariosMateriaPeriodo()
                ->with([
                    'materiaPeriodo.materia:id,nombre',
                    'materiaPeriodo.periodo:id,nombre',
                    'horarioBase.aula:id,nombre,sede_id',
                    'horarioBase.aula.sede:id,nombre',
                ]);
        } else {
            abort(403, 'No tienes ningun horario asignado.');
        }

        // 3. Unimos las tablas necesarias para poder ordenar por el nombre de la materia.
        // Esto es necesario porque 'nombre' está en la tabla 'materias', no en la tabla pivote.
        if ($sortField === 'materia_nombre') {
            $query->join('materia_periodo', 'horarios_materia_periodo.materia_periodo_id', '=', 'materia_periodo.id')
                ->join('materias', 'materia_periodo.materia_id', '=', 'materias.id')
                ->orderBy('materias.nombre', $sortDirection)
                // Es buena práctica seleccionar explícitamente la tabla principal para evitar conflictos de 'id'.
                ->select('horarios_materia_periodo.*');
        } else {
            // Si en el futuro agregas más ordenamientos, puedes manejarlos aquí.
            // Por ahora, mantenemos el orden por defecto si el campo no es válido.
            $query->orderBy('created_at', 'desc');
        }

        $horariosAsignados = $query->paginate(10)
            // 4. ¡MUY IMPORTANTE! Esto añade los parámetros de orden a los links de paginación.
            ->appends($request->query());

        // 6. Obtenemos los banners activos para el dashboard
        $banners = BannerEscuela::where('activo', true)->latest()->get();

        return view('contenido.paginas.escuelas.maestros.mis-horarios', [
            'maestro' => $maestro,
            'horariosAsignados' => $horariosAsignados,
            // 5. Pasamos las variables de ordenamiento a la vista para los estilos de las flechas.
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
            'banners' => $banners,
        ]);
    }

    /**
     * Muestra el panel de control detallado para una clase específica asignada a un maestro.
     *
     * @param  Maestro  $maestro  El maestro que gestiona la clase.
     * @param  HorarioMateriaPeriodo  $horarioAsignado  La clase específica que se está visualizando.
     * @return \Illuminate\View\View
     */
    // En el archivo: app/Http/Controllers/MaestroController.php

    // En el archivo: app/Http/Controllers/MaestroController.php

    public function dashboardClase(Maestro $maestro, HorarioMateriaPeriodo $horarioAsignado)
    {
        // --- 1. CONFIGURACIÓN INICIAL Y CARGA DE DATOS PRINCIPALES ---
        $usuarioActivo = Auth::user();
        $configuracion = Configuracion::find(1);
        $notaMinimaAprobatoria = (float) ($configuracion->nota_aprobatoria ?? 3.0);

        $horarioAsignado->load([
            'materiaPeriodo.materia:id,nombre',
            'materiaPeriodo.periodo:id,nombre,fecha_inicio,fecha_fin',
            'horarioBase.aula.sede',
        ]);
        $horarioId = $horarioAsignado->id;
        $periodoId = $horarioAsignado->materiaPeriodo?->periodo_id;
        if (! $periodoId) {
            return back()->with('mensaje_error', 'No se pudo determinar el periodo para esta clase.');
        }

        // --- 2. OBTENER Y PREPARAR LA ESTRUCTURA DE CORTES DE EVALUACIÓN ---
        $cortesDelPeriodo = CortePeriodo::where('periodo_id', $periodoId)
            ->with('corteEscuela:id,nombre,orden,porcentaje')
            ->select('cortes_periodo.*')
            ->join('cortes_escuela', 'cortes_periodo.corte_escuela_id', '=', 'cortes_escuela.id')
            ->orderBy('cortes_escuela.orden', 'asc')
            ->get();

        $cortesDefinidosParaVista = $cortesDelPeriodo->map(function ($cp) {
            return [
                'id_db' => $cp->id,
                'id_html' => 'corte_cp_'.$cp->id,
                'nombre' => $cp->corteEscuela?->nombre ?? 'Corte',
                'porcentaje_materia' => (float) $cp->porcentaje,
            ];
        });

        // --- 3. OBTENER Y PROCESAR LOS DATOS DE LOS ALUMNOS MATRICULADOS ---
        $estadosAcademicos = EstadoAcademico::where('horario_materia_periodo_id', $horarioAsignado->id)
            ->with([
                'user:id,email,genero,telefono_movil,sede_id,primer_nombre,segundo_nombre,primer_apellido,segundo_apellido,identificacion,fecha_nacimiento',
                'matricula.trasladosLog', // Carga la matrícula y sus traslados
            ])
            ->join('users', 'matricula_horario_materia_periodo.user_id', '=', 'users.id')
            ->orderBy('users.primer_apellido', 'asc')
            ->orderBy('users.primer_nombre', 'asc')
            ->select('matricula_horario_materia_periodo.*')
            ->get();

        $alumnosData = collect();
        $conteoGenero = ['hombres' => 0, 'mujeres' => 0, 'otros' => 0];

        // Obtenemos los IDs de los usuarios de los estados académicos para buscar sus matrículas
        $userIds = $estadosAcademicos->pluck('user_id')->unique();

        // Obtenemos las matrículas bloqueadas para este horario de una sola vez
        $matriculasBloqueadasCount = Matricula::where('horario_materia_periodo_id', $horarioId)
            ->where('bloqueado', true)
            ->count(); // Solo contamos, no necesitamos los detalles

        // Contadores para el gráfico de aprobación
        $aprobadosCount = 0;
        $reprobadosCount = 0;
        $cursandoCount = 0; // Contará solo los que están cursando y NO están bloqueados

        foreach ($estadosAcademicos as $estado) {
            // Aseguramos que el usuario y la matrícula existen
            if (! $estado->user || ! $estado->matricula) {
                continue;
            }

            // Verificamos si la matrícula de este estado académico está bloqueada
            $estaBloqueado = $estado->matricula->bloqueado;

            // --- Cálculos de Promedios y Asistencias (Tu lógica actual, sin cambios) ---
            $promediosPorCorteCalculados = [];
            $promedioGeneralMateriaCalculado = 0.0;
            $itemsDeLaClase = ItemCorteMateriaPeriodo::where('horario_materia_periodo_id', $horarioAsignado->id)->get(); // Optimizable si se hace fuera del loop
            $calificacionesDelAlumno = AlumnoRespuestaItem::where('user_id', $estado->user->id)
                ->whereIn('item_corte_materia_periodo_id', $itemsDeLaClase->pluck('id'))
                ->pluck('nota_obtenida', 'item_corte_materia_periodo_id');

            foreach ($cortesDefinidosParaVista as $corte) {
                $notaAcumuladaDelCorte = 0.0;
                $itemsDelCorte = $itemsDeLaClase->where('corte_periodo_id', $corte['id_db']);
                if ($itemsDelCorte->isNotEmpty()) {
                    foreach ($itemsDelCorte as $item) {
                        $notaItem = $calificacionesDelAlumno->get($item->id);
                        if ($notaItem !== null && is_numeric($item->porcentaje)) {
                            $notaAcumuladaDelCorte += (float) $notaItem * ((float) $item->porcentaje / 100);
                        }
                    }
                }
                $notaFinalCorte = round($notaAcumuladaDelCorte, 2);
                $promediosPorCorteCalculados[$corte['id_html']] = $notaFinalCorte;
                if ($corte['porcentaje_materia'] > 0) {
                    $promedioGeneralMateriaCalculado += $notaFinalCorte * ($corte['porcentaje_materia'] / 100);
                }
            }
            $promedioGeneralMateriaCalculado = round($promedioGeneralMateriaCalculado, 2);

            $asistenciasContadas = ReporteAsistenciaAlumnos::where('user_id', $estado->user->id)
                ->whereHas('reporteClase', fn ($query) => $query->where('horario_materia_periodo_id', $horarioId))
                ->where('asistio', true)->count();
            $inasistenciasContadas = ReporteAsistenciaAlumnos::where('user_id', $estado->user->id)
                ->whereHas('reporteClase', fn ($query) => $query->where('horario_materia_periodo_id', $horarioId))
                ->where('asistio', false)->count();
            // --- Fin Cálculos ---

            // --- LÓGICA DE ESTADO MEJORADA Y CENTRALIZADA ---
            $haAprobado = $promedioGeneralMateriaCalculado >= $notaMinimaAprobatoria;
            $periodoFinalizado = $horarioAsignado->materiaPeriodo->periodo->fecha_fin->isPast();
            $estadoMateria = 'Cursando'; // Estado por defecto

            if ($estaBloqueado) {
                $estadoMateria = 'Bloqueado'; // Máxima prioridad si está bloqueado
            } elseif ($periodoFinalizado) {
                $estadoMateria = $haAprobado ? 'Aprobado' : 'Reprobado';
            } else {
                // Si no está bloqueado y el periodo no ha terminado
                if ($haAprobado) {
                    $estadoMateria = 'Aprobando'; // Va aprobando pero aún no es final
                } else {
                    $estadoMateria = 'Cursando'; // Cursando y no cumple nota mínima aún
                }
            }

            // --- Actualización de Contadores para el Gráfico ---
            // Solo contamos para Aprobados/Reprobados/Cursando si NO está bloqueado
            if (! $estaBloqueado) {
                if ($estadoMateria === 'Aprobado' || $estadoMateria === 'Aprobando') {
                    $aprobadosCount++;
                } elseif ($estadoMateria === 'Reprobado') {
                    $reprobadosCount++;
                } elseif ($estadoMateria === 'Cursando') {
                    $cursandoCount++;
                }
            }
            // El contador de bloqueados se obtuvo directamente antes del bucle

            $ultimoTraslado = $estado->matricula->trasladosLog
                ->where('destino_horario_id', $horarioId) // Corregido para buscar destino
                ->first();

            // Guardamos los datos para la tabla del dashboard
            $alumnosData->push([
                'user_model' => $estado->user,
                'id_db' => $estado->user->id,
                'nombre_completo' => $estado->user->nombre(4),
                'promedios_por_corte' => $promediosPorCorteCalculados,
                'promedio_final_materia' => $promedioGeneralMateriaCalculado,
                'asistencias' => $asistenciasContadas,
                'inasistencias' => $inasistenciasContadas,
                'estado_materia' => $estadoMateria, // Estado calculado con la nueva lógica
                'ultimo_traslado' => $ultimoTraslado,
                'ha_aprobado' => $haAprobado, // Puedes quitarlo si 'estado_materia' es suficiente
            ]);

            // Contamos género
            if (isset($estado->user->genero)) {
                if ($estado->user->genero === 0) {
                    $conteoGenero['hombres']++;
                } elseif ($estado->user->genero === 1) {
                    $conteoGenero['mujeres']++;
                } else {
                    $conteoGenero['otros']++;
                }
            }
        } // Fin del bucle foreach $estadosAcademicos

        $totalAlumnos = $estadosAcademicos->count(); // Usamos el conteo original de estados

        // --- PREPARACIÓN DE DATOS PARA LOS GRÁFICOS ---
        $datosGenero = [
            'categorias' => ['Hombres', 'Mujeres', 'Otros'],
            'series' => [['name' => 'Matriculados', 'data' => array_values($conteoGenero)]],
        ];

        // --- CORRECCIÓN: Estructura final para el gráfico de aprobación ---
        $datosAprobacion = [
            'categorias' => ['Aprobados', 'Reprobados', 'Cursando', 'Bloqueados'], // Añadida la categoría
            'series' => [[
                'name' => 'Alumnos',
                'data' => [
                    $aprobadosCount,        // Suma de Aprobado + Aprobando
                    $reprobadosCount,
                    $cursandoCount,         // Cursando (NO bloqueados)
                    $matriculasBloqueadasCount, // El conteo directo de bloqueados
                ],
            ]],
        ];

        $nombreMateria = $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'Materia no disponible';
        $nombrePeriodo = $horarioAsignado->materiaPeriodo?->periodo?->nombre ?? 'Periodo no disponible';
        $nombreDocente = $maestro->user?->nombre(3) ?? 'Docente no asignado';
        $infoClase = "Periodo: {$nombrePeriodo} | Docente: {$nombreDocente}";

        return view('contenido.paginas.escuelas.maestros.dashboard-clase', [
            'maestro' => $maestro,
            'configuracion' => $configuracion,
            'horarioAsignado' => $horarioAsignado,
            'alumnosParaDashboard' => $alumnosData, // Datos para la tabla detallada
            'cortesDefinidos' => $cortesDefinidosParaVista,
            'nombreMateria' => $nombreMateria,
            'infoClase' => $infoClase,
            // Aseguramos que datosGenero se envíe a la vista
            'datosGenero' => $datosGenero,
            'datosAprobacion' => $datosAprobacion, // Datos para el gráfico de aprobación (corregido)
            'totalAlumnos' => $totalAlumnos, // Total general
            // Datos extra para validación de permisos en vista
            // Datos extra para validación de permisos en vista
            'usuarioLogueado' => $usuarioActivo,
            'rolUsuarioLogueado' => $usuarioActivo ? $usuarioActivo->roles : null,
            'rolActivo' => $usuarioActivo ? $usuarioActivo->roles()->wherePivot('activo', true)->first() : null,
        ]);
    }

    public function gestionarClase(Maestro $maestro, HorarioMateriaPeriodo $horarioAsignado)
    {
        $configuracion = Configuracion::find(1);

        $horarioAsignado->load([
            'materiaPeriodo.materia:id,nombre',
            'materiaPeriodo.periodo:id,nombre',
        ]);

        // Obtener alumnos matriculados
        $estadosAcademicos = EstadoAcademico::where('horario_materia_periodo_id', $horarioAsignado->id)
            ->with(['user'])
            ->get();

        $users = $estadosAcademicos->map(function ($estado) {
            return $estado->user;
        })->filter();

        return view('contenido.paginas.escuelas.maestros.gestionar-clase', [
            'maestro' => $maestro,
            'horarioAsignado' => $horarioAsignado,
            'configuracion' => $configuracion,
            'users' => $users,
            'rolActivo' => Auth::user() ? Auth::user()->roles()->wherePivot('activo', true)->first() : null,
        ]);
    }

    public function gestionarAlumno(Maestro $maestro, HorarioMateriaPeriodo $horarioAsignado, User $alumno)
    {
        $configuracion = Configuracion::find(1);
        $horarioAsignado->load([
            'materiaPeriodo.materia',
            'materiaPeriodo.periodo',
        ]);

        $estadoAcademicoAlumno = EstadoAcademico::where('user_id', $alumno->id)
            ->where('horario_materia_periodo_id', $horarioAsignado->id)
            ->first();

        // 1. Reportes de asistencia reales del alumno para este horario
        $reportesAsistencia = ReporteAsistenciaAlumnos::where('user_id', $alumno->id)
            ->whereHas('reporteClase', function ($query) use ($horarioAsignado) {
                $query->where('horario_materia_periodo_id', $horarioAsignado->id);
            })
            ->with(['reporteClase', 'motivoInasistencia'])
            ->get();

        $totalAsistenciasCumplidas = $reportesAsistencia->where('asistio', true)->count();
        $totalClasesReportadas = $reportesAsistencia->count();

        // 2. Ítems de evaluación del horario y respuestas/notas del alumno
        $itemsEvaluacion = ItemCorteMateriaPeriodo::where('horario_materia_periodo_id', $horarioAsignado->id)
            ->with(['cortePeriodo', 'respuestas' => function ($query) use ($alumno) {
                $query->where('user_id', $alumno->id);
            }])
            ->orderBy('orden', 'asc')
            ->get();

        // 3. Historial de Escuelas real del alumno (solo escuelas con materias activas)
        $escuelas = Escuela::whereHas('materias')
            ->with(['materias' => function ($query) {
                $query->orderBy('caracter_obligatorio', 'desc')
                    ->orderBy('nombre', 'asc');
            }])->get();

        $materiasAprobadas = $alumno->materiasAprobadasRelacion()
            ->get()
            ->keyBy('materia_id');

        foreach ($escuelas as $escuela) {
            $totalObligatorias = $escuela->materias->where('caracter_obligatorio', true)->count();
            $aprobadasObligatorias = 0;

            foreach ($escuela->materias as $materia) {
                $resultado = $materiasAprobadas->get($materia->id);
                $materia->resultado = $resultado;

                if ($materia->caracter_obligatorio && $resultado && $resultado->aprobado) {
                    $aprobadasObligatorias++;
                }
            }

            $escuela->progreso = $totalObligatorias > 0
                ? round(($aprobadasObligatorias / $totalObligatorias) * 100)
                : 0;
            $escuela->total_obligatorias = $totalObligatorias;
            $escuela->aprobadas_obligatorias = $aprobadasObligatorias;
        }

        return view('contenido.paginas.escuelas.maestros.gestionar-alumno', [
            'maestro' => $maestro,
            'configuracion' => $configuracion,
            'alumno' => $alumno,
            'horarioAsignado' => $horarioAsignado,
            'estadoAcademicoAlumno' => $estadoAcademicoAlumno,
            'reportesAsistencia' => $reportesAsistencia,
            'totalAsistenciasCumplidas' => $totalAsistenciasCumplidas,
            'totalClasesReportadas' => $totalClasesReportadas,
            'itemsEvaluacion' => $itemsEvaluacion,
            'escuelas' => $escuelas,
        ]);
    }

    /**
     * Gestión de ítems de evaluación para un horario específico.
     */
    public function gestionarItems(Maestro $maestro, HorarioMateriaPeriodo $horarioAsignado): \Illuminate\Contracts\View\View
    {
        $usuario = Auth::user();

        // Validar que el maestro tenga asignado este horario (seguridad básica)
        // Opcional: Podríamos usar un Policy, pero por ahora validamos relación directa
        if (! $maestro->horariosMateriaPeriodo()->where('horario_materia_periodo_id', $horarioAsignado->id)->exists()) {
            // Permitir acceso también si es admin o tiene permiso especial, pero por ahora restringimos al maestro dueño
            if (! $usuario->hasRole('Admin') && ! $usuario->can('escuelas.tab_gestionar_items')) {
                abort(403, 'No tienes permiso para gestionar ítems de este horario.');
            }
        }

        $configuracion = Configuracion::find(1);

        $horarioAsignado->load([
            'materiaPeriodo.materia:id,nombre',
            'materiaPeriodo.periodo:id,nombre',
        ]);

        $nombreMateria = $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'Materia no definida';
        $nombrePeriodo = $horarioAsignado->materiaPeriodo?->periodo?->nombre ?? 'Periodo no definido';
        $infoClase = "Periodo: {$nombrePeriodo}";

        return view('contenido.paginas.escuelas.maestros.gestion-items', [
            'maestro' => $maestro,
            'configuracion' => $configuracion,
            'horarioAsignado' => $horarioAsignado,
            'nombreMateria' => $nombreMateria,
            'infoClase' => $infoClase,
            'rolActivo' => $usuario->roles()->wherePivot('activo', true)->first(),
        ]);
    }

    // En MaestroController.php

    public function calificacionMultiple(Maestro $maestro, HorarioMateriaPeriodo $horarioAsignado)
    {
        $configuracion = Configuracion::find(1);

        // Cargar datos necesarios para el encabezado de la vista principal (Blade)
        // El componente Livewire cargará su propia data.
        $horarioAsignado->load([
            'materiaPeriodo.materia:id,nombre',
            'materiaPeriodo.periodo:id,nombre',
            // 'maestros.user:id,name' // Para mostrar el nombre del maestro de la clase
        ]);

        $nombreMateria = $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'Materia no definida';
        $nombrePeriodo = $horarioAsignado->materiaPeriodo?->periodo?->nombre ?? 'Periodo no definido';
        // Asumimos que $maestro (el que está logueado o gestionando) es el relevante aquí.
        $nombreDocente = $maestro->user?->name ?? 'Docente no asignado';
        $infoClase = "Periodo: {$nombrePeriodo} | Docente: {$nombreDocente}";

        return view('contenido.paginas.escuelas.maestros.calificacion-multiple', [
            'maestro' => $maestro,
            'configuracion' => $configuracion,
            'horarioAsignado' => $horarioAsignado, // Pasar el modelo completo al componente Livewire
            'horarioAsignado' => $horarioAsignado, // Pasar el modelo completo al componente Livewire
            'nombreMateria' => $nombreMateria, // Para el título de la página Blade
            'infoClase' => $infoClase,         // Para el subtítulo de la página Blade
            'rolActivo' => Auth::user() ? Auth::user()->roles()->wherePivot('activo', true)->first() : null,
        ]);
    }

    public function calificacionGrilla(Maestro $maestro, HorarioMateriaPeriodo $horarioAsignado)
    {
        $configuracion = Configuracion::find(1);

        $horarioAsignado->load([
            'materiaPeriodo.materia:id,nombre',
            'materiaPeriodo.periodo:id,nombre',
        ]);

        $nombreMateria = $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'Materia no definida';
        $nombrePeriodo = $horarioAsignado->materiaPeriodo?->periodo?->nombre ?? 'Periodo no definido';
        $nombreDocente = $maestro->user?->name ?? 'Docente no asignado';
        $infoClase = "Periodo: {$nombrePeriodo} | Docente: {$nombreDocente}";

        return view('contenido.paginas.escuelas.maestros.calificacion-grilla', [
            'maestro' => $maestro,
            'configuracion' => $configuracion,
            'horarioAsignado' => $horarioAsignado,
            'nombreMateria' => $nombreMateria,
            'infoClase' => $infoClase,
            'rolActivo' => Auth::user() ? Auth::user()->roles()->wherePivot('activo', true)->first() : null,
        ]);
    }

    // ASEGÚRATE DE QUE LA FUNCIÓN RECIBA EL REPORTE Y LO PASE A LA VISTA
    public function editarReporte(Maestro $maestro, HorarioMateriaPeriodo $horarioAsignado, ReporteAsistenciaClase $reporte) // <--- RECIBE $reporte
    {
        $configuracion = Configuracion::find(1);

        $horarioAsignado->load([
            'materiaPeriodo.materia:id,nombre',
            'materiaPeriodo.periodo:id,nombre',
        ]);

        $nombreMateria = $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'Materia no definida';
        $nombrePeriodo = $horarioAsignado->materiaPeriodo?->periodo?->nombre ?? 'Periodo no definido';
        $nombreDocente = $maestro->user?->name ?? 'Docente no asignado';
        $infoClase = "Periodo: {$nombrePeriodo} | Docente: {$nombreDocente}";

        return view('contenido.paginas.escuelas.maestros.editar-reporte-asistencia', [
            'maestro' => $maestro,
            'configuracion' => $configuracion,
            'horarioAsignado' => $horarioAsignado,
            'reporte' => $reporte, // <--- PASA EL REPORTE A LA VISTA
            'nombreMateria' => $nombreMateria,
            'infoClase' => $infoClase,
        ]);
    }

    public function reporteAsistencia(Maestro $maestro, HorarioMateriaPeriodo $horarioAsignado)
    {
        $configuracionGeneral = Configuracion::find(1);

        $horarioAsignado->load([
            'materiaPeriodo.materia',
            'materiaPeriodo.periodo',
            'horarioBase',
            'reportesAsistencia',
        ]);

        $nombreDeLaMateria = $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'Materia no disponible';
        $nombreDelDocente = $maestro->user?->nombre(3) ?? ($maestro->user?->name ?? 'Docente no asignado');
        $informacionDeLaClase = 'Periodo: '.($horarioAsignado->materiaPeriodo?->periodo?->nombre ?? 'Periodo N/A').
            ' | Docente: '.$nombreDelDocente;

        $datosMateria = $horarioAsignado->materiaPeriodo?->materia;
        $periodo = $horarioAsignado->materiaPeriodo?->periodo;
        $fechaActual = Carbon::now();
        $usuarioActivo = auth()->user();

        $estadoFechas = $this->calcularEstadoFechasReporte($horarioAsignado, $periodo, $datosMateria);

        $inputFechaEsSoloLectura = false;
        $fechaPorDefectoParaInput = $fechaActual->format('Y-m-d');
        $aplicarFiltroPorDiaSemana = false;
        $diaSemanaAVisualizarEnCalendario = null;
        $botonNuevoReporteHabilitado = false;
        $limiteMinimoFechaPicker = null;
        $limiteMaximoFechaPicker = null;
        $esSuperAdmin = $usuarioActivo && $usuarioActivo->hasPermissionTo('escuelas.reportar_asistencia_cualquier_dia');

        $numeroDiaDeLaClase = null;
        if ($horarioAsignado->horarioBase && isset($horarioAsignado->horarioBase->dia)) {
            $numeroDiaDeLaClase = (int) $horarioAsignado->horarioBase->dia;
        }

        if ($esSuperAdmin) {
            $inputFechaEsSoloLectura = false;
            $botonNuevoReporteHabilitado = true;

            if ($datosMateria && $datosMateria->tiene_dia_limite == true && $numeroDiaDeLaClase !== null) {
                $aplicarFiltroPorDiaSemana = true;
                $diaSemanaAVisualizarEnCalendario = $numeroDiaDeLaClase;
            }
        } else {
            $inputFechaEsSoloLectura = false;

            // Basamos la habilidad de reportar en si tiene fechas pendientes
            if (! empty($estadoFechas['pendientes'])) {
                $botonNuevoReporteHabilitado = true;
                // La fecha por defecto será la primera pendiente (usualmente la única de la semana)
                $fechaPorDefectoParaInput = $estadoFechas['pendientes'][0];
            } else {
                $botonNuevoReporteHabilitado = false;
            }

            if ($periodo) {
                if ($periodo->fecha_inicio) {
                    $limiteMinimoFechaPicker = Carbon::parse($periodo->fecha_inicio)->format('Y-m-d');
                }
                if ($periodo->fecha_fin) {
                    $limiteMaximoFechaPicker = Carbon::parse($periodo->fecha_fin)->format('Y-m-d');
                }
            }
        }

        return view('contenido.paginas.escuelas.maestros.reporte-asistencia-alumnos', [
            'maestro' => $maestro,
            'materiaAEvaluar' => $datosMateria,
            'fechaDeHoy' => $fechaActual,
            'configuracionGeneral' => $configuracionGeneral,
            'horarioAsignado' => $horarioAsignado,
            'nombreMateria' => $nombreDeLaMateria,
            'informacionDeLaClase' => $informacionDeLaClase,
            'botonNuevoReporteHabilitado' => $botonNuevoReporteHabilitado,
            'inputFechaEsSoloLectura' => $inputFechaEsSoloLectura,
            'fechaPorDefectoParaInput' => $fechaPorDefectoParaInput,
            'aplicarFiltroPorDiaSemana' => $aplicarFiltroPorDiaSemana,
            'diaSemanaAVisualizarEnCalendario' => $diaSemanaAVisualizarEnCalendario,
            'limiteMinimoFechaPicker' => $limiteMinimoFechaPicker,
            'limiteMaximoFechaPicker' => $limiteMaximoFechaPicker,
            'esSuperAdmin' => $esSuperAdmin,
            'estadoFechas' => $estadoFechas,
            'rolActivo' => $usuarioActivo ? $usuarioActivo->roles()->wherePivot('activo', true)->first() : null,
        ]);
    }

    private function calcularEstadoFechasReporte(HorarioMateriaPeriodo $horarioAsignado, $periodo, $datosMateria)
    {
        $fechasCalculadas = [
            'realizados' => [],
            'omitidos' => [],
            'pendientes' => [],
            'futuros' => [],
            'fechasPermitidasFlatpickr' => [],
        ];

        if (! $periodo || ! $periodo->fecha_inicio || ! $periodo->fecha_fin) {
            return $fechasCalculadas;
        }

        $fechaInicioPeriodo = Carbon::parse($periodo->fecha_inicio)->startOfDay();
        $fechaFinPeriodo = Carbon::parse($periodo->fecha_fin)->startOfDay();
        $fechaActual = Carbon::now()->startOfDay();

        $numeroDiaDeLaClase = null;
        if ($horarioAsignado->horarioBase && isset($horarioAsignado->horarioBase->dia)) {
            $numeroDiaDeLaClase = (int) $horarioAsignado->horarioBase->dia; // 0-6
        }

        if ($numeroDiaDeLaClase === null) {
            return $fechasCalculadas;
        }

        $horarioAsignado->loadMissing('reportesAsistencia');
        $reportesExistentes = $horarioAsignado->reportesAsistencia->pluck('fecha_clase_reportada')->map(function ($fecha) {
            return Carbon::parse($fecha)->startOfDay()->format('Y-m-d');
        })->toArray();

        $limiteTotal = (isset($datosMateria->limite_reporte_asistencias) && $datosMateria->limite_reporte_asistencias > 0)
            ? $datosMateria->limite_reporte_asistencias : 999;

        $fechaIteracion = $fechaInicioPeriodo->copy();

        // Ajustar al primer día de clase dentro del periodo
        while ($fechaIteracion->dayOfWeek !== $numeroDiaDeLaClase && $fechaIteracion->lte($fechaFinPeriodo)) {
            $fechaIteracion->addDay();
        }

        $cantidadTeorica = 0;

        while ($fechaIteracion->lte($fechaFinPeriodo) && $cantidadTeorica < $limiteTotal) {
            $fechaStr = $fechaIteracion->format('Y-m-d');
            $cantidadTeorica++;

            if (in_array($fechaStr, $reportesExistentes)) {
                $fechasCalculadas['realizados'][] = $fechaStr;
            } else {
                $inicioSemanaIteracion = $fechaIteracion->copy()->startOfWeek(Carbon::SUNDAY);
                $inicioSemanaActual = $fechaActual->copy()->startOfWeek(Carbon::SUNDAY);

                if ($inicioSemanaIteracion->lt($inicioSemanaActual)) {
                    $fechasCalculadas['omitidos'][] = $fechaStr;
                } elseif ($inicioSemanaIteracion->eq($inicioSemanaActual)) {
                    $vencido = false;
                    // Lógica para determinar si el plazo de esta semana ya expiró
                    if ($datosMateria && $datosMateria->tiene_dia_limite && isset($datosMateria->dia_limite_reporte)) {
                        $fechaTope = $inicioSemanaActual->copy()->addDays((int) $datosMateria->dia_limite_reporte)->startOfDay();
                        if ($fechaActual->gt($fechaTope)) {
                            $vencido = true;
                        }
                    } elseif ($datosMateria && isset($datosMateria->dias_plazo_reporte)) {
                        $fechaTope = $fechaIteracion->copy()->addDays((int) $datosMateria->dias_plazo_reporte)->startOfDay();
                        if ($fechaActual->gt($fechaTope)) {
                            $vencido = true;
                        }
                    }

                    if ($vencido) {
                        $fechasCalculadas['omitidos'][] = $fechaStr;
                    } else {
                        $fechasCalculadas['pendientes'][] = $fechaStr;
                        $fechasCalculadas['fechasPermitidasFlatpickr'][] = $fechaStr;
                    }
                } else {
                    $fechasCalculadas['futuros'][] = $fechaStr;
                }
            }

            $fechaIteracion->addWeek();
        }

        return $fechasCalculadas;
    }

    private function verificarCondicionesParaCrearReporte(
        Request $peticionHttp,
        HorarioMateriaPeriodo $horarioAsignado,
        $usuarioActivo,
        $datosMateria,
        $periodo
    ): array {
        $fechaClaseReportadaStr = Carbon::parse($peticionHttp->input('fecha_clase_reportada'))->startOfDay()->format('Y-m-d');

        $estadoFechas = $this->calcularEstadoFechasReporte($horarioAsignado, $periodo, $datosMateria);

        // --- RESTRICCIÓN GENERAL: LÍMITE DE CANTIDAD TOTAL DE REPORTES POR MATERIA ---
        if ($datosMateria && isset($datosMateria->limite_reporte_asistencias) && $datosMateria->limite_reporte_asistencias > 0) {
            $horarioAsignado->loadMissing('reportesAsistencia');
            $cantidadReportesExistentesTotal = $horarioAsignado->reportesAsistencia->count();

            if ($cantidadReportesExistentesTotal >= $datosMateria->limite_reporte_asistencias) {
                return [
                    'puedeCrear' => false,
                    'mensajeError' => "Se ha alcanzado el límite total de {$datosMateria->limite_reporte_asistencias} reportes de asistencia permitidos para este curso.",
                ];
            }
        }

        if ($usuarioActivo && $usuarioActivo->hasPermissionTo('escuelas.reportar_asistencia_cualquier_dia')) {
            if ($datosMateria && isset($datosMateria->cantidad_limite_reportes_semana) && $datosMateria->cantidad_limite_reportes_semana > 0) {
                $inicioSemanaDelReporte = Carbon::parse($fechaClaseReportadaStr)->startOfWeek(Carbon::SUNDAY);
                $finSemanaDelReporte = Carbon::parse($fechaClaseReportadaStr)->endOfWeek(Carbon::SATURDAY);

                $horarioAsignado->loadMissing('reportesAsistencia');
                $cantidadReportesEnLaSemanaDelReporte = $horarioAsignado->reportesAsistencia()
                    ->where('fecha_clase_reportada', '>=', $inicioSemanaDelReporte->toDateString())
                    ->where('fecha_clase_reportada', '<=', $finSemanaDelReporte->toDateString())
                    ->count();

                if ($cantidadReportesEnLaSemanaDelReporte >= $datosMateria->cantidad_limite_reportes_semana) {
                    return [
                        'puedeCrear' => false,
                        'mensajeError' => "Se ha alcanzado el límite de {$datosMateria->cantidad_limite_reportes_semana} reportes permitidos para la semana seleccionada.",
                    ];
                }
            }

            return ['puedeCrear' => true, 'mensajeError' => null];
        } else {
            // Maestro Regular: Validamos estrictamente contra el estado calculado del periodo
            if (in_array($fechaClaseReportadaStr, $estadoFechas['omitidos'])) {
                return ['puedeCrear' => false, 'mensajeError' => 'El plazo para registrar esta asistencia ha vencido (fecha omitida del periodo).'];
            }

            if (in_array($fechaClaseReportadaStr, $estadoFechas['futuros'])) {
                return ['puedeCrear' => false, 'mensajeError' => 'No se pueden crear reportes para fechas futuras de clase.'];
            }

            if (! in_array($fechaClaseReportadaStr, $estadoFechas['fechasPermitidasFlatpickr'])) {
                return ['puedeCrear' => false, 'mensajeError' => 'La fecha seleccionada no es válida, está fuera del periodo o el plazo expiró.'];
            }

            return ['puedeCrear' => true, 'mensajeError' => null];
        }
    }

    public function guardarNuevoReporteAsistenciaClase(Request $request, Maestro $maestro, HorarioMateriaPeriodo $horarioAsignado)
    {
        // 1. Validación de Laravel (la que ya tenías)
        $datosValidados = $request->validate([
            'fecha_clase_reportada' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('reportes_asistencia_clase')->where(function ($query) use ($horarioAsignado) {
                    return $query->where('horario_materia_periodo_id', $horarioAsignado->id);
                }),
                // ... (tus otras reglas de validación de Laravel existentes) ...
                function ($attribute, $value, $fail) { /* ... validación de rango de período ... */
                },
                function ($attribute, $value, $fail) { /* ... validación de no semanas anteriores y día límite de materia (ISO) ... */
                },
            ],
            'observaciones_generales' => 'nullable|string|max:2000',
        ], [
            'fecha_clase_reportada.required' => 'La fecha de la clase es obligatoria.',
            'fecha_clase_reportada.unique' => 'Ya existe un reporte de asistencia para esta clase en la fecha seleccionada.',
            'observaciones_generales.max' => 'Las observaciones no pueden exceder los 2000 caracteres.',
        ]);

        // 2. Obtener datos para la validación personalizada
        $usuarioActivo = auth()->user();
        $horarioAsignado->loadMissing(['materiaPeriodo.materia', 'materiaPeriodo.periodo', 'reportesAsistencia']); // Asegúrate de cargar reportesAsistencia aquí también
        $datosMateria = $horarioAsignado->materiaPeriodo?->materia;
        $periodo = $horarioAsignado->materiaPeriodo?->periodo;

        // 3. Llamar a la nueva función de validación personalizada
        $resultadoValidacion = $this->verificarCondicionesParaCrearReporte($request, $horarioAsignado, $usuarioActivo, $datosMateria, $periodo);

        if ($resultadoValidacion['puedeCrear'] == false) {

            return redirect()->back()->with('danger', $resultadoValidacion['mensajeError']);
        }

        // 4. Si todo pasa, crear el reporte
        $reporte = new ReporteAsistenciaClase;
        $reporte->horario_materia_periodo_id = $horarioAsignado->id;
        $reporte->fecha_clase_reportada = $datosValidados['fecha_clase_reportada']; // Usar dato validado
        $reporte->observaciones_generales = $datosValidados['observaciones_generales']; // Usar dato validado
        $reporte->reportado_por_user_id = Auth::id();
        $reporte->estado_reporte = 'pendiente_detalle';
        $reporte->save();

        return redirect()->route('maestros.reporteAsistencia', ['maestro' => $maestro->id, 'horarioAsignado' => $horarioAsignado->id])
            ->with('success', 'Reporte de asistencia creado exitosamente.');
    }

    public function reportarAutoAsistencia(HorarioMateriaPeriodo $horarioAsignado, ReporteAsistenciaClase $reporte)
    {
        $puedeReportar = false;

        try {
            // Acceder a la escuela a través de la jerarquía de relaciones
            $escuela = $horarioAsignado->materiaPeriodo->materia->escuela;
            $horasDisponibilidad = (int) $escuela->horasDisponiblidadLinkAsistencia;

            // Asumimos que HorarioBase tiene hora_fin, ej: '16:00:00'
            // y ReporteAsistenciaClase tiene fecha_clase_reportada (Date)
            if ($horarioAsignado->horarioBase && $horarioAsignado->horarioBase->hora_fin) {
                $horaFinClaseStr = $horarioAsignado->horarioBase->hora_fin;
                $fechaClase = Carbon::parse($reporte->fecha_clase_reportada);

                // Combinar la fecha de la clase con la hora de finalización de la clase
                $fechaHoraFinClase = Carbon::createFromFormat(
                    'Y-m-d H:i:s', // Asume que hora_fin es H:i:s, ajusta si es necesario
                    $fechaClase->toDateString().' '.$horaFinClaseStr,
                    config('app.timezone') // Usar la zona horaria de la aplicación
                );

                // Calcular cuándo expira el link
                $linkExpiraEn = $fechaHoraFinClase->copy()->addHours($horasDisponibilidad);
                $ahora = Carbon::now(config('app.timezone'));

                // El link está activo si:
                // 1. Es el mismo día de la clase.
                // 2. La hora actual es igual o posterior a la hora de finalización de la clase.
                // 3. La hora actual es anterior a la hora de expiración del link.
                if ($ahora->isSameDay($fechaHoraFinClase) && $ahora->gte($fechaHoraFinClase) && $ahora->lt($linkExpiraEn)) {
                    $puedeReportar = true;
                }
            } else {
                Log::warning("HorarioBase o hora_fin no definidos para HorarioMateriaPeriodo ID: {$horarioAsignado->id}");
            }
        } catch (\Exception $e) {
            Log::error('Error al determinar si se puede reportar auto-asistencia: '.$e->getMessage(), [
                'horario_id' => $horarioAsignado->id,
                'reporte_id' => $reporte->id,
            ]);
            // Mantener $puedeReportar como false en caso de error
        }

        return view('contenido.paginas.escuelas.maestros.reportar-auto-asistencia', [
            'horarioAsignado' => $horarioAsignado,
            'reporte' => $reporte,
            'puedeReportar' => $puedeReportar,
        ]);
    }

    public function registrarAutoAsistenciaEstudiante(Request $request, HorarioMateriaPeriodo $horarioAsignado, ReporteAsistenciaClase $reporte)
    {
        // 1. Volver a verificar la validez del link (seguridad adicional)
        $escuela = $horarioAsignado->materiaPeriodo->materia->escuela;
        $horasDisponibilidad = (int) $escuela->horasDisponiblidadLinkAsistencia;
        $puedeReportarAhora = false;
        if ($horarioAsignado->horarioBase && $horarioAsignado->horarioBase->hora_fin) {
            $horaFinClaseStr = $horarioAsignado->horarioBase->hora_fin;
            $fechaClase = Carbon::parse($reporte->fecha_clase_reportada);
            $fechaHoraFinClase = Carbon::createFromFormat('Y-m-d H:i:s', $fechaClase->toDateString().' '.$horaFinClaseStr, config('app.timezone'));
            $linkExpiraEn = $fechaHoraFinClase->copy()->addHours($horasDisponibilidad);
            $ahora = Carbon::now(config('app.timezone'));
            if ($ahora->isSameDay($fechaHoraFinClase) && $ahora->gte($fechaHoraFinClase) && $ahora->lt($linkExpiraEn)) {
                $puedeReportarAhora = true;
            }
        }

        if (! $puedeReportarAhora) {
            return redirect()->back()->withErrors(['error' => 'El link de asistencia ha caducado o no está disponible.'])->withInput();
        }

        // 2. Validar la entrada
        $request->validate(
            ['buscar' => 'required|string|max:100'],
            ['buscar.required' => 'Por favor, ingresa tu número de documento o nombre.']
        );
        $terminoBusqueda = $request->input('buscar');

        // 3. Buscar al usuario (por identificación o nombre)
        // Sanitizar identificación (quitar puntos y espacios)
        $identificacionSaneada = str_replace(['.', ' '], '', $terminoBusqueda);

        $usuario = User::where(function ($query) use ($identificacionSaneada, $terminoBusqueda) {
            $query->whereRaw("REPLACE(REPLACE(identificacion, '.', ''), ' ', '') = ?", [$identificacionSaneada])
                ->orWhereRaw("CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido) ILIKE ?", ["%{$terminoBusqueda}%"])
                ->orWhereRaw("CONCAT_WS(' ', primer_nombre, primer_apellido) ILIKE ?", ["%{$terminoBusqueda}%"]);
        })->first();

        if (! $usuario) {
            return redirect()->back()->withErrors(['error' => 'Usuario no encontrado con los datos proporcionados.'])->withInput();
        }

        // 4. Verificar si el usuario está matriculado en este HorarioMateriaPeriodo
        $estaMatriculado = MatriculaHorarioMateriaPeriodo::where('user_id', $usuario->id)
            ->where('horario_materia_periodo_id', $horarioAsignado->id)
            ->exists();

        if (! $estaMatriculado) {
            return redirect()->back()->withErrors(['error' => 'No te encuentras matriculado en esta clase.'])->withInput();
        }

        // 5. Verificar si ya tiene una asistencia registrada en este reporte
        $asistenciaExistente = ReporteAsistenciaAlumnos::where('reporte_asistencia_clase_id', $reporte->id)
            ->where('user_id', $usuario->id)
            ->first();

        if ($asistenciaExistente) {
            // Si ya existe y asistió, mensaje de éxito. Si no asistió, se podría debatir si permitir cambiarlo.
            // Por ahora, si existe, simplemente informamos.
            $mensaje = $asistenciaExistente->asistio ? 'Tu asistencia ya había sido registrada como PRESENTE.' : 'Tu asistencia ya había sido registrada como AUSENTE por el maestro.';

            return redirect()->back()->withErrors(['success' => $mensaje.' No se realizaron cambios.'])->withInput();
        }

        // 6. Registrar la asistencia
        ReporteAsistenciaAlumnos::create([
            'reporte_asistencia_clase_id' => $reporte->id,
            'user_id' => $usuario->id,
            'asistio' => true, // El estudiante se marca como presente
            'motivo_inasistencia_id' => null,
            'observaciones_alumno' => 'Auto-reportado por el estudiante.',
            'auto_asistencia' => true,
        ]);

        return redirect()->back()->withErrors(['success' => '¡Asistencia registrada exitosamente!'])->withInput();
    }

    public function recursosAlumnos(HorarioMateriaPeriodo $horarioAsignado, Maestro $maestro)
    {

        return view('contenido.paginas.escuelas.maestros.recursos-alumno', [

            'horarioAsignado' => $horarioAsignado,
            'horarioAsignado' => $horarioAsignado,
            'maestro' => $maestro,
            'rolActivo' => Auth::user() ? Auth::user()->roles()->wherePivot('activo', true)->first() : null,
        ]);
    }

    /**
     * Activa el perfil de un maestro.
     */
    public function activar(Maestro $maestro)
    {
        try {
            $maestro->activo = true;
            $maestro->save();

            return redirect()->route('maestros.gestionar')
                ->with('mensaje_exito', "El maestro '{$maestro->user->nombre(3)}' ha sido activado.");
        } catch (\Exception $e) {
            Log::error("Error al activar maestro ID {$maestro->id}: ".$e->getMessage());

            return back()->with('mensaje_error', 'Ocurrió un error al activar el maestro.');
        }
    }

    /**
     * Desactiva el perfil de un maestro.
     */
    public function desactivar(Maestro $maestro)
    {
        try {
            $maestro->activo = false;
            $maestro->save();

            return redirect()->route('maestros.gestionar')
                ->with('mensaje_exito', "El maestro '{$maestro->user->nombre(3)}' ha sido desactivado.");
        } catch (\Exception $e) {
            Log::error("Error al desactivar maestro ID {$maestro->id}: ".$e->getMessage());

            return back()->with('mensaje_error', 'Ocurrió un error al desactivar el maestro.');
        }
    }

    /**
     * Sube un archivo de recurso mediante fetch/Alpine.js
     */
    public function uploadArchivoRecurso(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,docx,xlsx,pptx,jpg,jpeg,png|max:10240',
            'horario_id' => 'required|integer',
        ]);

        $horarioId = $request->input('horario_id');
        $archivo = $request->file('archivo');
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = "recurso-{$horarioId}-".uniqid().".{$extension}";

        $directorio = "archivos/escuelas/recursos-horario/horario-{$horarioId}";

        try {
            // Aseguramos que cada carpeta intermedia tenga permisos 0755
            $currentPath = storage_path('app/public');
            $relativeParts = explode('/', trim($directorio, '/'));
            foreach ($relativeParts as $part) {
                if (empty($part)) {
                    continue;
                }
                $currentPath .= '/'.$part;
                if (! file_exists($currentPath)) {
                    @mkdir($currentPath, 0755, true);
                }
                @chmod($currentPath, 0755);
            }

            // Almacenamos el archivo en el disco 'public'
            $archivo->storeAs($directorio, $nombreArchivo, 'public');

            // Aseguramos que el archivo recién creado tenga permisos 0755
            $filePath = storage_path("app/public/{$directorio}/{$nombreArchivo}");
            @chmod($filePath, 0755);

            return response()->json([
                'success' => true,
                'nombre' => $nombreArchivo,
                'ruta_relativa' => "{$directorio}/{$nombreArchivo}",
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error uploading resource file: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el archivo.',
            ], 500);
        }
    }
}
