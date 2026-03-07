<?php

namespace App\Http\Controllers;

use App\Models\RangoEdad;
use Illuminate\Http\Request;

class RangoEdadController extends Controller
{
  // Listar rangos
  public function listar()
  {
    $rangos = RangoEdad::all();
    return view('contenido.paginas.rangos-edad.listar', compact('rangos'));
  }

  // Mostrar formulario de creación
  public function rangoDeEdad()
  {
    return view('contenido.paginas.rangos-edad.create');
  }

  // Guardar nuevo rango
  public function crearRangoDeEdad(Request $request)
  {
    $validated = $request->validate([
      'nombre' => 'nullable|string|max:30',
      'configuracion_id' => 'required|integer',
      'descripcion' => 'nullable|string|max:100',
      'edad_maxima' => 'required|integer|min:0',
      'edad_minima' => 'required|integer|min:0|lte:edad_maxima',
    ]);

    RangoEdad::create($validated);

    return redirect()->route('rangos-edad.listar')->with('success', 'Rango de edad creado correctamente.');
  }

  // Mostrar formulario de edición
  public function editarRangoDeEdad(RangoEdad $rango)
  {
    return view('contenido.paginas.rangos-edad.edit', compact('rango'));
  }

  // Actualizar rango
  public function actualizarRangoDeEdad(Request $request, RangoEdad $rango)
  {
    $validated = $request->validate([
      'nombre' => 'nullable|string|max:30',
      'configuracion_id' => 'required|integer',
      'descripcion' => 'nullable|string|max:100',
      'edad_maxima' => 'required|integer|min:0',
      'edad_minima' => 'required|integer|min:0|lte:edad_maxima',
    ]);

    $rango->update($validated);

    return redirect()->route('rangos-edad.listar')->with('success', 'Rango de edad actualizado correctamente.');
  }

  // Eliminar rango
  public function eliminarRangoDeEdad(RangoEdad $rango)
  {
    $rango->delete();

    return redirect()->route('rangos-edad.listar')->with('success', 'Rango de edad eliminado correctamente.');
  }
}
