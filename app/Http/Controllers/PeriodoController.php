<?php

namespace App\Http\Controllers;


// use Illuminate\Support\Facades\Storage; // Comentado si no se usa
use Illuminate\View\View;
// use Illuminate\Validation\Rule; // Comentado si no se usa aquí
use App\Helpers\Helpers; // Asumiendo que existe y se usa
use Illuminate\Support\Facades\DB;
use stdClass; // Asegúrate que stdClass esté disponible o importado
use Illuminate\Support\Facades\Auth; // Comentado si no se usa
use Illuminate\Support\Facades\Session; // Comentado si no se usa
use Illuminate\Support\Facades\Log; // Para registrar errores
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request; // Importante para recibir los filtros
use Illuminate\Support\Facades\Http;
use Illuminate\Http\RedirectResponse;
use App\Models\Configuracion;
use App\Models\SistemaCalificacion;
use App\Models\Periodo;
use App\Models\MateriaPeriodo;
use App\Models\MateriaAprobadaUsuario;
use App\Models\Sede;
use App\Models\Escuela;
use App\Models\CorteEscuela; // Importar CorteEscuela
use App\Models\CortePeriodo; // Importar CortePeriodo
use App\Jobs\FinalizarPeriodoJob;
use App\Models\MatriculaHorarioMateriaPeriodo; // <-- Añadir este import
use App\Models\TrasladoMatriculaLog;      // <-- Añadir este import
use App\Exports\HorariosPeriodoExport; // <-- Importa la nueva clase
use App\Exports\InformeFinalPeriodoExport;
use Maatwebsite\Excel\Facades\Excel;   // <-- Importa el Facade de Excel
use App\Exports\InformeFinalMateriaExport;




class PeriodoController extends Controller
{
    /**
     * Muestra el formulario para crear un nuevo periodo.
     *
     * @return \Illuminate\View\View
     */
    public function crear(): View
    {
        $configuracion = Configuracion::find(1);
        $escuelas = Escuela::with('cortesEscuela')->orderBy('nombre')->get(); // Cargar escuelas CON sus cortes
        $sedes = Sede::orderBy('nombre')->get();
        $sistemasCalifiacion = SistemaCalificacion::orderBy('nombre')->get();

        // Agrupar los cortes por escuela_id para pasarlos a la vista
        $cortesPorEscuela = $escuelas->mapWithKeys(function ($escuela) {
            // Ordenar los cortes de cada escuela por el campo 'orden'
            return [$escuela->id => $escuela->cortesEscuela->sortBy('orden')];
        });


        return view('contenido.paginas.escuelas.periodos.crear-periodo', [
            'configuracion' => $configuracion,
            'escuelas' => $escuelas, // Lista de escuelas para el select
            'sedes' => $sedes,
            'sistemasCalifiacion' => $sistemasCalifiacion,
            'cortesPorEscuela' => $cortesPorEscuela, // Pasar los cortes agrupados
        ]);
    }

    /**
     * Guarda un nuevo periodo y sus cortes asociados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function guardar(Request $request)
    {
        // 1. Validación de los datos (Paso 1 y Paso 2)
        $validatedData = $request->validate([
            // Paso 1
            'nombre' => 'required|string|max:200',
            'escuelaId' => 'required|integer',
            'sistema_calificacion_id' => 'required|integer',
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
            'fecha_limite_maestro' => 'nullable|date_format:Y-m-d',
            'sedes' => 'required|array|min:1',
            'sedes.*' => 'required|integer',

            // Paso 2 (Cortes)
            'cortes' => 'required|array',
            'cortes.*.corte_escuela_id' => 'required|integer',
            'cortes.*.fecha_inicio' => 'required|date_format:Y-m-d',
            'cortes.*.fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:cortes.*.fecha_inicio',
            'cortes.*.porcentaje' => 'required|numeric|min:0|max:100',
            'cortes.*' => [
                function ($attribute, $value, $fail) use ($request) {
                    static $sumaCalculada = false;
                    static $sumaPorcentajes = 0;
                    if (!$sumaCalculada && is_array($request->input('cortes'))) {
                        $sumaPorcentajes = collect($request->input('cortes'))->sum('porcentaje');
                        if (abs($sumaPorcentajes - 100) > 0.01) {
                            $fail("La suma de los porcentajes de los cortes debe ser exactamente 100%. Suma actual: {$sumaPorcentajes}%.");
                        }
                        $sumaCalculada = true;
                    }
                }
            ],
        ], [
            // Mensajes de error personalizados
            'nombre.required' => 'El nombre del periodo es obligatorio.',
            'escuelaId.*' => 'La escuela seleccionada no es válida.',
            'sistema_calificacion_id.*' => 'El sistema de calificación seleccionado no es válido.',
            'fecha_inicio.*' => 'La fecha de inicio no es válida (Formato: AAAA-MM-DD).',
            'fecha_fin.required' => 'La fecha de finalización es obligatoria.',
            'fecha_fin.date_format' => 'El formato de la fecha de finalización no es válido (AAAA-MM-DD).',
            'fecha_fin.after_or_equal' => 'La fecha de finalización debe ser igual o posterior a la fecha de inicio.',
            'fecha_limite_maestro.date_format' => 'El formato de la fecha límite no es válido (AAAA-MM-DD).',
            'fecha_limite_maestro.before_or_equal' => 'La fecha límite debe ser igual o anterior a la fecha de finalización del periodo.',
            'sedes.required' => 'Debes seleccionar al menos una sede.',
            'sedes.*' => 'Una de las sedes seleccionadas no es válida.',
            'cortes.required' => 'La información de los cortes es necesaria.',
            'cortes.*.corte_escuela_id.*' => 'El ID del corte base no es válido.',
            'cortes.*.fecha_inicio.required' => 'La fecha de inicio para cada corte es obligatoria.',
            'cortes.*.fecha_inicio.date_format' => 'Formato inválido para fecha de inicio de corte (AAAA-MM-DD).',
            'cortes.*.fecha_fin.required' => 'La fecha de fin para cada corte es obligatoria.',
            'cortes.*.fecha_fin.date_format' => 'Formato inválido para fecha de fin de corte (AAAA-MM-DD).',
            'cortes.*.fecha_fin.after_or_equal' => 'La fecha de fin del corte debe ser igual o posterior a su fecha de inicio.',
            'cortes.*.porcentaje.required' => 'El porcentaje para cada corte es obligatorio.',
            'cortes.*.porcentaje.numeric' => 'El porcentaje debe ser un número.',
            'cortes.*.porcentaje.min' => 'El porcentaje no puede ser negativo.',
            'cortes.*.porcentaje.max' => 'El porcentaje no puede ser mayor a 100.',
        ]);

        // Iniciar transacción para asegurar la atomicidad
        DB::beginTransaction();

        try {
            // 2. Crear la instancia de Periodo
            $periodo = new Periodo();
            $periodo->nombre = $validatedData['nombre'];
            $periodo->escuela_id = $validatedData['escuelaId'];
            $periodo->sistema_calificaciones_id = $validatedData['sistema_calificacion_id'];
            $periodo->fecha_inicio = $validatedData['fecha_inicio'];
            $periodo->fecha_fin = $validatedData['fecha_fin'];
            $periodo->fecha_maxima_entrega_notas = $validatedData['fecha_limite_maestro'] ?? null;
            $periodo->estado = true;
            $periodo->save(); // Guardar el periodo

            // 3. Crear los CortesPeriodo asociados
            foreach ($validatedData['cortes'] as $corteData) {
                $cortePeriodo = new CortePeriodo();
                $cortePeriodo->periodo_id = $periodo->id;
                $cortePeriodo->corte_escuela_id = $corteData['corte_escuela_id'];
                $cortePeriodo->fecha_inicio = $corteData['fecha_inicio'];
                $cortePeriodo->fecha_fin = $corteData['fecha_fin'];
                $cortePeriodo->porcentaje = $corteData['porcentaje'];
                $cortePeriodo->cerrado = false;
                $cortePeriodo->save();
            }

            // 4. Asociar las Sedes al Periodo (relación muchos a muchos)
            // Verifica que el array 'sedes' exista y no esté vacío antes de sincronizar
            if (!empty($validatedData['sedes'])) {
                // sync() se encarga de añadir/quitar registros en la tabla pívot
                // para que coincidan exactamente con los IDs proporcionados.
                $periodo->sedes()->sync($validatedData['sedes']);
            } else {
                // Opcional: Si no se enviaron sedes pero la relación existe,
                // podrías querer desasociar todas las sedes existentes (si aplicara en una actualización)
                // $periodo->sedes()->sync([]); // Esto eliminaría todas las asociaciones
            }


            DB::commit(); // Confirmar transacción

            // 5. Redireccionar con mensaje de éxito

            return redirect()->route('periodo.actualizar', $periodo->id)
                ->with('success', '¡Periodo, cortes y sedes creados exitosamente!');
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir transacción en caso de error
            Log::error("Error al guardar periodo: " . $e->getMessage());

            return redirect()->route('periodo.crear')
                ->withErrors(['error_general' => 'Ocurrió un error inesperado al guardar el periodo: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Muestra la vista para actualizar un periodo existente.
     *
     * @param  \App\Models\Periodo  $periodo
     * @return \Illuminate\View\View
     */
    public function actualizar(Periodo $periodo): View
    {
        $configuracion = Configuracion::find(1);
        $sedes = Sede::orderBy('nombre')->get();
        $escuelas = Escuela::orderBy('nombre')->get();
        $sistemasCalifiacion = SistemaCalificacion::orderBy('nombre')->get();
        $sedesPeriodo = $periodo->sedes()->pluck('sede_id')->toArray();
        $periodo->load(['escuela', 'sistemaCalificaciones', 'sedes', 'cortesPeriodo.corteEscuela']);
        $escuela = $periodo->escuela()->first();

        // --- INICIO: LÓGICA COMPLETA PARA ESTADÍSTICAS ---

        // 1. Total de Matrículas en el periodo
        $totalMatriculas = $periodo->matriculas()->count();

        // 2. Total de Aprobados (desde la tabla de resultados finales)
        $totalAprobadas = MateriaAprobadaUsuario::where('periodo_id', $periodo->id)
            ->where('aprobado', true)
            ->count();

        // 3. Total de No Aprobados (desde la tabla de resultados finales)
        $totalNoAprobadas = MateriaAprobadaUsuario::where('periodo_id', $periodo->id)
            ->where('aprobado', false)
            ->count();

        // 4. Total de Bloqueadas (desde la matrícula)
        $totalBloqueadas = $periodo->matriculas()->where('bloqueado', true)->count();

        // 5. Total de Traslados (buscando en el log a través de las matrículas del periodo)
        $totalTraslados = TrasladoMatriculaLog::whereHas('matricula', function ($query) use ($periodo) {
            $query->where('periodo_id', $periodo->id);
        })->count();

        // Agrupamos las estadísticas en un array para pasarlo a la vista
        $estadisticas = [
            'totalMatriculas'   => $totalMatriculas,
            'totalAprobadas'    => $totalAprobadas,
            'totalNoAprobadas'  => $totalNoAprobadas,
            'totalBloqueadas'   => $totalBloqueadas,
            'totalTraslados'    => $totalTraslados,
        ];

        // --- FIN: LÓGICA PARA ESTADÍSTICAS ---

        return view('contenido.paginas.escuelas.periodos.actualizar-periodo', [
            'configuracion' => $configuracion,
            'periodo' => $periodo,
            'sedes' => $sedes,
            'escuelas' => $escuelas,
            'sistemasCalifiacion' => $sistemasCalifiacion,
            'sedesPeriodo' => $sedesPeriodo,
            'escuela' => $escuela,
            'estadisticas' => $estadisticas,
        ]);
    }

    public function exportarHorarios(Periodo $periodo)
    {
        $fileName = 'horarios-' . \Str::slug($periodo->nombre) . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new HorariosPeriodoExport($periodo), $fileName);
    }

    public function informeFinal(Periodo $periodo)
    {
        $fileName = 'informe-final-' . \Str::slug($periodo->nombre) . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new InformeFinalPeriodoExport($periodo), $fileName);
    }


    public function eliminar($periodo)
    {
        return 'eliminar';
    }

    public function procesarActualizacion(Request $request, Periodo $periodo)
    {
        // 1. Validación de los datos (similar a guardar, pero puede tener reglas diferentes para 'unique' si es necesario)

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:200',

            'sistema_calificacion_id' => 'required|integer|exists:sistema_calificaciones,id',
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
            'fecha_limite_maestro' => 'nullable|date_format:Y-m-d',
            'sedes' => 'required|array', // 'sometimes' porque podría no enviarse si no hay cambios o se quieren desasociar todas


            // Validación para los cortes (si también permites actualizarlos aquí)
            // Si los cortes se gestionan en otra interfaz, puedes omitir esta parte.
            // Por ahora, asumiremos que los cortes del periodo NO se actualizan desde este formulario.
            // Si necesitas actualizar cortes, la lógica sería más compleja (encontrar existentes, crear nuevos, eliminar viejos).
        ], [
            // Mensajes de error personalizados (puedes reutilizar los del método guardar)
            'nombre.required' => 'El nombre del periodo es obligatorio.',

            // ...otros mensajes...

        ]);

        DB::beginTransaction();
        try {
            // 2. Actualizar los datos del Periodo
            $periodo->nombre = $validatedData['nombre'];


            $periodo->sistema_calificaciones_id = $validatedData['sistema_calificacion_id'];
            $periodo->fecha_inicio = $validatedData['fecha_inicio'];
            $periodo->fecha_fin = $validatedData['fecha_fin'];
            $periodo->fecha_maxima_entrega_notas = $validatedData['fecha_limite_maestro'] ?? null;
            // $periodo->estado = $request->input('estado', $periodo->estado); // Si tienes un input para estado
            $periodo->save(); // Guardar los cambios del periodo

            // 3. Actualizar las Sedes asociadas
            // Si 'sedes' no está en el request (porque el select estaba vacío), sync([]) desasociará todas.
            // Si 'sedes' es un array vacío, también desasociará todas.
            // Si 'sedes' tiene IDs, sincronizará esas.
            $sedesParaSincronizar = $request->input('sedes', []); // Default a array vacío si no se envía
            $periodo->sedes()->sync($sedesParaSincronizar);


            // NOTA: La actualización de CortePeriodo es más compleja.
            // Si necesitas actualizar los cortes (fechas, porcentajes),
            // requeriría iterar sobre los cortes enviados, compararlos con los existentes,
            // actualizar los que coincidan, crear nuevos si los hay, y opcionalmente eliminar los que no se envíen.
            // Por simplicidad, esta función de actualización ahora se enfoca en el Periodo y sus Sedes.
            // La gestión de los cortes del periodo se podría hacer en su propia interfaz (la que tienes en `periodo.cortes`).

            DB::commit();

            return redirect()->route('periodo.actualizar', $periodo->id)
                ->with('success', '¡Periodo actualizado exitosamente!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar periodo ID {$periodo->id}: " . $e->getMessage());

            return redirect()->route('periodo.actualizar', $periodo->id)
                ->withErrors(['error_general' => 'Ocurrió un error inesperado al actualizar el periodo: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function finalizar(Periodo $periodo): RedirectResponse
    {
        // Lógica de validación: ¿Ya se finalizó? ¿Está inactivo?
        if ($periodo->finalizado) { // Suponiendo que añadimos un campo 'finalizado' al modelo Periodo
            return redirect()->route('periodo.gestionar')->with('error', 'Este periodo ya ha sido finalizado anteriormente.');
        }

        try {
            $periodo->estado = false;
            $periodo->save();
            // Despachamos el Job a la cola
            FinalizarPeriodoJob::dispatch($periodo);

            // Devolvemos al usuario inmediatamente con un mensaje de éxito
            return redirect()->route('periodo.gestionar')->with('success', 'El proceso de finalización para el periodo "' . $periodo->nombre . '" ha comenzado. Se te notificará cuando termine.');
        } catch (\Exception $e) {
            Log::error("Error al despachar el Job FinalizarPeriodoJob para el periodo ID {$periodo->id}: " . $e->getMessage());
            return redirect()->route('periodo.gestionar')->with('error', 'Hubo un error al intentar iniciar el proceso de finalización.');
        }
    }

    /**
     * Muestra la lista de periodos gestionables.
     *
     * @return \Illuminate\View\View
     */
    public function gestionar(Request $request)
    {
        $configuracion = Configuracion::find(1);
        $sedes = Sede::orderBy('nombre')->get();
        $escuelas = Escuela::orderBy('nombre')->get();
        $rolActivo = auth()->user()->roles()->where('activo', true)->first();

        // Generar array de años (actual + 5 anteriores)
        $anioActual = date('Y');
        $anios = [];
        for ($i = 0; $i <= 5; $i++) {
            $anios[] = $anioActual - $i;
        }

        // Inicialización para filtros y tags
        $tagsBusqueda = [];
        $banderaFiltros = 0;

        // Obtener valores de los filtros del request
        $filtroNombre = $request->input('filtro_nombre');
        $filtroSedeId = $request->input('sedeFiltro');
        $filtroEstado = $request->input('estado');
        $filtroEscuelaId = $request->input('escuelaId');
        $filtroAnio = $request->input('anioFiltro');

        // Construcción de la consulta base con Eager Loading
        $queryPeriodos = Periodo::query()->with(['escuela', 'sistemaCalificaciones', 'sedes', 'matriculas']);

        // --- INICIO DE LÓGICA DE FILTRADO COMPLETA ---

        // Aplicar filtro por Nombre
        if ($filtroNombre) {
            $queryPeriodos->where('nombre', 'ilike', '%' . $filtroNombre . '%');
            $tag = new stdClass();
            $tag->label = 'Nombre: ' . $filtroNombre;
            $tag->field = 'filtro_nombre';
            $tag->value = $filtroNombre;
            $tagsBusqueda[] = $tag;
            $banderaFiltros = 1;
        }

        // Aplicar filtro por Escuela
        if ($filtroEscuelaId) {
            $queryPeriodos->where('escuela_id', $filtroEscuelaId);
            $escuelaSeleccionada = $escuelas->find($filtroEscuelaId);
            $tag = new stdClass();
            $tag->label = 'Escuela: ' . $escuelaSeleccionada->nombre;
            $tag->field = 'escuelaId';
            $tag->value = $filtroEscuelaId;
            $tagsBusqueda[] = $tag;
            $banderaFiltros = 1;
        }

        // Aplicar filtro por Sede (usando whereHas por la relación Many-to-Many)
        if ($filtroSedeId) {
            $queryPeriodos->whereHas('sedes', function ($query) use ($filtroSedeId) {
                $query->where('sedes.id', $filtroSedeId);
            });
            $sedeSeleccionada = $sedes->find($filtroSedeId);
            $tag = new stdClass();
            $tag->label = 'Sede: ' . $sedeSeleccionada->nombre;
            $tag->field = 'sedeFiltro';
            $tag->value = $filtroSedeId;
            $tagsBusqueda[] = $tag;
            $banderaFiltros = 1;
        }

        // Aplicar filtro por Estado (Activo/Inactivo)
        if ($request->filled('estado')) { // filled() verifica que no sea nulo ni vacío
            $queryPeriodos->where('estado', $filtroEstado);
            $tag = new stdClass();
            $tag->label = 'Estado: ' . ($filtroEstado == '1' ? 'Activo' : 'Inactivo');
            $tag->field = 'estado';
            $tag->value = $filtroEstado;
            $tagsBusqueda[] = $tag;
            $banderaFiltros = 1;
        }

        // Aplicar filtro por Año de Inicio
        if ($filtroAnio) {
            $queryPeriodos->whereYear('fecha_inicio', $filtroAnio);
            $tag = new stdClass();
            $tag->label = 'Año: ' . $filtroAnio;
            $tag->field = 'anioFiltro';
            $tag->value = $filtroAnio;
            $tagsBusqueda[] = $tag;
            $banderaFiltros = 1;
        }

        // --- FIN DE LÓGICA DE FILTRADO ---

        // Ordenar y paginar
        $periodos = $queryPeriodos->orderBy('fecha_inicio', 'desc')->paginate(15);

        return view('contenido.paginas.escuelas.periodos.gestionar-periodos', [
            'configuracion' => $configuracion,
            'periodos' => $periodos,
            'sedes' => $sedes,
            'escuelas' => $escuelas,
            'anios' => $anios,
            'tagsBusqueda' => $tagsBusqueda,
            'banderaFiltros' => $banderaFiltros,
            'filtroNombreActual' => $filtroNombre,
            'filtroSedeIdActual' => $filtroSedeId,
            'filtroEstadoActual' => $filtroEstado,
            'filtroEscuelaIdActual' => $filtroEscuelaId,
            'filtroAnioActual' => $filtroAnio,
            'rolActivo' => $rolActivo
        ]);
    }

    public function alumnos(Periodo $periodo)
    {

        $configuracion = Configuracion::find(1);
        return view('contenido.paginas.escuelas.periodos.listado-alumnos-periodo', [
            'configuracion' => $configuracion,
            'periodo' => $periodo,


        ]);
    }

    public function activar(Periodo $periodo)
    {
        $periodo->estado = true;
        $periodo->save();
        return redirect()->route('periodo.gestionar')->with('success', 'Se habilito correctamente el periodo.');
    }


    public function cortes(Periodo $periodo): View
    {
        $configuracion = Configuracion::find(1);
        $sedes = Sede::orderBy('nombre')->get();

        return view('contenido.paginas.escuelas.periodos.cortes-periodo', [
            'configuracion' => $configuracion,
            'periodo' => $periodo,
            'sedes' => $sedes,

        ]);
    }

    public function materias(Periodo $periodo): View
    {
        $configuracion = Configuracion::find(1);
        $sedes = Sede::orderBy('nombre')->get();

        return view('contenido.paginas.escuelas.periodos.materias-periodo', [
            'configuracion' => $configuracion,
            'periodo' => $periodo,
            'sedes' => $sedes,

        ]);
    }

    public function horarios(MateriaPeriodo $materiaPeriodo)
    {
        $configuracion = Configuracion::find(1);


        return view('contenido.paginas.escuelas.periodos.gestionar-horarios-materia-periodo', [
            'configuracion' => $configuracion,
            'materiaPeriodo' => $materiaPeriodo,


        ]);
    }

    public function exportarInformeFinalMateria(MateriaPeriodo $materiaPeriodo)
    {
        // 1. Verificación de seguridad: Solo permite la descarga si la materia está finalizada.
        if (!$materiaPeriodo->finalizado) {
            return redirect()->back()->with('mensaje_error', 'No se puede generar el informe de una materia que no ha sido finalizada.');
        }

        // 2. Preparamos un nombre de archivo dinámico y limpio.
        $nombreMateria = preg_replace('/[^A-Za-z0-9\-]/', '_', $materiaPeriodo->materia->nombre);
        $nombrePeriodo = preg_replace('/[^A-Za-z0-9\-]/', '_', $materiaPeriodo->periodo->nombre);
        $fileName = "Informe_Final_{$nombreMateria}_{$nombrePeriodo}.xlsx";

        // 3. Usamos Laravel Excel para iniciar la descarga.
        return Excel::download(new InformeFinalMateriaExport($materiaPeriodo), $fileName);
    }
}
