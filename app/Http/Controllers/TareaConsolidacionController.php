<?php

namespace App\Http\Controllers;

use App\Models\TareaConsolidacion;
use Illuminate\Http\Request;

class TareaConsolidacionController extends Controller
{
  /**
   * Muestra una lista de todas las tareas de consolidación.
   */
  public function listarTareasConsolidacion()
  {
    // Obtenemos todos los registros, ordenados por el campo 'orden'.
    $tareas = TareaConsolidacion::orderBy('orden', 'asc')->get();

    // Retornamos la vista principal del CRUD (asegúrate de que esta ruta sea la correcta)
    return view('contenido.paginas.tarea-consolidacion.listar-tarea-consolidacion', compact('tareas'));
  }

  /**
   * Guarda una nueva tarea de consolidación en la base de datos.
   */
  public function crearTareaConsolidacion(Request $request)
  {
    // 1. Validar los datos.
    $validatedData = $request->validate([
      'nombre' => 'required|string|max:50',
      'descripcion' => 'nullable|string|max:200',
      'orden' => 'nullable|integer',
      // No validamos 'default' aquí, lo manejaremos por separado.
    ]);

    // 2. Manejar el checkbox 'default'.
    // Si el checkbox está marcado, $request->has('default') será true (1).
    // Si no está marcado, será false (0).
    $validatedData['default'] = $request->has('default');

    // 3. Crear el nuevo registro.
    TareaConsolidacion::create($validatedData);

    // 4. Redirigir de vuelta con un mensaje de éxito.
    return redirect()->route('tareas-consolidacion.listarTareasConsolidacion')
      ->with('success', 'Tarea de consolidación creada exitosamente.');
  }

  /**
   * Actualiza una tarea de consolidación existente.
   */
  public function actualizarTareaConsolidacion(Request $request, TareaConsolidacion $tarea)
  {
    // 1. Validar los datos.
    $validatedData = $request->validate([
      'nombre' => 'required|string|max:50',
      'descripcion' => 'nullable|string|max:200',
      'orden' => 'nullable|integer',
    ]);

    // 2. Manejar el checkbox 'default'.
    $validatedData['default'] = $request->has('default');

    // 3. Actualizar el registro.
    $tarea->update($validatedData);

    // 4. Redirigir de vuelta.
    return redirect()->route('tareas-consolidacion.listarTareasConsolidacion')
      ->with('success', 'Tarea de consolidación actualizada exitosamente.');
  }

  /**
   * Elimina una tarea de consolidación de la base de datos.
   */
  public function eliminarTareaConsolidacion(TareaConsolidacion $tarea)
  {
    // 1. Eliminar el registro.
    $tarea->delete();

    // 2. Redirigir de vuelta.
    return redirect()->route('tareas-consolidacion.listarTareasConsolidacion')
      ->with('success', 'Tarea de consolidación eliminada exitosamente.');
  }
}
