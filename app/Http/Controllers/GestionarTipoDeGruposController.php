<?php

namespace App\Http\Controllers;

use App\Models\TipoGrupo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Storage;

class GestionarTipoDeGruposController extends Controller
{
  /**
   * Muestra la lista de tipos de grupos para modificar.
   */
  public function listar(Request $request)
  {
    $buscar = $request->input('buscar');

    $tiposGrupos = TipoGrupo::query()
      ->when($buscar, function ($query, $buscar) {
        return $query->where('nombre', 'like', '%' . $buscar . '%')
          ->orWhere('descripcion', 'like', '%' . $buscar . '%');
      })
      ->orderby('orden')
      ->paginate(12);

    $configuracion = Configuracion::find(1);

    return view('contenido.paginas.gestionar-tipos-de-grupos.gestionar-tipos-de-grupos', [
      'tiposGrupos' => $tiposGrupos,
      'configuracion' => $configuracion,
      'buscar' => $buscar
    ]);
  }

  /**
   * Muestra el formulario para crear un nuevo tipo de grupo.
   */
  public function nuevo()
  {
    $tiposGrupos = TipoGrupo::all();

    // Aquí devuelves la vista con el formulario para crear un nuevo grupo
    return view('contenido.paginas.gestionar-tipos-de-grupos.crear-tipos-de-grupos', [
      'tiposGrupos' => $tiposGrupos
    ]);
  }

  /**
   * Muestra el formulario para editar un tipo de grupo existente.
   * El {tipoGrupo} en la ruta se inyecta aquí automáticamente.
   */
  public function editarTipoDeGrupo(TipoGrupo $tipoGrupo)
  {
    // Devuelve LA MISMA VISTA de formulario, pero esta vez le pasa el objeto $tipoGrupo.
    return view('contenido.paginas.gestionar-tipos-de-grupos.modificar-tipos-de-grupos', [
      'tipoGrupo' => $tipoGrupo
    ]);
  }

  /**
   * Elimina un tipo de grupo de la base de datos.
   * El {tipoGrupo} en la ruta se inyecta aquí automáticamente.
   */
  public function cambiarEstadoTipoDeGrupo(TipoGrupo $tipoGrupo)
  {
    $tipoGrupo->update(['estado' => !$tipoGrupo->estado]);

    $estado = $tipoGrupo->estado ? 'activado' : 'desactivado';

    return redirect()
      ->route('gestionar-tipos-de-grupos.listar')
      ->with('success', 'El tipo de grupo "' . $tipoGrupo->nombre . '" fue ' . $estado . ' correctamente.');
  }

  // Aquí irían tus otros métodos como crearTipoDeGrupo y actualizarTipoDeGrupo...

  public function crearTipoDeGrupo(Request $request)
  {
    $configuracion = Configuracion::find(1);

    // 🔹 1. Validación
    $validatedData = $request->validate([
      'nombre' => 'required|string|max:50',
      'nombre_plural' => 'nullable|string|max:35',
      'imagen' => 'nullable|image|mimes:png|dimensions:width=100,height=100',
      'geo_icono' => 'nullable|string|max:50',
      'descripcion' => 'nullable|string|max:200',
      'color' => 'nullable|string|max:10',
      'orden' => 'nullable|integer',
      'metros_cobertura' => 'nullable|integer',
      'cantidad_maxima_reportes_semana' => 'nullable|integer',
      'tiempo_para_definir_inactivo_grupo' => 'nullable|integer',
      'horas_disponiblidad_link_asistencia' => 'nullable|integer',
      'titulo1_finalizar_reporte' => 'nullable|string|max:255',
      'subtitulo_encargados_finalizar_reporte' => 'nullable|string|max:255',
      'subtitulo_sumatorias_adiccionales_finalizar_reporte' => 'nullable|string|max:255',
      'subtitulo_miembros_finalizar_reporte' => 'nullable|string|max:255',
      'subtitulo_ofrendas_finalizar_reporte' => 'nullable|string|max:255',
      'descripcion1_finalizar_reporte' => 'nullable|string',
      'descripcion_ofrendas_finalizar_reporte' => 'nullable|string',
      'mensaje_bienvenida' => 'nullable|string',
    ]);

    // 🔹 2. Crear el registro base (sin imagen)
    $tipoGrupo = new TipoGrupo($validatedData);

    // 🔹 3. Checkboxes (valores booleanos)
    $tipoGrupo->seguimiento_actividad = $request->has('seguimiento_actividad');
    $tipoGrupo->contiene_servidores = $request->has('contiene_servidores');
    $tipoGrupo->posible_grupo_sede = $request->has('posible_grupo_sede');
    $tipoGrupo->ingresos_individuales_discipulos = $request->has('ingresos_individuales_discipulos');
    $tipoGrupo->ingresos_individuales_lideres = $request->has('ingresos_individuales_lideres');
    $tipoGrupo->registra_datos_planeacion = $request->has('registra_datos_planeacion');
    $tipoGrupo->servidores_solo_discipulos = $request->has('servidores_solo_discipulos');
    $tipoGrupo->visible_mapa_asignacion = $request->has('visible_mapa_asignacion');
    $tipoGrupo->tipo_evangelistico = $request->has('tipo_evangelistico');
    $tipoGrupo->enviar_mensaje_bienvenida = $request->has('enviar_mensaje_bienvenida');
    $tipoGrupo->sumar_encargado_asistencia_grupo = $request->has('sumar_encargado_asistencia_grupo');
    $tipoGrupo->registrar_inasistencia = $request->has('registrar_inasistencia');
    $tipoGrupo->inasistencia_obligatoria = $request->has('inasistencia_obligatoria');
    $tipoGrupo->estado = true;

    // 🔹 4. Manejar imagen si fue cargada
    if ($request->hasFile('imagen')) {
      $file = $request->file('imagen');

      // Generar nombre único
      $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
        . '_' . time() . '.png';

      // Ruta base configurada
      $rutaBase = $configuracion->ruta_almacenamiento . '/img/tipo-grupos/imagenes';

      // Guardar en storage/public
      $file->storeAs($rutaBase, $filename, 'public');

      // Actualizar campo imagen con la ruta completa
      $tipoGrupo->imagen = $rutaBase . '/' . $filename;
    }

    // 🔹 5. Guardar primero para generar el ID
    $tipoGrupo->save();

    // 🔹 6. Redirigir con mensaje de éxito
    return redirect()
      ->route('gestionar-tipos-de-grupos.listar')
      ->with('success', 'Tipo de grupo "' . $tipoGrupo->nombre . '" creado correctamente.');
  }

  /**
   * Procesa la actualización de un tipo de grupo.
   * Nota el cambio en los parámetros: ahora recibe el Request y el TipoGrupo
   */
  public function actualizarTipoDeGrupo(Request $request, TipoGrupo $tipoGrupo)
  {
    $configuracion = Configuracion::find(1);

    $request->validate([
      'nombre' => 'required|string|max:50',
      'nombre_plural' => 'nullable|string|max:35',
      'imagen' => 'nullable|image|dimensions:width=100,height=100',
      'geo_icono' => 'nullable|string|max:50',
      'descripcion' => 'nullable|string|max:200',
      'color' => 'nullable|string|max:10',
      'orden' => 'nullable|integer',
      'metros_cobertura' => 'nullable|integer',
      'cantidad_maxima_reportes_semana' => 'nullable|integer',
      'tiempo_para_definir_inactivo_grupo' => 'nullable|integer',
      'horas_disponiblidad_link_asistencia' => 'nullable|integer',
      'titulo1_finalizar_reporte' => 'nullable|string|max:255',
      'subtitulo_encargados_finalizar_reporte' => 'nullable|string|max:255',
      'subtitulo_sumatorias_adiccionales_finalizar_reporte' => 'nullable|string|max:255',
      'subtitulo_miebros_finalizar_reporte' => 'nullable|string|max:255',
      'subtitulo_ofrendas_finalizar_reporte' => 'nullable|string|max:255',
      'descripcion1_finalizar_reporte' => 'nullable|string',
      'descripcion_ofrendas_finalizar_reporte' => 'nullable|string',
      'mensaje_bienvenida' => 'nullable|string',
    ]);

    $tipoGrupo->nombre = $request->nombre;
    $tipoGrupo->nombre_plural = $request->nombre_plural;
    $tipoGrupo->geo_icono = $request->geo_icono;
    $tipoGrupo->descripcion = $request->descripcion;
    $tipoGrupo->color = $request->descripcion;
    $tipoGrupo->orden = $request->orden;
    $tipoGrupo->metros_cobertura = $request->metros_cobertura;
    $tipoGrupo->cantidad_maxima_reportes_semana = $request->cantidad_maxima_reportes_semana;
    $tipoGrupo->tiempo_para_definir_inactivo_grupo = $request->tiempo_para_definir_inactivo_grupo;
    $tipoGrupo->horas_disponiblidad_link_asistencia = $request->horas_disponiblidad_link_asistencia;
    $tipoGrupo->titulo1_finalizar_reporte = $request->titulo1_finalizar_reporte;
    $tipoGrupo->subtitulo_encargados_finalizar_reporte = $request->subtitulo_encargados_finalizar_reporte;
    $tipoGrupo->subtitulo_sumatorias_adiccionales_finalizar_reporte = $request->subtitulo_sumatorias_adiccionales_finalizar_reporte;
    $tipoGrupo->subtitulo_miembros_finalizar_reporte = $request->subtitulo_miembros_finalizar_reporte;
    $tipoGrupo->subtitulo_ofrendas_finalizar_reporte = $request->subtitulo_ofrendas_finalizar_reporte;
    $tipoGrupo->descripcion1_finalizar_reporte = $request->descripcion1_finalizar_reporte;
    $tipoGrupo->descripcion_ofrendas_finalizar_reporte = $request->descripcion_ofrendas_finalizar_reporte;
    $tipoGrupo->mensaje_bienvenida = $request->mensaje_bienvenida;

    // 🔹 Manejo de archivos (guardar en storage/app/public/tipo_grupos)
    if ($request->hasFile('imagen')) {
      $file = $request->file('imagen');
      $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
        . '_' . time() . '.png';

      $file->storeAs($configuracion->ruta_almacenamiento . '/img/tipo-grupos/imagenes', $filename, 'public');
      $tipoGrupo->imagen = $configuracion->ruta_almacenamiento . '/img/tipo-grupos/imagenes/' . $filename;
    }

    // 🔹 Actualizar el registro
    $tipoGrupo->save();

    return redirect()->route('gestionar-tipos-de-grupos.listar')
      ->with('success', 'El tipo de grupo "' . $tipoGrupo->nombre . '" fue actualizado correctamente.');
  }
}
