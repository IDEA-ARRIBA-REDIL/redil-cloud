<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
  public function index()
  {
    $productos = Producto::all();
    return view('contenido.paginas.productos.index', compact('productos'));
  }

  public function create(Request $request)
  {
    $request->validate([
      'nombre' => 'required|string|max:250',
      'descripcion' => 'nullable|string',
      'precio' => 'required |numeric|between:0,999999999999.9999|decimal:1,4',
    ]);

    // Crear el producto con los nombres correctos en la base de datos
    Producto::create([
      'nombre' => $request->nombre,
      'descripcion' => $request->descripcion,
      'precio' => $request->precio,
    ]);

    return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
  }

  public function store(Request $request)
  {
    return view('contenido.paginas.productos.create');
  }

  public function show(Producto $producto)
  {
    return view(
      'contenido.paginas.productos.show',
      [
        'producto' => $producto,
      ]
    );
  }

  public function edit(Producto $producto)
  {
    return view('contenido.paginas.productos.edit', compact('producto'));
  }

  public function update(Request $request, Producto $producto)
  {
    $request->validate([
      'nombre' => 'required|string|max:250',
      'descripcion' => 'nullable|string',
      'precio' => 'required|numeric|between:0,999999999999.9999|decimal:1,4',
    ]);

    // Asigna los valores
    $producto->nombre = $request->nombre;
    $producto->descripcion = $request->descripcion;
    $producto->precio = $request->precio;

    // Guarda los cambios
    $producto->save();

    return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
  }

  public function destroy(Producto $producto)
  {
    $producto->delete();
    return redirect()->route('productos.index')->with('exitosamente', 'Producto Eliminado');
  }
}
