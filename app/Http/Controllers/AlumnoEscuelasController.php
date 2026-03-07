<?php

namespace App\Http\Controllers;

use stdClass;

use App\Models\Configuracion;
use App\Models\Escuela;
use App\Models\CorteEscuela; // Importar el modelo CorteEscuela
use App\Models\User;
// Quité Usuario si no se usa directamente aquí, User parece ser el modelo correcto
// use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Importar DB para transacciones
use Illuminate\Support\Facades\Log; // Importar Log para errores
use Illuminate\Support\Facades\Storage; // Importar Storage si se usa en update
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

use Illuminate\View\View;
use App\Models\Maestro; // <-- Asegúrate de importar el modelo Maestro
use App\Models\RecursoAlumnoHorario;
use App\Models\Calificaciones;
use App\Models\HorarioMateriaPeriodo;
use App\Models\CortePeriodo;
use App\Models\ReporteAsistenciaAlumnos;
use App\Models\AlumnoRespuestaItem;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\MateriaAprobadaUsuario;
use App\Models\Matricula;
use App\Models\BannerEscuela;

class AlumnoEscuelasController extends Controller
{
    /**
     * Muestra el perfil de una materia para el alumno con datos de ejemplo.
     */
    public function perfilMateria(HorarioMateriaPeriodo $horario)
    {
        $alumno = Auth::user();

        // --- Verificación de Seguridad ---
        $estaMatriculado = $horario->alumnosMatriculados()->where('user_id', $alumno->id)->exists();
        if (!$estaMatriculado) {
            abort(403, 'No tienes permiso para ver esta materia.');
        }

        // --- BLOQUE 1: DATOS GENERALES DE LA MATERIA ---
        $materiaData = new \stdClass();
        $materiaData->nombre = $horario->materiaPeriodo->materia->nombre;

        $materiaData->maestros = $horario->maestros->map(fn($maestro) => (object)[
            'nombre' => $maestro->user->nombre(3),
            'imagen' => $maestro->user->foto,
            'iniciales' => $maestro->user->inicialesNombre()
        ]);

        $materiaData->horario = sprintf(
            '%s %s - %s | %s',
            $horario->horarioBase->dia_semana,
            $horario->horarioBase->hora_inicio_formato,
            $horario->horarioBase->hora_fin_formato,
            $horario->horarioBase->aula->nombre
        );

        // --- BLOQUE 2: DATOS DE ASISTENCIA ---
        $asistenciaData = new \stdClass();
        $asistenciaData->total_clases = $horario->reportesAsistencia()->count();
        $asistenciaData->asistencias_alumno = ReporteAsistenciaAlumnos::where('user_id', $alumno->id)
            ->whereIn('reporte_asistencia_clase_id', $horario->reportesAsistencia->pluck('id'))
            ->where('asistio', true)->count();
        $asistenciaData->porcentaje = $asistenciaData->total_clases > 0
            ? round(($asistenciaData->asistencias_alumno / $asistenciaData->total_clases) * 100) : 0;

        $asistenciaData->historial = ReporteAsistenciaAlumnos::where('user_id', $alumno->id)
            ->whereIn('reporte_asistencia_clase_id', $horario->reportesAsistencia->pluck('id'))
            ->with('reporteClase', 'motivoInasistencia')->get()
            ->map(fn($registro) => (object)[
                'fecha' => $registro->reporteClase->fecha_clase_reportada->format('Y-m-d'),
                'estado' => $registro->asistio ? 'Asistió' : 'Inasistencia',
                'motivo' => $registro->motivoInasistencia?->nombre ?? null
            ]);

        // --- BLOQUE 3: DATOS DE CALIFICACIONES POR CORTE ---
        $periodo = $horario->materiaPeriodo->periodo;
        $itemsHorario = ItemCorteMateriaPeriodo::where('horario_materia_periodo_id', $horario->id)->get();


        // --- CORRECCIÓN 1: Usamos 'itemInstancias' en la carga ansiosa (with) ---
        $cortes = CortePeriodo::where('periodo_id', $periodo->id)
            ->with(['itemInstancias' => fn($query) => $query->where('horario_materia_periodo_id', $horario->id)->orderBy('orden')])
            ->get();



        // --- CORRECCIÓN 2: Usamos 'itemInstancias' para obtener los IDs de los ítems ---
        $respuestasAlumno = AlumnoRespuestaItem::where('user_id', $alumno->id)
            ->whereIn('item_corte_materia_periodo_id', $cortes->pluck('itemInstancias')->flatten()->pluck('id'))
            ->get()->keyBy('item_corte_materia_periodo_id');

        $materiaData->promedio_actual = $this->calcularPromedioActual($respuestasAlumno, $cortes);

        $cortes->each(function ($corte) use ($respuestasAlumno) {
            $corte->nombre_completo = "{$corte->corteEscuela->nombre} ({$corte->porcentaje}%)";
            // --- CORRECCIÓN 3: Usamos 'itemInstancias' para iterar sobre los ítems de cada corte ---
            $corte->itemInstancias->each(function ($item) use ($respuestasAlumno) {
                $respuesta = $respuestasAlumno->get($item->id);
                $item->fecha_entrega = $item->fecha_fin?->format('Y-m-d');
                $item->porcentaje_str = "{$item->porcentaje}%";
                $item->nota = $respuesta?->nota_obtenida;
                $item->entregado = isset($respuesta);
                $item->respuesta_alumno = $respuesta?->respuesta_alumno;
                $item->feedback_maestro = $respuesta?->observaciones_maestro;
                $item->estado = 'Pendiente';
                if (isset($respuesta)) $item->estado = 'Entregado';
                if (isset($respuesta) && $respuesta->nota_obtenida !== null) $item->estado = 'Calificado';
            });
        });

        // Creamos la tabla de resumen para la primera pestaña
        $materiaData->items_tabla_resumen = [];
        foreach ($cortes as $corte) {
            // --- CORRECCIÓN 4: Usamos 'itemInstancias' para construir el resumen ---
            foreach ($corte->itemInstancias as $item) {
                $materiaData->items_tabla_resumen[] = (object)[
                    'nombre' => $item->nombre,
                    'corte'  => $corte->nombre_completo,
                    'nota'   => $item->nota,
                ];
            }
        }

        // ==========================================================
        // === INICIO DE LA SECCIÓN AÑADIDA                        ===
        // ==========================================================

        // Buscamos en la tabla de calificaciones cuál es la nota mínima para tener el estado "aprobado"
        // para el sistema de calificación de este periodo.
        $notaMinimaAprobacion = Calificaciones::where('sistema_calificacion_id', $periodo->sistema_calificaciones_id)
            ->where('aprobado', true)
            ->min('nota_minima');

        // Si no se encuentra una regla, usamos 3.0 como valor por defecto seguro.
        if (is_null($notaMinimaAprobacion)) {
            $notaMinimaAprobacion = 3.0;
        }

        // ==========================================================
        // === FIN DE LA SECCIÓN AÑADIDA                           ===
        // ==========================================================


        // --- BLOQUE 4: DATOS DE RECURSOS (sin cambios, se mantiene de ejemplo) ---
        $recursos = RecursoAlumnoHorario::where('horario_materia_periodo_id', $horario->id)
            ->where('visible', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // --- ELIMINADO ---
        // Se eliminó el bloque de código duplicado y erróneo que causaba el error "Attempt to assign property on null".
        $configuracion = Configuracion::find(1);
        return view('contenido.paginas.escuelas.alumnos.perfil-materia', [
            'materia' => $materiaData,
            'asistencia' => $asistenciaData,
            'cortes' => $cortes,
            'recursos' => $recursos,
            'notaMinimaAprobacion' => $notaMinimaAprobacion,
            'configuracion' => $configuracion,
            'horario' => $horario
        ]);
    }

    /**
     * Calcula el promedio ponderado actual de un alumno basado en sus respuestas.
     */
    private function calcularPromedioActual($respuestas, $cortes): float
    {
        $notaTotalPonderada = 0;

        if ($respuestas->isEmpty()) {
            return 0.0;
        }

        // Obtenemos un mapa de item_id => [item_porcentaje, corte_porcentaje]
        $mapaPonderaciones = [];
        $cortes->each(function ($corte) use (&$mapaPonderaciones) {
            $corte->itemInstancias->each(function ($item) use ($corte, &$mapaPonderaciones) {
                $mapaPonderaciones[$item->id] = [
                    'item_porcentaje' => $item->porcentaje,
                    'corte_porcentaje' => $corte->porcentaje,
                ];
            });
        });

        foreach ($respuestas as $respuesta) {
            if ($respuesta->nota_obtenida !== null && isset($mapaPonderaciones[$respuesta->item_corte_materia_periodo_id])) {
                $ponderaciones = $mapaPonderaciones[$respuesta->item_corte_materia_periodo_id];
                $notaPonderadaItem = $respuesta->nota_obtenida * ($ponderaciones['item_porcentaje'] / 100.0);
                $notaPonderadaCorte = $notaPonderadaItem * ($ponderaciones['corte_porcentaje'] / 100.0);
                $notaTotalPonderada += $notaPonderadaCorte;
            }
        }

        return round($notaTotalPonderada, 2);
    }

    public function dashboard(User $user): View|RedirectResponse
    {

        if (Auth::id() !== $user->id) {
            abort(403, 'No tienes permiso para ver este panel.');
        }

        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo->hasPermissionTo('escuelas.es_estudiante')) {
            // 1. Obtenemos todos los banners que tengan el estado "activo"
            $banners = BannerEscuela::where('activo', true)->latest()->get();
            $matriculasActivas = $user->matriculas()
                ->whereHas('periodo', function ($query) {
                    $query->where('estado', true);
                })
                ->with([
                    'periodo',

                    // --- CORRECCIÓN ---
                    // Se usa el nombre correcto de la relación: 'horarioMateriaPeriodo'
                    // para cargar toda la cadena de relaciones anidadas.
                    'horarioMateriaPeriodo.materiaPeriodo.materia',
                    'horarioMateriaPeriodo.horarioBase.aula.sede'
                ])
                ->get();

            return view('contenido.paginas.escuelas.alumnos.dashboard', [
                'matriculas' => $matriculasActivas,
                'alumno' => $user,
                'banners' => $banners
            ]);
        } else {
            return redirect()->route('escuelas.dashboard');
        }
    }

    /**
     * Muestra el historial académico completo para el alumno autenticado.
     */
    public function historialAcademico(): View
    {
        $alumno = Auth::user();

        // 1. Obtenemos todos los registros de materias finalizadas para este alumno
        $historialRegistros = MateriaAprobadaUsuario::with(['periodo', 'materia'])
            ->where('user_id', $alumno->id)
            ->orderBy('periodo_id', 'desc') // Ordenar por periodo más reciente primero
            ->get();

        // 2. Enriquecemos cada registro con los detalles del horario (Aula, Sede, etc.)
        $historial = $historialRegistros->map(function ($registro) {
            $registro->detalles_matricula = $this->getDetallesMatricula($registro);
            return $registro;
        });

        return view('contenido.paginas.escuelas.alumnos.historial-academico', [
            'historial' => $historial,
            'alumno' => $alumno,
        ]);
    }

    /**
     * Método privado para obtener detalles del horario a partir de un registro de materia aprobada.
     */
    private function getDetallesMatricula(MateriaAprobadaUsuario $registro): object
    {
        $matricula = Matricula::where('user_id', $registro->user_id)
            ->whereHas('horarioMateriaPeriodo', function ($query) use ($registro) {
                $query->where('materia_periodo_id', $registro->materia_periodo_id);
            })
            // --- CAMBIO 1: Añadimos la carga de los maestros y su usuario asociado ---
            ->with('horarioMateriaPeriodo.horarioBase.aula.sede', 'horarioMateriaPeriodo.maestros.user')
            ->first();

        if ($matricula && $matricula->horarioMateriaPeriodo?->horarioBase) {
            $horarioBase = $matricula->horarioMateriaPeriodo->horarioBase;

            // --- CAMBIO 2: Obtenemos el nombre del primer maestro asignado ---
            $nombreMaestro = $matricula->horarioMateriaPeriodo->maestros->first()?->user?->nombre(3) ?? 'No asignado';

            return (object) [
                'horario' => $horarioBase->dia_semana . ' | ' . $horarioBase->hora_inicio_formato,
                'aula'    => $horarioBase->aula->nombre ?? 'N/A',
                'sede'    => $horarioBase->aula->sede->nombre ?? 'N/A',
                'maestro' => $nombreMaestro, // <-- Nueva propiedad
            ];
        }

        return (object) ['horario' => 'N/A', 'aula' => 'N/A', 'sede' => 'N/A', 'maestro' => 'N/A'];
    }
}
