<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Escuela;
use App\Models\Matricula;
use App\Models\User;
use App\Services\MatriculaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatriculaController extends Controller
{
    /**
     * Muestra la vista para gestionar matrículas.
     *
     * @param  User  $user  El usuario ACTIVO (administrador).
     * @param  MatriculaService  $matriculaService  El servicio que contiene la lógica de negocio.
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

        // Inicializamos las colecciones para evitar errores en la vista
        $reporteItems = collect();
        $matriculasDelAlumno = collect();

        // -------------------------------------------------------------------------
        // LOGICA DE PROCESAMIENTO SI HAY USUARIO Y ESCUELA SELECCIONADOS
        // -------------------------------------------------------------------------
        if ($usuarioSeleccionado && $escuelaSeleccionada) {

            // 1. OBTENER TODAS LAS MATRÍCULAS DEL ALUMNO
            $matriculasDelAlumno = Matricula::where('user_id', $usuarioSeleccionado->id)
                ->with([
                    'periodo',
                    'escuela',
                    'estadoPago',
                    'horarioMateriaPeriodo.materiaPeriodo.materia',
                    'horarioMateriaPeriodo.horarioBase.aula.sede',
                ])
                ->latest('id')
                ->get();

            // 2. BIFURCACIÓN DE LÓGICA: ¿Niveles o Materias?
            // Dependiendo de la configuración de la escuela, obtenemos un tipo de reporte u otro.

            if ($escuelaSeleccionada->tipo_matricula === 'niveles_agrupados') {
                // --- LÓGICA DE NIVELES ---
                // Obtenemos el reporte de disponibilidad de NIVELES (Grados).
                $reporteItems = $matriculaService->getReporteDisponibilidadNiveles($usuarioSeleccionado, $escuelaSeleccionada);
            } else {
                // --- LÓGICA DE MATERIAS INDEPENDIENTES ---
                // Obtenemos el reporte estándar de disponibilidad de MATERIAS.
                $reporteItems = $matriculaService->getReporteDisponibilidadMaterias($usuarioSeleccionado, $escuelaSeleccionada);
            }

            // 3. ORDENAMIENTO DEL REPORTE
            // Priorizamos los ítems (materias o niveles) que están DISPONIBLES para facilitar la gestión.
            $reporteItems = $reporteItems->sortBy(function ($item) {
                return match ($item->estado) {
                    'DISPONIBLE' => 0,
                    'APROBADA', 'APROBADO' => 1,
                    default => 2, // BLOQUEADA
                };
            });
        }

        // Retornamos la vista con los datos procesados según el tipo de escuela.
        return view('contenido.paginas.escuelas.matriculas.gestionar-matriculas', [
            'usuarioActivo' => $usuarioActivo,
            'usuarioSeleccionado' => $usuarioSeleccionado,
            'escuelaSeleccionada' => $escuelaSeleccionada,
            'escuelas' => $escuelas,
            'reporteItems' => $reporteItems, // Contiene Niveles o Materias según el caso
            'matriculasDelAlumno' => $matriculasDelAlumno,
            'configuracion' => $configuracion,
            'userId' => $usuarioSeleccionado?->id,
            'rolActivo' => $rolActivo,
        ]);
    }

    public function eliminarMatricula(Matricula $matricula, User $user)
    {
        $matriculaId = $matricula->id;

        // 1. Desvincular de la clase (pivote horario/materia/periodo) para liberar el cupo
        DB::table('matricula_horario_materia_periodo')->where('matricula_id', $matriculaId)->delete();

        // 2. Registrar el usuario administrador que ejecutó la eliminación si existe la columna
        if (\Illuminate\Support\Facades\Schema::hasColumn('matriculas', 'deleted_by')) {
            $matricula->deleted_by = auth()->id();
            $matricula->save();
        }

        // 3. Ejecutar Soft Delete
        $matricula->delete();

        return redirect()->back()->with('success', "La matrícula ID #{$matriculaId} ha sido eliminada correctamente y enviada al historial.");
    }

    /**
     * Vista de Historial de Matrículas Eliminadas / Canceladas con buscador multi-campo.
     */
    public function historialEliminadas(Request $request, User $user)
    {
        $usuarioActivo = $user;
        $rolActivo = auth()->user()->roles()->where('activo', true)->first();

        if (! $rolActivo || (! $rolActivo->hasPermissionTo('escuelas.subitem_historial_matriculas') && ! $rolActivo->hasPermissionTo('escuelas.opcion_eliminar_matricula') && ! $rolActivo->hasPermissionTo('escuelas.opcion_eliminar_materia'))) {
            abort(403, 'No tienes permisos para acceder al historial de matrículas eliminadas.');
        }

        $query = Matricula::onlyTrashed()
            ->with([
                'user',
                'periodo',
                'horarioMateriaPeriodo.materiaPeriodo.materia',
                'horarioMateriaPeriodo.horarioBase.aula.sede',
                'escuela',
                'deletedBy',
                'estadoPago',
                'compra.pagos',
            ]);

        // Filtrar por término de búsqueda (Identificación, Nombre, #ID o Periodo)
        if ($request->filled('buscar')) {
            $buscar = trim((string) $request->query('buscar'));
            $query->where(function ($q) use ($buscar) {
                if (is_numeric($buscar)) {
                    $q->orWhere('id', (int) $buscar);
                }
                $q->orWhereHas('user', function ($qUser) use ($buscar) {
                    $qUser->where('identificacion', 'like', "%{$buscar}%")
                        ->orWhere('primer_nombre', 'like', "%{$buscar}%")
                        ->orWhere('segundo_nombre', 'like', "%{$buscar}%")
                        ->orWhere('primer_apellido', 'like', "%{$buscar}%")
                        ->orWhere('segundo_apellido', 'like', "%{$buscar}%")
                        ->orWhere(DB::raw("CONCAT(primer_nombre, ' ', primer_apellido)"), 'like', "%{$buscar}%");
                });
                $q->orWhereHas('periodo', function ($qPeriodo) use ($buscar) {
                    $qPeriodo->where('nombre', 'like', "%{$buscar}%");
                });
                $q->orWhereHas('horarioMateriaPeriodo.materiaPeriodo.materia', function ($qMateria) use ($buscar) {
                    $qMateria->where('nombre', 'like', "%{$buscar}%");
                });
            });
        }

        // Filtrar por periodo específico
        if ($request->filled('periodo_id')) {
            $query->where('periodo_id', $request->query('periodo_id'));
        }

        $matriculasEliminadas = $query->latest('deleted_at')->paginate(20)->withQueryString();
        $periodos = \App\Models\Periodo::orderBy('nombre', 'desc')->get();

        return view('contenido.paginas.escuelas.matriculas.historial-eliminadas', [
            'usuarioActivo' => $usuarioActivo,
            'rolActivo' => $rolActivo,
            'matriculasEliminadas' => $matriculasEliminadas,
            'periodos' => $periodos,
            'buscar' => $request->query('buscar'),
            'periodoId' => $request->query('periodo_id'),
        ]);
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
                    'trasladosLog.user', // <-- AÑADIR ESTA LÍNEA
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
