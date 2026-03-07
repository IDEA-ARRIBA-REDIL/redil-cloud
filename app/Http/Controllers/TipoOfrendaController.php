<?php

namespace App\Http\Controllers;

use App\Models\TipoOfrenda;
use Illuminate\Http\Request;

class TipoOfrendaController extends Controller
{
  public function listar()
  {
    $tiposOfrendas = TipoOfrenda::all();
    return view('contenido.paginas.tipo-ofrenda.listar', [
      'tiposOfrendas' => $tiposOfrendas
    ]);
  }

  // Guardar creación
  public function crear(Request $request)
  {
    $validated = $request->validate([
      'descripcion' => 'required|string',
      'nombre' => 'required|string|max:255',
      'codigo_sap' => 'nullable|string|max:255',
      'generica' => 'nullable|boolean',
      'formulario_donaciones' => 'nullable|boolean',
      'tipo_reunion' => 'nullable|boolean',
      'ofrenda_obligatoria' => 'nullable|boolean',
    ]);

    $tipoOfrenda = new TipoOfrenda();
    $tipoOfrenda->descripcion = $validated['descripcion'];
    $tipoOfrenda->generica = $validated['generica'] ?? false; // 👈 cambio aquí
    $tipoOfrenda->nombre = $validated['nombre'];
    $tipoOfrenda->codigo_sap = $validated['codigo_sap'] ?? null;

    // normalizamos los booleanos opcionales (checkboxes)
    $tipoOfrenda->formulario_donaciones = $request->has('formulario_donaciones');
    $tipoOfrenda->tipo_reunion = $request->has('tipo_reunion');
    $tipoOfrenda->ofrenda_obligatoria = $request->has('ofrenda_obligatoria');

    $tipoOfrenda->save();

    return redirect()->route('tipo-ofrenda.listar')
      ->with('success', 'Tipo de ofrenda creada correctamente.');
  }

  // Actualizar Tipo de Ofrenda
  public function actualizar(Request $request, TipoOfrenda $tipoOfrenda)
  {
    $request->validate([
      'descripcion' => 'required|string',
      'generica' => 'required|boolean',
      'nombre' => 'required|string|max:255',
      'formulario_donaciones' => 'nullable|boolean',
      'codigo_sap' => 'required|string|max:255',
      'tipo_reunion' => 'nullable|boolean',
      'ofrenda_obligatoria' => 'nullable|boolean',
    ]);

    $tipoOfrenda->descripcion = $request->input('descripcion');
    $tipoOfrenda->generica = $request->boolean('generica');
    $tipoOfrenda->nombre = $request->input('nombre');
    $tipoOfrenda->formulario_donaciones = $request->has('formulario_donaciones');
    $tipoOfrenda->codigo_sap = $request->input('codigo_sap');
    $tipoOfrenda->tipo_reunion = $request->has('tipo_reunion');
    $tipoOfrenda->ofrenda_obligatoria = $request->has('ofrenda_obligatoria');

    $tipoOfrenda->save();

    return redirect()
      ->route('tipo-ofrenda.listar')
      ->with('success', 'Tipo de ofrenda actualizada correctamente.');
  }

  public function eliminar(TipoOfrenda $tipoOfrenda)
  {
    $tipoOfrenda->delete();
    return redirect()->route('tipo-ofrenda.listar')->with('success', 'Tipo de ofrenda eliminada correctamente.');
  }
}
