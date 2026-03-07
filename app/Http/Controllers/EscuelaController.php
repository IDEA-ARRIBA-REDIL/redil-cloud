<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Escuela;
use App\Models\CorteEscuela; // Importar el modelo CorteEscuela
use App\Models\User;
use App\Models\Matricula;
use App\Exports\MatriculasActivasEscuelaExport;
use Maatwebsite\Excel\Facades\Excel;

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

class EscuelaController extends Controller
{
    /**
     * Muestra el panel principal
     */
    /**
     * Muestra el panel principal del módulo de escuelas, adaptando el contenido
     * según el rol del usuario autenticado.
     */

    /**
     * Actúa como un gatekeeper para el panel de escuelas.
     * Redirige al usuario al dashboard o página correspondiente según su rol/permiso.
     */
    public function panel(): View|RedirectResponse
    {
        $user = Auth::user();
        $rolActivo = $user->roles()->wherePivot('activo', true)->first();

        // Si el usuario no tiene un rol activo, no puede continuar.
        if (!$rolActivo) {
            // Puedes mostrar una vista de error o redirigir a otra página.
            abort(403, 'No tienes un rol activo asignado.');
        }

        // --- LÓGICA DE REDIRECCIÓN DIRECTA ---

        // 1. Si el usuario es un Estudiante
        if ($rolActivo->hasPermissionTo('escuelas.es_estudiante')) {
            // Lo redirigimos a la ruta del dashboard del alumno.
            // Asegúrate de que esta ruta exista en tu archivo web.php
            return redirect()->route('alumnos.dashboard', ['user' => $user]);
        }
        // 2. Si el usuario es un Maestro
        elseif ($rolActivo->hasPermissionTo('escuelas.es_maestro')) {
            // Para redirigir a 'maestros.horariosAsignados', necesitamos el ID del maestro.
            // Buscamos el perfil de "Maestro" que corresponde al usuario actual.

            // Redirigimos a la ruta que necesitas, pasando el ID del maestro encontrado.
            return redirect()->route('maestros.misHorarios', ['user' => $user]);
        } // 3. Si el usuario es un Administrativo
        elseif ($rolActivo->hasPermissionTo('escuelas.es_administrativo')) {
            // El administrativo no se redirige, se le muestra el dashboard principal.
            return redirect()->route('escuelas.adminDashboard');
        }
    }

    /**
     * Obtiene los datos necesarios para el panel del estudiante.
     * (Ej: sus matrículas activas).
     */
    private function obtenerDatosEstudiante($user)
    {
        // Aquí iría la lógica para buscar las matrículas activas del estudiante.
        // Por ejemplo:
        // $matriculas = $user->matriculas()->with('horario.materiaPeriodo.materia')->where('estado', 'activa')->get();
        return [
            'mensaje' => 'Estos son los cursos en los que estás matriculado.',
            // 'matriculas' => $matriculas,
        ];
    }

    /**
     * Obtiene los datos necesarios para el panel del maestro.
     * (Ej: los horarios que tiene asignados).
     */
    private function obtenerDatosMaestro($user)
    {
        // Lógica para buscar los horarios del maestro
        return [
            'mensaje' => 'Estos son los cursos que tienes a tu cargo.',
            // 'horarios' => $horarios,
        ];
    }

    /**
     * Obtiene los datos necesarios para el panel del administrativo.
     * (Ej: estadísticas generales de la escuela).
     */
    private function obtenerDatosAdmin($user)
    {
        // Lógica para buscar estadísticas
        return [
            'mensaje' => 'Aquí tienes las estadísticas generales del módulo de escuelas.',
            // 'estadisticas' => $estadisticas,
        ];
    }

    /**
     * Muestra el formulario para gestionar escuelas
     */
    public function gestionarEscuelas()
    {
        $escuelas = Escuela::all();
        // Considera usar auth()->user() directamente si no necesitas específicamente el modelo User asociado al rol
        $rolActivo = auth()->user()->roles()->where('activo', true)->first();
        // $usuario = User::find($rolActivo->pivot->model_id); // Esto podría fallar si el rol no es de tipo User
        $usuario = auth()->user(); // Más directo si solo necesitas el usuario logueado
        $configuracion = Configuracion::find(1);

        return view('contenido.paginas.escuelas.gestionar-escuelas', [
            'escuelas' => $escuelas,
            // 'rolActivo' => $rolActivo, // Comentado si no se usa
            'usuario' => $usuario,
            'configuracion' => $configuracion,
            'rolActivo' => $rolActivo
        ])->with('moduloEscuelas', true);
    }

    /**
     * Guarda una nueva escuela y sus cortes asociados.
     */
    public function guardar(Request $request)
    {
        // Validación de los campos



        // 1. Crear la Escuela
        $escuela = new Escuela();
        $escuela->nombre = $request->nombre;
        $escuela->descripcion = $request->descripcion;
        $escuela->tipo_matricula = $request->tipo_matricula;
        $escuela->habilitada_consilidacion = $request->has('habilitada_consilidacion');
        $escuela->save();


        // 2. Crear los CortesEscuela asociados con porcentaje distribuido
        $cantidadCortes = (int)$request->cortes; // Asegurar que es entero
        $nombreBaseCorte = $request->nombreCortes;

        // Calcular porcentajes enteros que sumen 100
        $basePorcentaje = floor(100 / $cantidadCortes);
        $restoPorcentaje = 100 % $cantidadCortes;
        $porcentajes = [];

        for ($i = 0; $i < $cantidadCortes; $i++) {
            // Asignar el resto a los primeros cortes
            $porcentajes[] = $basePorcentaje + ($i < $restoPorcentaje ? 1 : 0);
        }


        // Crear los cortes
        for ($i = 0; $i < $cantidadCortes; $i++) {
            $orden = $i + 1; // El orden empieza en 1
            CorteEscuela::create([
                'escuela_id' => $escuela->id,
                'nombre' => $nombreBaseCorte . ' ' . $orden . '44' . $escuela->id, // Ej: "Corte 1"
                'orden' => $orden,
                'porcentaje' => 30, // Asignar el porcentaje calculado
            ]);
        }


        // Redirigir con mensaje de éxito
        return redirect()->route('escuelas.gestionarEscuelas')
            ->with('success', '¡Escuela y sus cortes (con porcentajes) creados exitosamente!')
            ->with('moduloEscuelas', true);
    }
    /**
     * Muestra una escuela específica para actualizar (vista detalle/edición)
     */
    public function actualizar(Escuela $escuela)
    {
        // Cargar relaciones necesarias
        $escuela->load(['materias', 'cortesEscuela']); // Cargar los cortes

        // Calcular la suma actual de los porcentajes de los cortes
        $sumaPorcentajesActual = $escuela->cortesEscuela->sum('porcentaje');
        $rolActivo = auth()->user()->roles()->where('activo', true)->first();
        $configuracion = Configuracion::find(1);
        $usuario = auth()->user();

        return view('contenido.paginas.escuelas.actualizar-escuela', [
            'escuela' => $escuela,
            'configuracion' => $configuracion,
            'usuario' => $usuario,
            'rolActivo' => $rolActivo,
            'sumaPorcentajesActual' => $sumaPorcentajesActual, // Pasar la suma a la vista
        ])->with('moduloEscuelas', true);
    }

    public function gestionarHorarios(User $user) {}


    /**
     * Muestra el formulario de edición (si es una vista separada)
     * Si 'actualizar' ya muestra el formulario, este método puede no ser necesario.
     */
    // public function editar(Escuela $escuela)
    // {
    //     return view('contenido.paginas.escuelas.editar', compact('escuela'))
    //         ->with('moduloEscuelas', true);
    // }

    /**
     * Actualiza una escuela existente (solo datos de la escuela, no los cortes aquí)
     */
    public function update(Request $request, Escuela $escuela)
    {
        // ... (Código del método update sin cambios respecto a la versión anterior) ...
        $configuracion = Configuracion::find(1);
        $datosValidados = $request->validate([
            'nombre' => 'required|string|max:200|unique:escuelas,nombre,' . $escuela->id,
            'descripcion' => 'nullable|string',
            'tipo_matricula' => 'required|in:materias_independientes,niveles_agrupados',
            'habilitada_consilidacion' => 'nullable|boolean',
        ]);

        $escuela->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'tipo_matricula' => $request->tipo_matricula,
            'habilitada_consilidacion' => $request->has('habilitada_consilidacion'),
        ]);

        if ($request->filled('foto')) {

            if ($configuracion->version == 1) {
                $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/escuelas/');
                !is_dir($path) && mkdir($path, 0777, true);

                $data = explode(';base64,', $request->foto)[1];
                file_put_contents(
                    $path . 'escuela' . $escuela->id . '.png',
                    base64_decode($data)
                );
                $escuela->portada = 'escuela' . $escuela->id . '.png';
                $escuela->save();
            }
        }

        return redirect()->route('escuelas.actualizar', $escuela->id)
            ->with('success', '¡Escuela actualizada correctamente!');
    }

    /**
     * Muestra las materias asociadas a una escuela
     */
    public function materias(Escuela $escuela)
    {
        $materias = $escuela->materias; // Carga implícita por la relación
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->where('activo', true)->first();
        $usuario = auth()->user();


        return view('contenido.paginas.escuelas.materias-asociadas', [
            'escuela' => $escuela,
            'materias' => $materias,
            'usuario' => $usuario,
            'configuracion' => $configuracion,
            'rolActivo' => $rolActivo,
        ])->with('moduloEscuelas', true);
    }

    /**
     * Elimina una escuela (Considera usar Soft Deletes si está habilitado)
     */
    public function eliminar(Escuela $escuela)
    {
        try {
            // Si usas SoftDeletes, esto marcará como eliminado: $escuela->delete();
            // Si quieres borrado físico: $escuela->forceDelete();
            // Si tienes restricciones ON DELETE CASCADE, se borrarán los registros relacionados (cortes, periodos, etc.)
            // ¡Ten cuidado con el borrado físico!

            // Asumiendo borrado físico o SoftDelete simple:
            $escuela->delete();

            // Cambiado 'gestionar' a 'gestionarEscuelas' para coincidir con el nombre de la ruta definido generalmente
            return redirect()->route('escuelas.gestionarEscuelas')
                ->with('success', '¡Escuela eliminada exitosamente!'); // Cambiado 'exito' a 'success' por convención

        } catch (\Exception $e) {
            Log::error('Error al eliminar escuela: ' . $e->getMessage());
            // Manejar error si hay restricciones que impiden borrar
            return redirect()->route('escuelas.gestionarEscuelas')
                ->with('error', 'No se pudo eliminar la escuela. Puede tener registros asociados.');
        }
    }

    /**
     * Exporta a Excel un listado de todas las matrículas de los periodos activos de una escuela.
     *
     * @param Escuela $escuela La escuela de la cual exportar las matrículas.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportarMatriculasActivas(Escuela $escuela)
    {
        // 1. Obtener los IDs de los periodos ACTIVOS de esta escuela
        $periodosActivosIds = $escuela->periodos()->where('estado', true)->pluck('id');

        if ($periodosActivosIds->isEmpty()) {
            // Si no hay periodos activos, redirige atrás con un mensaje
            return back()->with('mensaje_info', 'La escuela "' . $escuela->nombre . '" no tiene periodos activos actualmente.');
        }

        // 2. Obtener todas las matrículas de esos periodos activos
        //    Usamos Eager Loading para cargar todas las relaciones necesarias de una sola vez
        $matriculas = Matricula::whereIn('periodo_id', $periodosActivosIds)
            ->with([
                // Carga el usuario y selecciona solo las columnas necesarias
                'user:id,primer_nombre,segundo_nombre,primer_apellido,segundo_apellido,identificacion',
                // Carga la relación con el periodo
                'periodo:id,nombre',
                // Carga las relaciones anidadas para obtener la materia y el horario
                'horarioMateriaPeriodo.materiaPeriodo.materia:id,nombre',
                'horarioMateriaPeriodo.horarioBase.aula:id,nombre',
                // Carga la sede asociada directamente a la matrícula (si existe la relación)
                // Si la sede viene del aula, ajusta esta línea
                'sede:id,nombre'
            ])
            ->orderBy('periodo_id') // Ordena por periodo
            ->orderBy('user_id') // Luego por alumno
            ->get();

        if ($matriculas->isEmpty()) {
            return back()->with('mensaje_info', 'No se encontraron matrículas en los periodos activos de la escuela "' . $escuela->nombre . '".');
        }

        // 3. Prepara el nombre del archivo
        $nombreArchivo = 'Matriculas_Activas_' . str_replace(' ', '_', $escuela->nombre) . '_' . date('Y-m-d') . '.xlsx';

        // 4. Dispara la descarga usando la clase de exportación
        return Excel::download(new MatriculasActivasEscuelaExport($matriculas), $nombreArchivo);
    }
}
