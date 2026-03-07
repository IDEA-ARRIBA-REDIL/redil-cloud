<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\ClasificacionAsistente;
use App\Models\Configuracion;
use App\Models\RangoEdad;
use App\Models\Reunion;
use App\Models\TipoOfrenda;
use App\Models\Sede;
use App\Models\TipoUsuario;
use Illuminate\Http\Request;
use Carbon\Carbon;
use stdClass;

class ReunionesController extends Controller
{
  public function nueva()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reuniones.subitem_nueva_reunion');

    $configuracion = Configuracion::first();
    $sedes = Sede::get();
    $tiposEdades = RangoEdad::get();
    $ofrendas = TipoOfrenda::get();
    $tipoUsuarios = TipoUsuario::select('id', 'nombre')->orderBy('nombre', 'asc')->get();
    $clasificacionesAsistentes = ClasificacionAsistente::select('id', 'nombre')->orderBy('nombre', 'desc')->get();

    return view('contenido.paginas.reuniones.nueva', [
      'clasificacionesAsistentes' => $clasificacionesAsistentes,
      'configuracion' => $configuracion,
      'sedes' => $sedes,
      'tiposEdades' => $tiposEdades,
      'ofrendas' => $ofrendas,
      'tipoUsuarios' => $tipoUsuarios,
    ]);
  }

  public function crear(Request $request)
  {
    $configuracion = Configuracion::first();
    $validate = [
      'nombre' => 'required',
      //'día' => 'required',
      'hora' => 'required',
      'díasDePlazo' => 'required',
      'LaSede' => 'required|exists:sedes,id',
    ];

    if ($request->habilitarReserva) {
      $validate = array_merge($validate, [
        'díasPlazoReserva' => ['required', 'numeric', 'min:0'],
        'aforo' => ['required', 'numeric', 'min:0'],
      ]);
      if ($request->habilitarReservaInvitados) {
        $validate = array_merge($validate, [
          'cantidadInvitados' => ['required', 'numeric', 'min:0']
        ]);
      }
    }

    $request->validate($validate);

    // Crear la reunión
    $reunion = new Reunion();
    //$reunion->dia = $request->día;
    $reunion->hora = $request->hora;
    $reunion->aforo = $request->aforo;
    $reunion->nombre = $request->nombre;
    $reunion->sede_id = $request->LaSede;
    $reunion->descripcion = $request->descripción;
    $reunion->genero = json_encode($request->sexos);
    $reunion->dias_plazo_reporte = $request->díasDePlazo;
    $reunion->dias_plazo_reserva = $request->díasPlazoReserva;
    $reunion->hora_maxima_reportar_asistencia = $request->horaMáxima;
    $reunion->solo_reservados_pueden_asistir = $request->soloReservaronAsistir;
    $reunion->habilitar_reserva = $request->habilitarReserva;
    $reunion->habilitar_reserva_invitados = $request->habilitarReservaInvitados;
    $reunion->cantidad_maxima_reserva_invitados = $request->cantidadInvitados;
    $reunion->habilitar_reserva_familiares = $request->habilitarReservaFamiliares;
    $reunion->habilitar_preregistro_iglesia_infantil = $request->habilitarPreregistroInfantil ? true : false;
    $reunion->portada = 'default.png';
    $reunion->save();

    // Sincronizar relaciones
    $reunion->sedes()->attach($request->sedesAsistentes);
    $reunion->rangosEdades()->attach($request->rangosEdad);
    $reunion->tiposOfrendas()->attach($request->ofrendas);
    $reunion->tipoUsuarios()->attach($request->tipoUsuarios);
    $reunion->clasificacionesAsistentes()->attach($request->clasificacionAsistentes);

    if ($reunion->save()) {

      // AÑADO LA PORTADA
      if ($request->foto) {
        if ($configuracion->version == 1) {
          $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/reuniones/');
          !is_dir($path) && mkdir($path, 0777, true);

          $imagenPartes = explode(';base64,', $request->foto);
          $imagenBase64 = base64_decode($imagenPartes[1]);
          $nombreFoto = 'reunion' . $reunion->id . '.png';
          $imagenPath = $path . $nombreFoto;
          file_put_contents($imagenPath, $imagenBase64);
          $reunion->portada = $nombreFoto;
          $reunion->save();
        } else {
          /*
          $s3 = AWS::get('s3');
          $s3->putObject(array(
            'Bucket'     => $_ENV['aws_bucket'],
            'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
            'SourceFile' => "img/temp/".Input::get('foto-hide'),
          ));*/
        }
      }
    }

    return redirect()->route('reuniones.nueva')->with('success', 'Reunion creada exitosamente.');

  }

  public function lista(Request $request, $tipo = 'todos')
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reuniones.subitem_lista_reuniones');

    $bandera = 0;
    $tagsBusqueda = [];
    $contadorBaja = 0;
    $contadorTodos = 0;
    $textoBusqueda = '';
    $meses = Helpers::meses('largo');
    //$dias = Helpers::diasDeLaSemana();
    $sedes = Sede::select('id', 'nombre')->get();

    if ($rolActivo->hasPermissionTo('reuniones.lista_reuniones_todas')) {
      if ($tipo == 'todos') {
        $reuniones = Reunion::get();
      } else {
        $reuniones = Reunion::withTrashed()->whereNot('deleted_at', null)->get();
      }
      $contadorTodos = Reunion::select('id')->count();
      $contadorBaja = Reunion::withTrashed()->whereNot('deleted_at', null)->select('id')->count();
    } else {
      // Creo la logica para cuando no tenga
      $user = auth()->user();
      $sedesEncargadasArray = $user->sedesEncargadas('array');
      if (!empty($sedesEncargadasArray)) {
        // Obtener el usuario autenticado
        // Llamar el método con el parámetro 'array'
        if ($tipo == "todos") {
          $reuniones = Reunion::whereIn('sede_id', $sedesEncargadasArray)->get();
          $tipo = "todos";
        } else if ($tipo == "baja") {
          $reuniones = Reunion::onlyTrashed()->whereIn('sede_id', $sedesEncargadasArray)->get();
          $tipo = "baja";
        }
      } else {
        $reuniones = Reunion::whereRaw('1=2');
      }
      $sedes = Sede::whereIn('id', $sedesEncargadasArray)->select('id', 'nombre')->get();
      $contadorTodos = Reunion::whereIn('sede_id', $sedesEncargadasArray)->select('id')->count();
      $contadorBaja = Reunion::onlyTrashed()->whereIn('sede_id', $sedesEncargadasArray)->select('id')->count();
    }

    $buscar = $request->input('buscar'); // Es mejor usar input() para obtener datos de la request

    // Busqueda por palabra clave
    if ($buscar != '') {
      $terminoBusquedaLimpio = trim(Helpers::sanearStringConEspacios(str_replace(["'"], '', $buscar)));
      $terminoBusquedaLimpio = htmlspecialchars($terminoBusquedaLimpio);
      $palabras = explode(' ', $terminoBusquedaLimpio);

      foreach ($palabras as $palabra) {
        $reuniones = $reuniones->filter(function ($reunion) use ($palabra) {
          return false !== stristr(Helpers::sanearStringConEspacios($reunion->nombre), $palabra);
        });
      }
      $textoBusqueda .= '<b>, con busqueda: </b>"' . $buscar . '" ';
      $bandera = 1;

      // Crear una tag
      $tag = new stdClass();
      $tag->label = $buscar;
      $tag->field = 'buscar';
      $tag->value = $buscar;
      $tag->fieldAux = '';
      $tagsBusqueda[] = $tag;
    }

    //$diasSelecionado = [];
    // Filtrar por día
    /*if ($request->dias) {
      $diasSelecionado = $request->dias;
      $reuniones = $reuniones->whereIn('dia', $diasSelecionado);

      $nombreDias = [];
      foreach ($request->dias as $dia) {
        $nombreDias[] = Helpers::obtenerDiaDeLaSemana($dia);

        // Crear una tag
        $tag = new stdClass();
        $tag->label = Helpers::obtenerDiaDeLaSemana($dia);
        $tag->field = 'selectDia';
        $tag->value = $dia;
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;
      }

      if (count($nombreDias) > 1)
        $textoBusqueda .= '<b> Día: </b>"' . implode(', ', $nombreDias) . '"';
      else
        $textoBusqueda .= '<b> Días: </b>"' . implode(', ', $nombreDias) . '"';

      $bandera = 1;
    }*/

    $sedesSelecionadas = [];
    // Filtrar por sede
    if ($request->sede_id) {
      $sedesSelecionadas = is_array($request->sede_id);
      $reuniones = $reuniones->whereIn('sede_id', $request->sede_id);

      $nombresSeleccionSedes = Sede::whereIn('id', $request->sede_id)->select('nombre', 'id')->get();

      foreach ($nombresSeleccionSedes as $sede) {
        // Crear una tag
        $tag = new stdClass();
        $tag->label = $sede->nombre;
        $tag->field = 'selectSede';
        $tag->value = $sede->id;
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;
      }

      $textoBusqueda .= '<b> Sedes: </b>"' . implode(', ', $nombresSeleccionSedes->pluck('nombre')->toArray()) . '"';
      $bandera = 1;
    }

    /// AQUI SE FINALIZA LA CONSULTO TOTAL
    if ($reuniones->count() > 0) {
      /// AQUI PONGO ESA FUNCION TOQUERY PORQUE DEBO PASARLO DEL FORMATO COLLECTION QUE USO PARA EL FILTER
      /// Y LUEGO DEBO PONERLA EN UN ARREGLO DE TIPO OBJETO PARA PODER HACER EL ORDER BY Y EL PAGINATE
      $reuniones = $reuniones->toQuery()->orderBy('id', 'desc')->paginate(12);
    } else {
      $reuniones = Reunion::whereRaw('1=2')->paginate(12);
    }

    $configuracion = Configuracion::first();
    return view('contenido.paginas.reuniones.listar', [
      //'dias' => $dias,
      'sedes' => $sedes,
      'meses' => $meses,
      'bandera' => $bandera,
      'tagsBusqueda' => $tagsBusqueda,
      'rolActivo' => $rolActivo,
      'reuniones' => $reuniones,
      'contadorBaja' => $contadorBaja,
      'textoBusqueda' => $textoBusqueda,
      'configuracion' => $configuracion,
      'contadorTodos' => $contadorTodos,
      //'diasSelecionado' => $diasSelecionado,
      'sedesSelecionadas' => $sedesSelecionadas,
      'buscar' => $buscar
    ]);
  }

  public function editar(Reunion $reunion)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reuniones.opcion_modificar_reunion');

    $configuracion = Configuracion::first();
    $sedes = Sede::get();
    $sedesReunion = $reunion->sedes()->pluck('sedes_id')->toArray();
    $sexosReunion = json_decode($reunion->genero);
    $tiposEdades = RangoEdad::get();
    $tiposEdadesReunion = $reunion->rangosEdades()->pluck('rangos_edades_id')->toArray();

    $ofrendas = TipoOfrenda::get();
    $ofrendaReunion = $reunion->tiposOfrendas()->pluck('tipo_ofrenda_id')->toArray();
    $tipoUsuarios = TipoUsuario::select('id', 'nombre')->orderBy('nombre', 'asc')->get();
    $tipoUsuariosReunion = $reunion->tipoUsuarios()->pluck('tipo_usuarios.id')->toArray();
    $clasificacionesAsistentes = ClasificacionAsistente::orderBy('id', 'desc')->get();
    $clasificacionAsistentesReunion = $reunion->clasificacionesAsistentes()->pluck('clasificacion_asistente_id')->toArray();

    return view('contenido.paginas.reuniones.editar', [
      'reunion' => $reunion,
      'configuracion' => $configuracion,
      'sedes' => $sedes,
      'tiposEdades' => $tiposEdades,
      'ofrendas' => $ofrendas,
      'sedesReunion' => $sedesReunion,
      'sexosReunion' => $sexosReunion,
      'tiposEdadesReunion' => $tiposEdadesReunion,
      'ofrendaReunion' => $ofrendaReunion,
      'tipoUsuarios' => $tipoUsuarios,
      'tipoUsuariosReunion' => $tipoUsuariosReunion,
      'clasificacionesAsistentes' => $clasificacionesAsistentes,
      'clasificacionAsistentesReunion' => $clasificacionAsistentesReunion
    ]);
  }

  public function actualizar(Request $request, Reunion $reunion)
  {
    $configuracion = Configuracion::first();
    $validate = [
      'hora' => ['required'],
      //'día' => ['required'],
      'nombre' => ['required',],
      'sede' => ['required'],
      'díasDePlazo' => ['required'],
    ];
    if ($request->habilitarReserva) {
      $validate = array_merge($validate, [
        'díasPlazoReserva' => ['required', 'numeric', 'min:0'],
        'aforo' => ['required', 'numeric', 'min:0'],
      ]);
      if ($request->habilitarReservaInvitados) {
        $validate = array_merge($validate, [
          'cantidadMáximaDeInvitados' => ['required', 'numeric', 'min:0'],
        ]);
      }
    }

    $request->validate($validate);

    //$reunion->dia = $request->día;
    $reunion->hora = $request->hora;
    $reunion->nombre = $request->nombre;
    $reunion->sede_id = $request->sede;
    $reunion->descripcion = $request->descripción;
    $reunion->genero = json_encode($request->sexos);
    $reunion->dias_plazo_reporte = $request->díasDePlazo;
    $reunion->hora_maxima_reportar_asistencia = $request->horaMáxima;

    $reunion->habilitar_reserva = $request->habilitarReserva ? true : false;

    if($request->habilitarReserva)
    {
      $reunion->dias_plazo_reserva = $request->díasPlazoReserva;
      $reunion->aforo = $request->aforo;
      $reunion->solo_reservados_pueden_asistir = $request->soloReservaronAsistir;
      $reunion->habilitar_reserva_invitados = $request->habilitarReservaInvitados;
      $reunion->habilitar_reserva_familiares = $request->habilitarReservaFamiliares;
      $reunion->cantidad_maxima_reserva_invitados = $request->cantidadMáximaDeInvitados;
    }else {
      $reunion->dias_plazo_reserva = null;
      $reunion->aforo = null;
      $reunion->solo_reservados_pueden_asistir = false; // O null, según tu base de datos
      $reunion->habilitar_reserva_invitados = false;   // O null
      $reunion->habilitar_reserva_familiares = false;   // O null
      $reunion->cantidad_maxima_reserva_invitados = null;
    }

    $reunion->habilitar_preregistro_iglesia_infantil = $request->habilitarPreregistroInfantil ? true : false;

    $reunion->save();

    if ($reunion->save()) {

      // AÑADO LA PORTADA
      if ($request->foto) {
        if ($configuracion->version == 1) {
          $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/reuniones/');
          !is_dir($path) && mkdir($path, 0777, true);

          $imagenPartes = explode(';base64,', $request->foto);
          $imagenBase64 = base64_decode($imagenPartes[1]);
          $nombreFoto = 'reunion' . $reunion->id . '.png';
          $imagenPath = $path . $nombreFoto;
          file_put_contents($imagenPath, $imagenBase64);
          $reunion->portada = $nombreFoto;
          $reunion->save();
        } else {
          /*
          $s3 = AWS::get('s3');
          $s3->putObject(array(
            'Bucket'     => $_ENV['aws_bucket'],
            'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
            'SourceFile' => "img/temp/".Input::get('foto-hide'),
          ));*/
        }
      }
    }

    // Sincronizar relaciones
    $reunion->sedes()->sync($request->sedesAsistentes);
    $reunion->rangosEdades()->sync($request->rangosEdad);
    $reunion->tiposOfrendas()->sync($request->ofrendas);
    $reunion->tipoUsuarios()->sync($request->tipoUsuarios);
    $reunion->clasificacionesAsistentes()->sync($request->clasificacionAsistentes);

    return back()->with('success', 'Reunión actualizado exitosamente.');
  }

  public function eliminar(Reunion $reunion)
  {
    if (count($reunion->reportes) > 0) {
      return redirect()->route('reuniones.lista')->with('danger', 'No se puede eliminar la reunión porque tiene registros asociados. Utilizar opción dar de baja.');
    } else {
      $reunion->forceDelete();
      return redirect()->route('reuniones.lista')->with('success', 'Reunión eliminada exitosamente.');
    }
  }

  public function darBaja(Reunion $reunion)
  {
    if (count($reunion->reportes) > 0) {
      $reunion->delete();
      return redirect()->route('reuniones.lista')->with('success', 'Reunión dada de baja exitosamente.');
    }
  }
}
