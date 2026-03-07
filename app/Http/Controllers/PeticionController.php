<?php

namespace App\Http\Controllers;

use App\Exports\PeticionesExport;
use App\Helpers\Helpers;
use App\Mail\DefaultMail;
use App\Models\CampoExtra;
use App\Models\CampoInformeExcel;
use App\Models\Configuracion;
use App\Models\Pais;
use App\Models\PasoCrecimiento;
use App\Models\Peticion;
use App\Models\SeguimientoPeticion;
use App\Models\TipoPeticion;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use \stdClass;

class PeticionController extends Controller
{

  public function panel(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    // verificar si cumple el permiso
    $rolActivo->verificacionDelPermiso('peticiones.subitem_panel_peticiones');

    $configuracion = Configuracion::find(1);
    $peticiones = collect();
    $indicadores = [];
    $tiposPeticiones = TipoPeticion::orderBy('orden', 'asc')->get();

    $paisSeleccionado = null;
    $tipoPeticionSeleccionada = null;


    if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas') || $rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
        $peticiones = auth()->user()->misPeticiones();
      }

      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas')) {
        $peticiones = Peticion::leftJoin('users', 'peticiones.user_id', '=', 'users.id')
          ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'genero')
          ->get();
      }
    }

    // Filtro por fechas
    $filtroFechaIni = $request->filtroFechaIni ? Carbon::parse($request->filtroFechaIni)->format('Y-m-d') : Carbon::now()->firstOfMonth()->format('Y-m-d');
    $filtroFechaFin = $request->filtroFechaFin ? Carbon::parse($request->filtroFechaFin)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

    $peticiones = $peticiones->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);
    //$textoBusqueda .= '<b>, Rango </b> Del ' . $filtroFechaIni . ' al ' . $filtroFechaFin;

    $arrayPaises = $peticiones->where('pais_id', '!=', null)->unique('pais_id')->pluck('pais_id')->toArray();
    $paises = Pais::whereIn('id', $arrayPaises)->get();

    $paises->map(function ($pais)  use ($peticiones, $tiposPeticiones) {
      $peticionesPaises = clone $peticiones;
      $pais->cantidad = $peticionesPaises->where('pais_id', $pais->id)->count();

      $tipos =  [];
      foreach ($tiposPeticiones as $tipoPeticion) {
        $item = new stdClass();
        $item->id = $tipoPeticion->id;
        $item->nombre = $tipoPeticion->nombre;
        $item->cantidad = $tipoPeticion->peticiones()->where('pais_id', $pais->id)->select('id')->count();
        $tipos[] = $item;
      }

      $pais->tipos = $tipos;
    });

    //tiposPeticiones
    $tiposPeticiones->map(function ($tipoPeticion)  use ($peticiones) {
      $peticionesPaises = clone $peticiones;
      $tipoPeticion->cantidad = $peticionesPaises->where('tipo_peticion_id', $tipoPeticion->id)->count();
    });

    $labelsTiposPeticiones = $tiposPeticiones->pluck('nombre')->toArray();
    $seriesTiposPeticiones = $tiposPeticiones->pluck('cantidad')->toArray();
    $primerSerieTipoPeticion = $seriesTiposPeticiones[0] ? $seriesTiposPeticiones[0] : 0;
    $primerLabelTipoPeticion = $labelsTiposPeticiones[0] ? $labelsTiposPeticiones[0] : '';

    // Filtro por pais
    if ($request->paisId) {
      $peticiones = $peticiones->where('pais_id', $request->paisId);
      $paisSeleccionado = Pais::find($request->paisId);
    }

    // Filtro por tipo peticion
    if ($request->tipoPeticionId) {
      $tipoPeticionSeleccionada = TipoPeticion::find($request->tipoPeticionId);
      $peticiones = $peticiones->where('tipo_peticion_id', $request->tipoPeticionId);
    }


    $item = new stdClass();
    $item->nombre = 'Total peticiones';
    $item->cantidad = $peticiones->count();
    $item->color = 'bg-label-primary';
    $item->imagen = 'icono_indicador.png';
    $item->icono = 'ti ti-notes';
    $item->col = 'col-md-3 col-sm-6';
    $indicadores[] = $item;

    $item = new stdClass();
    $item->nombre = 'Total respondidas';
    $item->cantidad = $peticiones->where('estado', 2)->count();
    $item->color = 'bg-label-success';
    $item->imagen = 'icono_indicador.png';
    $item->icono = 'ti ti-file-like';
    $item->col = 'col-md-3 col-sm-6';
    $indicadores[] = $item;

    $item = new stdClass();
    $item->nombre = 'Paises';
    $item->cantidad = $peticiones->groupBy('pais_id')->count();
    $item->color = 'bg-label-warning';
    $item->imagen = 'icono_indicador.png';
    $item->icono = 'ti ti-world-pin';
    $item->col = 'col-md-2  col-sm-6';
    $indicadores[] = $item;

    $item = new stdClass();
    $item->nombre = 'Hombres';
    $item->cantidad = $peticiones->where('genero', 0)->count();
    $item->color = 'bg-label-warning';
    $item->imagen = 'icono_indicador.png';
    $item->icono = 'ti ti-man';
    $item->col = 'col-md-2  col-sm-6';
    $indicadores[] = $item;

    $item = new stdClass();
    $item->nombre = 'Mujeres';
    $item->cantidad = $peticiones->where('genero', 1)->count();
    $item->color = 'bg-label-warning';
    $item->col = 'col-md-2 col-sm-6';
    $item->imagen = 'icono_indicador.png';
    $item->icono = 'ti ti-woman';
    $indicadores[] = $item;

    if ($peticiones->count() > 0) {

      $peticiones = $peticiones->toQuery()->leftJoin('users', 'peticiones.user_id', '=', 'users.id')
        ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido')
        ->orderBy('peticiones.id', 'desc')->paginate(12);
      $peticiones->map(function ($peticion) {

        $peticion->nombreUsuario = $peticion->primer_nombre . ' ' . $peticion->segundo_nombre . ' ' . $peticion->primer_apellido;
        $peticion->fotoUsuario = $peticion->foto;

        $telefonosArray = [];
        $peticion->telefono_fijo ? array_push($telefonosArray, $peticion->telefono_fijo) : '';
        $peticion->telefono_movil ? array_push($telefonosArray, $peticion->telefono_movil) : '';
        $peticion->telefono_otro ? array_push($telefonosArray, $peticion->telefono_otro) : '';

        $peticion->telefonosUsuario = $telefonosArray && is_array($telefonosArray) ? implode(", ", $telefonosArray) : ' Sin datos';
        $peticion->emailUsuario = $peticion->email ?: 'Sin dato';

        // usuarioCreacion =
        $usuarioCreacion = $peticion->autorCreacion()->withTrashed()->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido')->first();
        $peticion->usuarioCreacion = ($usuarioCreacion && $peticion->user_id != $usuarioCreacion->autor_creacion_id)
          ? $usuarioCreacion->nombre(3)
          : 'Autogestión';
      });
    } else {
      $peticiones = User::whereRaw('1=2')->paginate(1);
    }

    $meses = Helpers::meses('largo');

    return view('contenido.paginas.peticiones.panel', [
      'rolActivo' => $rolActivo,
      'peticiones' => $peticiones,
      'configuracion' => $configuracion,
      'indicadores' => $indicadores,
      'tiposPeticiones' => $tiposPeticiones,
      'labelsTiposPeticiones' => $labelsTiposPeticiones,
      'seriesTiposPeticiones' => $seriesTiposPeticiones,
      'primerSerieTipoPeticion' => $primerSerieTipoPeticion,
      'primerLabelTipoPeticion' => $primerLabelTipoPeticion,
      'textoBusqueda' => '',
      'filtroFechaIni' => $filtroFechaIni,
      'filtroFechaFin' => $filtroFechaFin,
      'paises' => $paises,
      'meses' => $meses,
      'paisSeleccionado' => $paisSeleccionado,
      'tipoPeticionSeleccionada' => $tipoPeticionSeleccionada
    ]);
  }

  public function gestionar(Request $request, $tipo = 'sin-responder')
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    // verificar si cumple el permiso
    $rolActivo->verificacionDelPermiso('peticiones.subitem_gestionar_peticiones');

    $camposPeticiones = Helpers::camposPeticiones();
    $configuracion = Configuracion::find(1);
    $tiposPeticiones = TipoPeticion::orderBy('orden', 'asc')->get();
    $paises = Pais::select('id', 'nombre')->orderBy('nombre', 'asc')->get();
    $peticiones = collect();
    $indicadores = [];
    $buscar = '';
    $textoBusqueda = '';
    $tagsBusqueda = [];
    $bandera = 0;
    $persona = null;

    $queUsuariosCargar = $rolActivo->hasPermissionTo('personas.ajax_obtiene_asistentes_solo_ministerio')
      ? 'discipulos'
      : 'todos';

    if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas') || $rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
        $peticiones = auth()->user()->misPeticiones();
      }

      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas')) {
        $peticiones = Peticion::leftJoin('users', 'peticiones.user_id', '=', 'users.id')
          ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido')
          ->get();
      }
    }

    $item = new stdClass();
    $item->nombre = 'Sin responder';
    $item->url = 'sin-responder';
    $item->cantidad = $peticiones->where('estado', 1)->count();
    $item->color = 'bg-label-primary';
    $item->imagen = 'icono_indicador.png';
    $item->icono = 'ti ti-notes';
    $indicadores[] = $item;

    $item = new stdClass();
    $item->nombre = 'Con seguimiento';
    $item->url = 'con-seguimiento';
    $item->cantidad = $peticiones->where('estado', 3)->count();
    $item->color = 'bg-label-warning';
    $item->imagen = 'icono_indicador.png';
    $item->icono = 'ti ti-file-like';
    $indicadores[] = $item;

    $item = new stdClass();
    $item->nombre = 'Finalizadas';
    $item->url = 'finalizadas';
    $item->cantidad = $peticiones->where('estado', 2)->count();
    $item->color = 'bg-label-success';
    $item->imagen = 'icono_indicador.png';
    $item->icono = 'ti ti-file-check';
    $indicadores[] = $item;

    $indicadores = collect($indicadores);

    if ($tipo == 'sin-responder') {
      $peticiones = $peticiones->where('estado', 1);
      $textoBusqueda .= '<b> Tipo: </b>"Sin resporder"';
    } elseif ($tipo == 'finalizadas') {
      $peticiones = $peticiones->where('estado', 2);
      $textoBusqueda .= '<b> Tipo: </b>"Finalizadas"';
    } elseif ($tipo == 'con-seguimiento') {
      $peticiones = $peticiones->where('estado', 3);
      $textoBusqueda .= '<b> Tipo: </b>"Con seguimiento"';
    }

    // Filtro por persona
    if ($request->persona_id) {
      $peticiones = $peticiones->whereIn('user_id', $request->persona_id);
      $persona = User::withTrashed()->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido')->find($request->persona_id);
      $textoBusqueda .= '<b>, Peticiones de: </b>"' . $persona->nombre(3) . '"';
      $bandera = 1;

      // Tag por persona
      $tag = new stdClass();
      $tag->label = $persona->nombre(3);
      $tag->field = 'persona_id';
      $tag->value = $persona->id;
      $tag->fieldAux = '';
      $tagsBusqueda[] = $tag;
    }

    // filtro por tipo peticiones
    $filtroTipoPeticiones = [];
    if ($request->filtroTipoPeticiones) {
      $filtroTipoPeticiones = $request->filtroTipoPeticiones;
      $peticiones = $peticiones->whereIn('tipo_peticion_id', $request->filtroTipoPeticiones);

      $tps = TipoPeticion::whereIn('id', $request->filtroTipoPeticiones)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $textoBusqueda .= '<b>, Tipo de peticiones: </b>"' . implode(', ', $tps) . '"';
      $bandera = 1;

      $tiposDePeticiones = TipoPeticion::whereIn('id', $request->filtroTipoPeticiones)->select('id','nombre')->get();
      foreach( $tiposDePeticiones as $tipo)
      {
        // Tag por tipo de grupo
        $tag = new stdClass();
        $tag->label = $tipo->nombre;
        $tag->field = 'filtroTipoPeticiones';
        $tag->value = $tipo->id;
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;
      }
    }

    // filtro por paises
    $filtroPaises = [];
    if ($request->filtroPaises) {
      $filtroPaises = $request->filtroPaises;
      $peticiones = $peticiones->whereIn('pais_id', $request->filtroPaises);

      $textoPaises = Pais::whereIn('id', $request->filtroPaises)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $textoBusqueda .= '<b>, Paises: </b>"' . implode(', ', $textoPaises) . '"';
      $bandera = 1;
    }

    // filtro por rango de fecha
    $filtroFechaIni = $request->filtroFechaIni ? $request->filtroFechaIni : Carbon::now()->firstOfYear()->format('Y-m-d');
    $filtroFechaFin = $request->filtroFechaFin ? $request->filtroFechaFin : Carbon::now()->format('Y-m-d');
    $peticiones = $peticiones->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);
    $textoBusqueda .= '<b>, Rango </b> Del ' . $filtroFechaIni . ' al ' . $filtroFechaFin;


    // Busqueda por palabra clave
    if ($request->buscar) {
      $buscar = htmlspecialchars($request->buscar);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $peticiones = $peticiones->filter(function ($peticion) use ($palabra) {
          $respuesta  = false !== stristr(Helpers::sanearStringConEspacios($peticion->primer_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->segundo_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->primer_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->segundo_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->identificacion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->direccion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->telefono_movil), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->email), $palabra);

          return $respuesta;
        });
      }

      $buscar = $request->buscar;
      $textoBusqueda .=  '<b>, Con busqueda: </b>"' . $buscar . '" ';
      $bandera = 1;
    }

    if ($peticiones->count() > 0) {

      $peticiones = $peticiones->toQuery()->leftJoin('users', 'peticiones.user_id', '=', 'users.id')
        ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido')
        ->orderBy('peticiones.id', 'desc')->paginate(12);
      $peticiones->map(function ($peticion) {

        $peticion->nombreUsuario = $peticion->primer_nombre . ' ' . $peticion->segundo_nombre . ' ' . $peticion->primer_apellido;
        $peticion->fotoUsuario = $peticion->foto;

        $telefonosArray = [];
        $peticion->telefono_fijo ? array_push($telefonosArray, $peticion->telefono_fijo) : '';
        $peticion->telefono_movil ? array_push($telefonosArray, $peticion->telefono_movil) : '';
        $peticion->telefono_otro ? array_push($telefonosArray, $peticion->telefono_otro) : '';

        $peticion->telefonosUsuario = $telefonosArray && is_array($telefonosArray) ? implode(", ", $telefonosArray) : ' Sin datos';
        $peticion->emailUsuario = $peticion->email ?: 'Sin dato';

        // usuarioCreacion =
        $usuarioCreacion = $peticion->autorCreacion()->withTrashed()->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido')->first();
        $peticion->usuarioCreacion = ($usuarioCreacion && $peticion->user_id != $usuarioCreacion->autor_creacion_id)
          ? $usuarioCreacion->nombre(3)
          : 'Autogestión';
      });
    } else {
      $peticiones = User::whereRaw('1=2')->paginate(1);
    }

    $camposInformeExcel = CampoInformeExcel::orderBy('orden', 'asc')->get();
    $pasosCrecimiento = PasoCrecimiento::orderBy('updated_at', 'asc')->get();
    $camposExtras = CampoExtra::where('visible', '=', true)->get();
    $meses = Helpers::meses('largo');


    return view('contenido.paginas.peticiones.gestionar', [
      'rolActivo' => $rolActivo,
      'peticiones' => $peticiones,
      'configuracion' => $configuracion,
      'indicadores' => $indicadores,
      'tipo' => $tipo,
      'tiposPeticiones' => $tiposPeticiones,
      'filtroTipoPeticiones' => $filtroTipoPeticiones,
      'buscar' => $buscar,
      'filtroFechaIni' => $filtroFechaIni,
      'filtroFechaFin' => $filtroFechaFin,
      'textoBusqueda' => $textoBusqueda,
      'tagsBusqueda' => $tagsBusqueda,
      'bandera' =>  $bandera,
      'queUsuariosCargar' => $queUsuariosCargar,
      'persona' => $persona,
      'camposInformeExcel' => $camposInformeExcel,
      'pasosCrecimiento' => $pasosCrecimiento,
      'camposExtras' => $camposExtras,
      'camposPeticiones' => $camposPeticiones,
      'paises' => $paises,
      'filtroPaises' => $filtroPaises,
      'meses' => $meses
    ]);
  }

  public function nueva()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    // verificar si cumple el permiso
    $rolActivo->verificacionDelPermiso('peticiones.subitem_nueva_peticion');

    $queUsuariosCargar = $rolActivo->hasPermissionTo('personas.ajax_obtiene_asistentes_solo_ministerio')
      ? 'discipulos'
      : 'todos';

    $tiposPeticiones = TipoPeticion::orderBy('orden', 'asc')->get();

    return view('contenido.paginas.peticiones.nueva', [
      'rolActivo' => $rolActivo,
      'queUsuariosCargar' => $queUsuariosCargar,
      'tiposPeticiones'  => $tiposPeticiones
    ]);
  }

  public function crear(Request $request)
  {
    $request->validate([
      'persona' => 'required',
      'tipo_de_petición' => 'required',
      'descripción' => 'required'
    ]);
    $configuracion = Configuracion::find(1);
    $usuario = User::find($request->persona);
    $peticion = new Peticion;
    $peticion->user_id = $usuario->id;
    $peticion->descripcion = $request->descripción;
    $peticion->tipo_peticion_id = $request->tipo_de_petición;
    $peticion->autor_creacion_id = auth()->user()->id;
    $peticion->pais_id = $usuario->pais_id;
    $peticion->estado = 1; // 1=Iniciada, 2=Finalizada, 3=Seguimiento

    $peticion->fecha = Carbon::now()->format('Y-m-d');
    $peticion->save();

    // Enviar el correo
    $mensaje = $peticion->tipoPeticion->mensaje_parte_1;
    if ($usuario->email != '' && $mensaje != '') {
      $key = config('variables.biblia_key');
      $arrContextOptions = [
        'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false,
        ],
      ];

      try {
        $jsonVersiculos = $peticion->tipoPeticion->json_versiculos;
        if ($jsonVersiculos != '') {
          $jsonVersiculos = json_decode($jsonVersiculos);
          $cantidadItems = count($jsonVersiculos);
          $random = rand(1, $cantidadItems);
          $respuestaText = file_get_contents(
            'https://api.biblia.com/v1/bible/content/RVR60.txt?passage=' .
              $jsonVersiculos[$random - 1]->cita .
              '&key=' .
              $key .
              '&style=neVersePerLineFullReference&culture=es',
            false,
            stream_context_create($arrContextOptions)
          );
          $mensaje .=
            '<I>' . $respuestaText . '</I> <B>(' . $jsonVersiculos[$random - 1]->titulo . ', RVR60)</B></p>';
        }
      } catch (Exception $e) {
      }

      $mensaje .= $peticion->tipoPeticion->mensaje_parte_2;

      $mailData = new stdClass();
      $mailData->subject = 'Petición';
      $mailData->nombre = $usuario->nombre(3);
      $mailData->mensaje = $mensaje;

      if ($peticion->tipoPeticion->banner_email != '') {
        $mailData->banner =
          $configuracion->version == 1
          ? Storage::url(
            $configuracion->ruta_almacenamiento . '/img/email/' . $peticion->tipoPeticion->banner_email
          )
          : Storage::url(
            $configuracion->ruta_almacenamiento . '/img/email/' . $peticion->tipoPeticion->banner_email
          );
      }

      //Mail::to($usuario->email)->send(new DefaultMail($mailData));
      Mail::to('softjuancarlos@gmail.com')->send(new DefaultMail($mailData));
    }

    return back()->with('success', "La petición de <b>" . $usuario->nombre(3) . "</b> fue creada con éxito.");
  }

  public function eliminaciones(Request $request, $tipo)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $peticiones = [];
    $parametrosBusqueda = json_decode($request->parametrosBusqueda);


    if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas') || $rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
        $peticiones = auth()->user()->misPeticiones();
      }

      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas')) {
        $peticiones = Peticion::leftJoin('users', 'peticiones.user_id', '=', 'users.id')
          ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido')
          ->get();
      }
    }

    if ($tipo == 'sin-responder') {
      $peticiones = $peticiones->where('estado', 1);
    } elseif ($tipo == 'finalizadas') {
      $peticiones = $peticiones->where('estado', 2);
    } elseif ($tipo == 'con-seguimiento') {
      $peticiones = $peticiones->where('estado', 3);
    }

    // Filtro por fechas
    $filtroFechaIni = isset($parametrosBusqueda->filtroFechaIni) ? $parametrosBusqueda->filtroFechaIni : Carbon::now()->firstOfYear()->format('Y-m-d');
    $filtroFechaFin = isset($parametrosBusqueda->filtroFechaFin) ? $parametrosBusqueda->filtroFechaFin : Carbon::now()->format('Y-m-d');
    $peticiones = $peticiones->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);


    // Filtro por persona
    if ($parametrosBusqueda && isset($parametrosBusqueda->persona_id)) {
      $peticiones = $peticiones->whereIn('user_id', $parametrosBusqueda->persona_id);
    }

    // filtro por tipo peticiones
    if ($parametrosBusqueda && isset($parametrosBusqueda->filtroTipoPeticiones)) {
      $peticiones = $peticiones->whereIn('tipo_peticion_id', $parametrosBusqueda->filtroTipoPeticiones);
    }

    // Busqueda por palabra clave
    if ($parametrosBusqueda &&  isset($parametrosBusqueda->buscar)) {
      $buscar = htmlspecialchars($parametrosBusqueda->buscar);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $peticiones = $peticiones->filter(function ($peticion) use ($palabra) {
          $respuesta  = false !== stristr(Helpers::sanearStringConEspacios($peticion->primer_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->segundo_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->primer_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->segundo_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->identificacion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->direccion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->telefono_movil), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->email), $palabra);

          return $respuesta;
        });
      }
    }

    //Elimino
    $cantidad = $peticiones->count();
    foreach ($peticiones as $peticion) {
      $peticion->seguimientos()->delete();
      $peticion->delete();
    }

    $mensaje = $cantidad > 1
      ? "Las <b>" . $cantidad . "</b> peticiones fueron eliminadas con éxito."
      : "La petición fue eliminada con éxito.";

    return back()->with('success', $mensaje);
  }

  public function eliminacion($id)
  {
    $peticion = Peticion::find($id);
    $peticion->seguimientos()->delete();
    $peticion->delete();

    return back()->with('success', "La petición fue eliminada con éxito.");
  }

  public function generarExcel(Request $request, $tipo)
  {

     $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    // verificar si cumple el permiso
    $rolActivo->verificacionDelPermiso('peticiones.boton_descargar_excel');

    $configuracion = Configuracion::find(1);
    $camposPeticiones = collect(Helpers::camposPeticiones());
    $camposPeticiones =  $camposPeticiones->whereIn('id', $request->informacionCamposPeticiones);
    $parametrosBusqueda = json_decode($request->parametrosBusqueda);

    $arrayCamposInfoPersonal = $request->informacionPersonal ? $request->informacionPersonal : []; //$arrayCamposInfoPersonal
    $arrayPasosCrecimiento = $request->informacionMinisterial ? $request->informacionMinisterial : []; // $arrayPasosCrecimiento
    $arrayDatosCongregacionales = $request->informacionCongregacional ? $request->informacionCongregacional : []; // $arrayDatosCongregacionales
    $arrayCamposExtra = $request->informacionCamposExtras ? $request->informacionCamposExtras : []; // $arrayCamposExtra

    $nombreArchivo = 'informe_peticiones_' . Carbon::now()->format('Y-m-d-H-i-s');
    $rutaArchivo = "/$configuracion->ruta_almacenamiento/informes/peticiones/$nombreArchivo.xlsx";

    Excel::store(
      new PeticionesExport($tipo, $parametrosBusqueda, $camposPeticiones, $arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra),
      $rutaArchivo,
      'public'
    );

    return back()->with(
      'success',
      'El informe fue generado con éxito, <a href="' . Storage::url($rutaArchivo) . '" class=" link-success fw-bold" download="' . $nombreArchivo . '.xlsx"> descargalo aquí</a>'
    );
  }
}
