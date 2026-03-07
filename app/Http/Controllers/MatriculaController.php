<?php

namespace App\Http\Controllers;


use App\Models\AlumnoRespuestaItem;
use App\Models\ReporteAsistenciaAlumnos;

use App\Models\User;
use App\Models\Materia;
use App\Models\Matricula;
use App\Models\Configuracion;
use App\Models\Escuela;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\MatriculaService;

class MatriculaController extends Controller
{
    /**
     * Muestra la vista para gestionar matrículas.
     *
     * @param Request $request
     * @param User $user El usuario ACTIVO (administrador).
     * @param MatriculaService $matriculaService El servicio que contiene la lógica de negocio.
     * @return \Illuminate\View\View
     */
    public function gestionar(Request $request, User $user, MatriculaService $matriculaService)
    {
        $usuarioActivo = $user;

        $estudianteId = $request->query('buscador-estudiante');
        $escuelaId = $request->query('escuela_id');

        $usuarioSeleccionado = $estudianteId ? User::find($estudianteId) : null;
        $escuelaSeleccionada = $escuelaId ? Escuela::find($escuelaId) : null;

        $configuracion = Configuracion::find(1);
        $escuelas = Escuela::orderBy('nombre')->get();
        $rolActivo = auth()->user()->roles()->where('activo', true)->first();

        $materiasParaMostrar = collect();
        $matriculasDelAlumno = collect();

        if ($usuarioSeleccionado && $escuelaSeleccionada) {

            // 1. Obtenemos las matrículas del alumno SOLO de periodos activos.
            $matriculasDelAlumno = Matricula::where('user_id', $usuarioSeleccionado->id)
                ->whereHas('periodo', function ($query) {
                    $query->where('estado', true);
                })
                ->with([
                    'periodo',
                    'horarioMateriaPeriodo.materiaPeriodo.materia',
                    'horarioMateriaPeriodo.horarioBase.aula.sede'
                ])
                ->get();

            // 2. Usamos el servicio para saber qué materias están disponibles para matricular.
            $materiasDisponibles = $matriculaService->getMateriasDisponibles($usuarioSeleccionado, $escuelaSeleccionada);

            // --- LÓGICA CLAVE: CONSTRUIR LA LISTA FINAL ---
            // 3. Extraemos los modelos de Materia desde las matrículas existentes.
            // $materiasYaMatriculadas = $matriculasDelAlumno->map(function ($matricula) { // No longer needed
            //     // Navegamos a través de las relaciones para obtener el objeto Materia.
            //     return $matricula->horarioMateriaPeriodo->materiaPeriodo->materia;
            // });

            // 4. Unimos las dos colecciones: las ya matriculadas + las disponibles.
            // El método unique() asegura que no haya materias duplicadas en la lista final.
            // $materiasParaMostrar = $materiasYaMatriculadas->merge($materiasDisponibles)->unique('id'); // No longer needed

            // Obtener el reporte de disponibilidad y ORDENARLO: Disponibles primero, luego las que no.
            $reporteMaterias = $matriculaService->getReporteDisponibilidadMaterias($usuarioSeleccionado, $escuelaSeleccionada)
                ->sortBy(function ($item) {
                     return match ($item->estado) {
                        'DISPONIBLE' => 0,
                        'APROBADA' => 1,
                        default => 2, // BLOQUEADA
                    };
                });
        } else {
            $reporteMaterias = collect(); // Initialize if no selection
        }




        return view('contenido.paginas.escuelas.matriculas.gestionar-matriculas', [
            'usuarioActivo' => $usuarioActivo,
            'usuarioSeleccionado' => $usuarioSeleccionado,
            'escuelaSeleccionada' => $escuelaSeleccionada,
            'escuelas' => $escuelas,
            'reporteMaterias' => $reporteMaterias,
            'matriculasDelAlumno' => $matriculasDelAlumno, // <-- Restored
            'configuracion' => $configuracion,
            'userId' => $usuarioSeleccionado?->id,
            'rolActivo' => $rolActivo
        ]);
    }



    public function eliminarMatricula(Matricula $matricula, User $user)
    {
        // --- 1. VALIDACIÓN ---
        // Necesitamos el ID del HorarioMateriaPeriodo para buscar en las otras tablas.
        $horarioMateriaPeriodoId = $matricula->horario_materia_periodo_id;

        // Validar si el estado del pago permite la eliminación (no es final)
        if ($matricula->estadoPago && $matricula->estadoPago->estado_final_inscripcion) {
             return redirect()->back()->with('error', 'No se puede eliminar la matrícula porque el pago ya ha sido completado y finalizado.');
        }

        // Verificamos si existen notas para este alumno en este horario/clase.
        $tieneNotas = AlumnoRespuestaItem::where('user_id', $matricula->user_id)
            ->whereHas('itemCalificado', function ($query) use ($horarioMateriaPeriodoId) {
                $query->where('horario_materia_periodo_id', $horarioMateriaPeriodoId);
            })->exists();

        // Verificamos si existen asistencias para este alumno en este horario/clase.
        $tieneAsistencias = ReporteAsistenciaAlumnos::where('user_id', $matricula->user_id)
            ->whereHas('reporteClase', function ($query) use ($horarioMateriaPeriodoId) {
                $query->where('horario_materia_periodo_id', $horarioMateriaPeriodoId);
            })->exists();

        // Si tiene notas O asistencias, no se puede eliminar.
        if ($tieneNotas || $tieneAsistencias) {
            return redirect()->back()->with('error', 'No se puede eliminar la matrícula porque el alumno ya tiene notas o registros de asistencia asociados.');
        }

        // --- 2. ELIMINACIÓN ---
        // Usamos una transacción para asegurar que todo se elimine correctamente.
        try {
            DB::transaction(function () use ($matricula) {
                // Buscamos la compra asociada
                $compra = \App\Models\Compra::find($matricula->referencia_pago);

                // Eliminar relaciones de la compra si existe
                if ($compra) {
                    $compra->pagos()->delete();
                    $compra->inscripciones()->delete();
                    $compra->delete();
                }

                // CAMBIO: Usamos el nuevo nombre de la relación que creaste.
                $matricula->estadoAcademicoClase()->delete();

                // Luego eliminamos la matrícula principal.
                $matricula->delete();
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error al intentar eliminar la matrícula: ' . $e->getMessage());
        }
        // --- 3. RESPUESTA ---
        return redirect()->back()->with('success', 'Matrícula y registros asociados eliminados correctamente.');
    }

    public function gestionarTraslados(Request $request, User $user)
    {
        // 1. OBTENER USUARIOS Y PARÁMETROS
        $usuarioActivo = $user; // El administrador que está usando la vista.

        $estudianteId = $request->query('buscador-estudiante');
        $escuelaId = $request->query('escuela_id');

        $usuarioSeleccionado = $estudianteId ? User::find($estudianteId) : null;
        $escuelaSeleccionada = $escuelaId ? Escuela::find($escuelaId) : null;



        $configuracion = Configuracion::find(1);
        $escuelas = Escuela::orderBy('nombre')->get();
        $matriculasActivas = collect(); // Inicializamos la colección.

        // 2. BUSCAR MATRÍCULAS ACTIVAS
        // Si tenemos un estudiante y una escuela, buscamos sus matrículas.
        if ($usuarioSeleccionado && $escuelaSeleccionada) {
            $matriculasActivas = Matricula::where('user_id', $usuarioSeleccionado->id)
                // Usamos whereHas para filtrar solo las matrículas cuyo periodo...
                ->whereHas('periodo', function ($query) use ($escuelaSeleccionada) {
                    // ...esté activo Y pertenezca a la escuela seleccionada.
                    $query->where('estado', true)
                        ->where('escuela_id', $escuelaSeleccionada->id);
                })
                // Precargamos todas las relaciones que necesitaremos en la vista para ser eficientes.
                ->with([
                    'periodo',
                    'horarioMateriaPeriodo.materiaPeriodo.materia',
                    'horarioMateriaPeriodo.horarioBase.aula.sede',
                    'trasladosLog.user' // <-- AÑADIR ESTA LÍNEA
                ])
                ->get();
        }

        // 3. ENVIAR DATOS A LA VISTA
        return view('contenido.paginas.escuelas.matriculas.gestionar-traslados', [
            'usuarioActivo' => $usuarioActivo,
            'usuarioSeleccionado' => $usuarioSeleccionado,
            'escuelaSeleccionada' => $escuelaSeleccionada,
            'escuelas' => $escuelas,
            'matriculasActivas' => $matriculasActivas, // Pasamos las matrículas encontradas.
            'configuracion' => $configuracion,
            'userId' => $usuarioSeleccionado?->id,
        ]);
    }

    /**
     * Muestra la vista donde el estudiante (o admin invitado) puede solicitar un traslado.
     */
    public function solicitarTraslado(User $usuario)
    {
        $usuario = Auth::user();
        return view('contenido.paginas.escuelas.matriculas.solicitar-traslado', [
            'usuario' => $usuario,

        ]);
    }

    /**
     * Muestra la vista administrativa para gestionar las solicitudes de traslado pendientes.
     */
    public function gestionarSolicitudesTraslado()
    {
        return view('contenido.paginas.escuelas.matriculas.gestionar-solicitudes-traslado');
    }
}
