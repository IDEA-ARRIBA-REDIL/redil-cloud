<?php

namespace App\Http\Controllers;

use App\Models\EstadoCivil;
use App\Models\EstadoTareaConsolidacion;
use App\Models\FiltroConsolidacion;
use App\Models\TareaConsolidacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FiltroConsolidacionController extends Controller
{
  /**
   * Muestra una lista de todos los filtros de consolidación. */
  public function listarFiltrosConsolidacion()
  {
    $filtros = FiltroConsolidacion::with('condiciones')
      ->orderBy('orden', 'asc')
      ->get();

    $tareasDisponibles = TareaConsolidacion::orderBy('nombre', 'asc')->get();

    // Obtener todos los estados disponibles
    $estados = EstadoTareaConsolidacion::orderBy('nombre')->get(); // <-- FETCH ESTADOS

    $estadosCiviles = EstadoCivil::orderBy('nombre')->get();

    return view('contenido.paginas.filtro-consolidacion.listar-filtros-consolidacion', [
      'filtros' => $filtros,
      'tareasDisponibles' => $tareasDisponibles,
      'estadosCiviles' => $estadosCiviles,
      'estados' => $estados // <-- PASS ESTADOS TO VIEW
    ]);
  }

  /**
   * Guarda un nuevo filtro de consolidación en la base de datos. */
  public function crearFiltroConsolidacion(Request $request)
  {
    // 1. Validar los datos que vienen del formulario de creación.
    $validatedData = $request->validate([
      'nombre' => 'required|string|max:100',
      'descripcion' => 'nullable|string|max:255',
      'orden' => 'required|integer',
      'estados_civiles' => 'nullable|array', // Validar que es un array (si se envía)
      'estados_civiles.*' => 'exists:estados_civiles,id' // Validar que cada ID exista
    ]);

    // Usamos una transacción para asegurar la integridad de los datos
    DB::beginTransaction();
    try {
        // 2. Crear el filtro principal
        $filtro = FiltroConsolidacion::create([
            'nombre' => $validatedData['nombre'],
            'descripcion' => $validatedData['descripcion'],
            'orden' => $validatedData['orden'],
        ]);

        // 3. (NUEVO) Sincronizar la relación
        // Usamos sync() para adjuntar los IDs.
        if ($request->has('estados_civiles')) {
            $filtro->estadosCiviles()->sync($validatedData['estados_civiles']);
        }

        DB::commit(); // Todo salió bien
    } catch (\Exception $e) {
        DB::rollBack(); // Algo salió mal, deshacemos
        return redirect()->back()->with('danger', 'Error al crear el filtro, por favor intenta de nuevo.');
    }

    // 3. Redirigir al usuario de vuelta a la lista con un mensaje de éxito.
    return redirect()->route('filtros-consolidacion.listarFiltrosConsolidacion')
      ->with('success', 'Filtro de consolidación creado exitosamente.');
  }

  /**
   * Actualiza un filtro de consolidación existente en la base de datos. */
  public function actualizarFiltroConsolidacion(Request $request, FiltroConsolidacion $filtro)
  {
    $validatedData = $request->validate([
      'nombre' => 'required|string|max:100',
      'descripcion' => 'nullable|string|max:255',
      'orden' => 'required|integer',
      'estados_civiles' => 'nullable|array', // Validar que es un array
      'estados_civiles.*' => 'exists:estados_civiles,id'
    ]);

    DB::beginTransaction();
    try {
        $filtro->update([
            'nombre' => $validatedData['nombre'],
            'descripcion' => $validatedData['descripcion'],
            'orden' => $validatedData['orden'],
        ]);

        $estadosASincronizar = $request->input('estados_civiles', []);
        $filtro->estadosCiviles()->sync($estadosASincronizar);

        DB::commit(); // Todo salió bien
    } catch (\Exception $e) {
        DB::rollBack(); // Algo salió mal
        return redirect()->back()->with('error', 'Error al actualizar el filtro: ' . $e->getMessage());
    }

    return redirect()->route('filtros-consolidacion.listarFiltrosConsolidacion')
      ->with('success', 'Filtro de consolidación actualizado exitosamente.');
  }

  /**
   * Elimina un filtro de consolidación de la base de datos. */
  public function eliminarFiltroConsolidacion(FiltroConsolidacion $filtro)
  {
    // 1. Eliminar el registro.
    $filtro->delete();

    // 2. Redirigir al usuario de vuelta a la lista con un mensaje de éxito.
    return redirect()->route('filtros-consolidacion.listarFiltrosConsolidacion')
      ->with('success', 'Filtro de consolidación eliminado exitosamente.');
  }

  /**
   * Asocia una Tarea a un Filtro.
   */
  public function asignarTarea(Request $request, FiltroConsolidacion $filtro, TareaConsolidacion $tarea)
  {
    $request->validate([
      'estado_tarea_consolidacion_id' => 'required|exists:estados_tarea_consolidacion,id',
      'incluir' => 'nullable|boolean',
    ]);

    $estadoId = $request->input('estado_tarea_consolidacion_id');
    $incluirValue = $request->input('incluir', false); // `false` si no se envía

    // ... (lógica para verificar duplicados si es necesario) ...

    try {
      $filtro->condiciones()->attach($tarea->id, [
        'estado_tarea_consolidacion_id' => $estadoId,
        'incluir'                       => $incluirValue,
      ]);

      session()->flash('expandCollapseFiltroId', $filtro->id); // Añadido flash para auto-expandir
      return response()->json(['success' => true, 'message' => 'Tarea asignada.']);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => "Ups!"], 500);
    }
  }

  /**
   * Desasocia una Tarea de un Filtro.
   */
  public function desasignarTarea(Request $request, FiltroConsolidacion $filtro, TareaConsolidacion $tarea)
  {
    try {
      $detachedCount = $filtro->condiciones()->detach($tarea->id);

      if ($detachedCount > 0) {
        // Almacena en la sesión flash para que JS lo lea al recargar
        session()->flash('expandCollapseFiltroId', $filtro->id);
        return response()->json(['success' => true, 'message' => 'Tarea desasignada correctamente.']);
      } else {
        return response()->json(['success' => false, 'message' => 'La tarea no estaba asignada a este filtro.'], 404);
      }
    } catch (\Exception $e) {
      Log::error("Error al desasignar tarea [AJAX]: " . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Error al desasignar la tarea.'], 500);
    }
  }
}
