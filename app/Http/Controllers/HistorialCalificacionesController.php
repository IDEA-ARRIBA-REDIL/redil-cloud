<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Escuela;
use App\Models\MateriaAprobadaUsuario;
use App\Models\User;
use App\Models\Matricula;
use App\Models\AlumnoRespuestaItem;
use App\Models\ReporteAsistenciaAlumnos;
use PDF; // Asegúrate de tener este import para dompdf

class HistorialCalificacionesController extends Controller
{
    /**
     * Muestra la página de historial y los resultados de la búsqueda.
     */
    public function index(Request $request): View
    {
        // 1. Validar la entrada (opcional, pero buena práctica)
        $request->validate([
            'escuela_id' => 'integer',
            'user_id' => '|integer',
        ]);

        // 2. Obtener los datos para los filtros y el estado actual
        $escuelas = Escuela::orderBy('nombre')->get();
        $selectedEscuelaId = $request->input('escuela_id');
        $selectedUserId = $request->input('user_id');

        $selectedUser = null;
        $historial = collect(); // Iniciar como una colección vacía

        // 3. Si se ha seleccionado un usuario, buscar su historial
        if ($selectedUserId) {
            $selectedUser = User::find($selectedUserId);
            $historialRegistros = MateriaAprobadaUsuario::with(['periodo', 'materia'])
                ->where('user_id', $selectedUserId)
                ->orderBy('periodo_id', 'desc')
                ->get();

            // 4. Enriquecer cada registro con los detalles del horario (Aula, Sede, etc.)
            $historial = $historialRegistros->map(function ($registro) {
                $registro->detalles_matricula = $this->getDetallesMatricula($registro);
                return $registro;
            });
        }

        // 5. Devolver la vista con todos los datos necesarios
        return view('contenido.paginas.escuelas.historial-calificaciones.consultar-historial', [
            'escuelas' => $escuelas,
            'selectedEscuelaId' => $selectedEscuelaId,
            'selectedUser' => $selectedUser,
            'historial' => $historial,
        ]);
    }

    /**
     * Método privado para obtener detalles del horario y MAESTRO.
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

    /**
     * Genera y descarga un PDF con el boletín de notas para un registro académico.
     */
    public function exportarBoletin(MateriaAprobadaUsuario $materiaAprobadaUsuario)
    {
        $registro = $materiaAprobadaUsuario;
        $alumno = $registro->user;
        // --- INICIO: CÓDIGO AÑADIDO PARA OBTENER EL MAESTRO ---
        $matricula = Matricula::where('user_id', $alumno->id)
            ->whereHas('horarioMateriaPeriodo', function ($q) use ($registro) {
                $q->where('materia_periodo_id', $registro->materia_periodo_id);
            })
            ->with('horarioMateriaPeriodo.maestros.user')
            ->first();

        $nombreMaestro = $matricula?->horarioMateriaPeriodo?->maestros?->first()?->user?->nombre(3) ?? 'No Asignado';
        // --- FIN: CÓDIGO AÑADIDO ---

        // 1. Cargar el detalle de las notas (esta parte estaba bien)
        $notasDetalladas = AlumnoRespuestaItem::where('user_id', $alumno->id)
            ->whereHas('itemCalificado.materiaPeriodo', function ($query) use ($registro) {
                $query->where('id', $registro->materia_periodo_id);
            })
            ->with('itemCalificado.cortePeriodo.corteEscuela')
            ->get();

        // 2. Cargar el detalle de las asistencias (VERSIÓN CORREGIDA)
        $asistenciaDetallada = ReporteAsistenciaAlumnos::where('user_id', $alumno->id)
            // --- INICIO DE LA CORRECCIÓN ---
            // Se corrige la cadena de la relación de 'reporteClase.horario.materiaPeriodo'
            // a 'reporteClase.horarioMateriaPeriodo'.
            ->whereHas('reporteClase.horarioMateriaPeriodo', function ($query) use ($registro) {
                // Ahora la consulta apunta al modelo correcto (HorarioMateriaPeriodo)
                // y podemos filtrar por el ID de la materia del periodo.
                $query->where('materia_periodo_id', $registro->materia_periodo_id);
            })
            // --- FIN DE LA CORRECCIÓN ---
            ->with('reporteClase')
            ->get()
            ->sortBy('reporteClase.fecha_clase_reportada');

        // 3. Preparar los datos para la vista del PDF
        $data = [
            'registro' => $registro,
            'alumno' => $alumno,
            'notasDetalladas' => $notasDetalladas,
            'asistenciaDetallada' => $asistenciaDetallada,
            'nombreMaestro' => $nombreMaestro,
        ];

        // 4. Cargar la vista y generar el PDF
        $pdf = PDF::loadView('contenido.paginas.escuelas.historial-calificaciones.boletin-materia', $data);

        // 5. Generar un nombre de archivo y servir la descarga
        $nombreAlumno = str_replace(' ', '_', $alumno->nombre(3));
        $nombreMateria = str_replace(' ', '_', $registro->materia->nombre);
        $fileName = "Boletin_{$nombreAlumno}_{$nombreMateria}.pdf";

        return $pdf->download($fileName);
    }
}
