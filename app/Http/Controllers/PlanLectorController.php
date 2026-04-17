<?php

namespace App\Http\Controllers;

use App\Models\PlanLector;
use App\Models\Configuracion;
use App\Models\Sede;
use App\Models\EstadoCivil;
use App\Models\RangoEdad;
use App\Models\TipoUsuario;
use App\Models\PasoCrecimiento;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\TareaConsolidacion;
use App\Models\EstadoTareaConsolidacion;
use App\Models\PlanLectorCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'configuracion' => $configuracion
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
        
        if (!$puedeVer || !$plan->estado) {
            return back()->with('error', 'No tienes permitido acceder a este plan lector en estos momentos.');
        }

        // Crear la relación (attach lanza error si el unique la rechaza pero validamos antes)
        if (!$usuario->planesLectoresInscritos()->where('plan_lector_id', $plan->id)->exists()) {
            $usuario->planesLectoresInscritos()->attach($plan->id, [
                'estado' => 'inscrito',
                'fecha_inscripcion' => now(),
                'porcentaje_progreso' => 0
            ]);
        }

        // Redirigir directamente al visor de lectura.
        return redirect()->route('planes-lectores.lectura', $plan->slug)->with('success', '¡Te has inscrito exitosamente a ' . $plan->titulo . '!');
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
                        $term = '%' . trim($palabra) . '%';
                        
                        // Normalizamos acentos tanto en la columna como en el término de búsqueda para PostgreSQL
                        $query->where(DB::raw("TRANSLATE(planes_lectores.titulo, 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU')"), 'ILIKE', DB::raw("TRANSLATE('" . $term . "', 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU')"));
                    }
                }
            });
        }

        if (!empty($categoriasSeleccionadas)) {
             $planesQuery->whereHas('categorias', function ($q) use ($categoriasSeleccionadas) {
                 $q->whereIn('plan_lector_categorias.id', $categoriasSeleccionadas);
             });
        }

        if ($estado !== null && $estado !== '') {
            $planesQuery->where('estado', $estado);
        }

        // Restricciones según permisos
        if (!$rolActivo->hasPermissionTo('planes_lectores.listar_todos_planes_lectores')) {
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
            $tagsBusqueda[] = (object)[
                'field' => 'buscar_plan',
                'fieldAux' => 'buscar_plan_offcanvas',
                'value' => $buscar,
                'label' => 'Búsqueda: ' . $buscar
            ];
            $bandera = 1;
        }

        if (!empty($categoriasSeleccionadas)) {
            foreach ($categoriasSeleccionadas as $catId) {
                $cat = $categorias->where('id', $catId)->first();
                if ($cat) {
                    $tagsBusqueda[] = (object)[
                        'field' => 'categorias',
                        'fieldAux' => null,
                        'value' => $cat->id,
                        'label' => 'Categoría: ' . $cat->nombre
                    ];
                }
            }
            $bandera = 1;
        }

        if ($estado !== null && $estado !== '') {
            $estadoStr = $estado == 1 ? 'Activos' : 'Inactivos';
            $tagsBusqueda[] = (object)[
                'field' => 'estado',
                'fieldAux' => null,
                'value' => $estado,
                'label' => 'Estado: ' . $estadoStr
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

            $plan = new PlanLector();
            $plan->titulo = $request->titulo;
            $plan->slug = Str::slug($request->titulo);
            $plan->descripcion = $request->descripcion;
            $plan->autor_id = auth()->id();
            $plan->visible_todos = $request->has('visible_todos');
            $plan->genero = $request->genero ?? 3;
            $plan->estado = true;

            // Manejo de la imagen base64 (recortada)
            if ($request->imagen_base64) {
                $configuracion = Configuracion::find(1);
                $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/planes_lectores/');

                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                $imagenPartes = explode(';base64,', $request->imagen_base64);
                if (isset($imagenPartes[1])) {
                    $imagenBase64 = base64_decode($imagenPartes[1]);
                    $nombreFoto = 'plan-' . time() . '.jpg';
                    $imagenPath = $path . $nombreFoto;

                    file_put_contents($imagenPath, $imagenBase64);

                    // Asumiendo que guardamos la URL relativa o completa
                    $plan->imagen_url = 'storage/' . $configuracion->ruta_almacenamiento . '/img/planes_lectores/' . $nombreFoto;
                }
            }

            $plan->save();

            // Guardar categorías
            if ($request->categorias) {
                $plan->categorias()->sync($request->categorias);
            }

            // Guardar restricciones
            if (!$plan->visible_todos) {
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
                                'indice' => $index
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
                                'indice' => $index
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('planes-lectores.gestionar')->with('success', 'Plan lector creado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el plan lector: ' . $e->getMessage())->withInput();
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
                $configuracion = Configuracion::find(1);
                $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/planes_lectores/');

                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                // Borrar imagen anterior si existe y no es el placeholder
                if ($plan->imagen_url && strpos($plan->imagen_url, 'placeholder') === false) {
                    $oldPath = public_path($plan->imagen_url);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $imagenPartes = explode(';base64,', $request->imagen_base64);
                if (isset($imagenPartes[1])) {
                    $imagenBase64 = base64_decode($imagenPartes[1]);
                    $nombreFoto = 'plan-' . time() . '.jpg';
                    $imagenPath = $path . $nombreFoto;

                    file_put_contents($imagenPath, $imagenBase64);
                    $plan->imagen_url = 'storage/' . $configuracion->ruta_almacenamiento . '/img/planes_lectores/' . $nombreFoto;
                }
            }

            $plan->save();

            // Sincronizar categorías
            $plan->categorias()->sync($request->categorias ?? []);

            // Guardar restricciones
            if (!$plan->visible_todos) {
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
                                'indice' => $index
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
                                'indice' => $index
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
            return back()->with('error', 'Error al actualizar el plan lector: ' . $e->getMessage())->withInput();
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
                $path = public_path($plan->imagen_url);
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $plan->delete();

            return redirect()->route('planes-lectores.gestionar')->with('success', 'Plan lector eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el plan lector: ' . $e->getMessage());
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
            $plan->estado = !$plan->estado;
            $plan->save();

            $mensaje = $plan->estado ? 'Plan lector activado correctamente.' : 'Plan lector inactivado correctamente.';
            return back()->with('success', $mensaje);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cambiar el estado del plan lector: ' . $e->getMessage());
        }
    }
}
