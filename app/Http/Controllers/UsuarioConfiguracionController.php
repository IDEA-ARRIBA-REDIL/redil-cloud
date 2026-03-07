<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\TipoUsuario;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Storage;

class UsuarioConfiguracionController extends Controller
{
  public function listar(Request $request)
  {
    $buscar = $request->input('buscar');

    $tipoUsuarios = TipoUsuario::query()
      ->when($buscar, function ($query, $buscar) {
        return $query->where('nombre', 'like', '%' . $buscar . '%')
          ->orWhere('descripcion', 'like', '%' . $buscar . '%');
      })
      ->orderby('orden')
      ->paginate(12);

    return view('contenido.paginas.tipo-usuarios.listar', [
      'tipoUsuarios' => $tipoUsuarios,
      'buscar' => $buscar
    ]);
  }

  public function creacion()
  {
    $tiposUsuarios = TipoUsuario::all();

    return view('contenido.paginas.tipo-usuarios.creacion', [
      'tiposUsuarios' => $tiposUsuarios
    ]);
  }

  public function crear(Request $request)
  {
    $configuracion = Configuracion::find(1);

    $request->validate([
      'nombre' => 'required|string|max:50',
      'descripcion' => 'nullable|string|max:200',
      'color' => 'nullable|string|max:10',
      'icono' => 'nullable|string|max:100', // ahora es string
      'imagen' => 'nullable|file|mimes:png',
      'nombre_plural' => 'nullable|string|max:50',
      'id_rol_dependiente' => 'nullable|string',
      'puntaje' => 'nullable|integer',
      'orden' => 'nullable|integer',
    ]);

    $data = $request->only([
      'nombre',
      'descripcion',
      'color',
      'icono',
      'nombre_plural',
      'id_rol_dependiente',
      'orden',
      'puntaje'
    ]);

    // Booleanos
    $data['tipo_pastor'] = $request->has('tipo_pastor') ? 1 : 0;
    $data['tipo_pastor_principal'] = $request->has('tipo_pastor_principal') ? 1 : 0;
    $data['seguimiento_actividad_grupo'] = $request->has('seguimiento_actividad_grupo') ? 1 : 0;
    $data['seguimiento_actividad_reunion'] = $request->has('seguimiento_actividad_reunion') ? 1 : 0;
    $data['habilitado_para_consolidacion'] = $request->has('habilitado_para_consolidacion') ? 1 : 0;
    $data['puntaje'] = $request->puntaje ?? 0;
    $data['visible'] = $request->has('visible') ? 1 : 0;
    $data['default'] = $request->has('default') ? 1 : 0;

    // Crear el registro primero
    $tipoUsuario = TipoUsuario::create($data);

    // Manejo de imagen
    if ($request->hasFile('imagen')) {
      $file = $request->file('imagen');

      [$width, $height] = getimagesize($file->getPathname());
      if ($width != 100 || $height != 100) {
        return back()->withErrors(['imagen' => 'La imagen debe ser de 100x100 píxeles.']);
      }

      $nombreImagen = 'imagen-' . $tipoUsuario->id . '.png';

      $file->storeAs(
        $configuracion->ruta_almacenamiento . '/img/tipos-usuarios',
        $nombreImagen,
        'public'
      );

      $tipoUsuario->imagen = $nombreImagen;
      $tipoUsuario->save();
    }

    return redirect()->route('tipo-usuario.listar')
      ->with('success', 'Tipo de Usuario creado correctamente.');
  }

  public function editar(TipoUsuario $tipoUsuario)
  {
    $tiposUsuarios = TipoUsuario::all();

    return view('contenido.paginas.tipo-usuarios.editar', [
      'tipoUsuario' => $tipoUsuario,
      'tiposUsuarios' => $tiposUsuarios
    ]);
  }


  /**
   * Actualiza un Tipo de Usuario existente en la base de datos.
   */
  public function actualizar(Request $request, TipoUsuario $tipoUsuario)
  {
    // 1. Obtener configuración (si es necesaria para la ruta)
    $configuracion = Configuracion::find(1);

    // 2. Validación completa de todos los campos
    $validatedData = $request->validate([
      'nombre' => 'required|string|max:50',
      'descripcion' => 'nullable|string|max:200',
      'color' => 'nullable|string|max:10',
      'icono' => 'nullable|string|max:100',
      'estado' => 'nullable|string|max:100',
      'nombre_plural' => 'nullable|string|max:50',
      'id_rol_dependiente' => 'nullable|string',
      'puntaje' => 'nullable|integer',
      'orden' => 'nullable|integer',
      'imagen' => [ // ¡Mejora! Validación de imagen integrada
        'nullable',
        'file',
        'mimes:png',
      ],
    ]);

    // 3. Asignar los datos validados al modelo
    $tipoUsuario->fill($validatedData);

    // 4. Manejar los campos booleanos (checkboxes) por separado
    $tipoUsuario->tipo_pastor = $request->has('tipo_pastor');
    $tipoUsuario->tipo_pastor_principal = $request->has('tipo_pastor_principal');
    $tipoUsuario->seguimiento_actividad_grupo = $request->has('seguimiento_actividad_grupo');
    $tipoUsuario->seguimiento_actividad_reunion = $request->has('seguimiento_actividad_reunion');
    $tipoUsuario->habilitado_para_consolidacion = $request->has('habilitado_para_consolidacion');
    $tipoUsuario->visible = $request->has('visible');
    $tipoUsuario->default = $request->has('default');

    // 5. Procesar la subida de la nueva imagen si se envió una
    if ($request->hasFile('imagen')) {
      $file = $request->file('imagen');
      $basePath = $configuracion->ruta_almacenamiento . '/tipos-usuarios';
      $nombreImagen = 'imagen-' . $tipoUsuario->id . '.png';

      // Eliminar la imagen anterior del servidor si existía
      if ($tipoUsuario->imagen) {
        // ¡Mejora! Forma correcta y segura de eliminar el archivo
        Storage::disk('public')->delete($basePath . '/img/tipos-usuarios' . $tipoUsuario->imagen);
      }

      // Guardar la nueva imagen en el servidor
      $file->storeAs($basePath, $nombreImagen, 'public');

      // Actualizar el nombre de la imagen en el modelo
      $tipoUsuario->imagen = $nombreImagen;
    }

    // 6. Guardar todos los cambios en la base de datos
    $tipoUsuario->save();

    // 7. Redirigir con un mensaje de éxito
    return redirect()->route('tipo-usuario.listar')
      ->with('success', 'Tipo de Usuario actualizado correctamente.');
  }

  public function eliminar(TipoUsuario $tipoUsuario)
  {
    $tipoUsuario->delete();

    return redirect()->route('tipo-usuario.listar')
      ->with('success', 'Tipo de Usuario eliminado correctamente.');
  }
}
