<?php

namespace App\Http\Controllers;

use App\Exports\AsistenciasReporteReunionExport;
use App\Exports\ReservasReporteReunionExport;
use Maatwebsite\Excel\Facades\Excel; // Importa el Facade de Excel
use App\Helpers\Helpers;
use App\Livewire\ReporteReuniones\Reservas;
use App\Models\Configuracion;
use App\Models\Ofrenda;
use App\Models\ReporteReunion;
use App\Models\ReservaReunion;
use App\Models\Reunion;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail; // <-- Importa la clase Mail
use App\Mail\DefaultMail;           // <-- Importa tu Mailable
use stdClass;


class ReporteReunionController extends Controller
{

  public function lista(Request $request, $tipo = 'todos')
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reporte_reuniones.subitem_lista_reportes_reunion');

    $textoBusqueda = '';
    $tagsBusqueda = [];
    $bandera = 0;

    $sedes = Sede::get();
    $reuniones = Reunion::get();
    $meses = Helpers::meses('largo');
    $reportes = ReporteReunion::whereRaw("1=1");

    $filtroFechaIni = $request->filtroFechaIni ? Carbon::parse($request->filtroFechaIni)->format('Y-m-d') : Carbon::now()->subDays(30)->format('Y-m-d');
    $filtroFechaFin = $request->filtroFechaFin ? Carbon::parse($request->filtroFechaFin)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

    $user = auth()->user();
    $sedesEncargadasArray = $user->sedesEncargadas('array');

    // Obtener reuniones seleccionadas por el usuario
    $reunionesFiltradas = $request->has('reuniones_id') ? $request->input('reuniones_id') : [];

     if ($request->reuniones_id) {
      $bandera = 1;
      foreach($reuniones->whereIn('id', $reunionesFiltradas) as $reunion)
      {
          $tag = new stdClass();
          $tag->label = $reunion->nombre;
          $tag->field = 'reuniones_id';
          $tag->value = $reunion->id;
          $tag->fieldAux = '';
          $tagsBusqueda[] = $tag;
      }
    }

    $sedesFiltradas = $request->has('sedes_id') ? $request->input('sedes_id') : [];

    if ($request->sedes_id) {
      $bandera = 1;
      foreach($sedes->whereIn('id', $sedesFiltradas) as $sede)
      {
          $tag = new stdClass();
          $tag->label = $sede->nombre;
          $tag->field = 'sedes';
          $tag->value = $sede->id;
          $tag->fieldAux = '';
          $tagsBusqueda[] = $tag;
      }
    }

    if ($rolActivo->hasPermissionTo('reporte_reuniones.lista_reportes_reunion_todos') == FALSE) {
      if ($rolActivo->lista_reuniones_sede_id) {
        //Reasigno sedes y reuniones
        $sedes = Sede::where('id', '=', $rolActivo->lista_reuniones_sede_id)->get();
        $reuniones = Reunion::where('sede_id', '=', $rolActivo->lista_reuniones_sede_id)->get();

        $ids_reuniones = Reunion::where('sede_id', '=', $rolActivo->lista_reuniones_sede_id)->select('id')->pluck('id')->toArray();
        $reportes = ReporteReunion::whereIn('reunion_id', $ids_reuniones);
      } else {
        $sedesEncargadasArray = $user->sedesEncargadas('array');
        $sedeDeLosGruposArray = $user->gruposEncargados()->select('grupos.sede_id')->pluck('grupos.sede_id')->toArray();
        $sedesTotalesArray = array_merge($sedesEncargadasArray, $sedeDeLosGruposArray);
        $sedesTotalesArray = array_filter($sedesTotalesArray);
        $sedesTotalesArray = array_unique($sedesTotalesArray);

        $reuniones = Reunion::select('reuniones.id', 'reuniones.sede_id')->get();

        $reunionesDisponiblesArray = array();

        foreach ($reuniones as $reunion) {
          $interseccionArray = array();

          $otrasSedes = $reunion->sedes()->pluck('sedes_id')->toArray();

          // para determinar si la reunion actual tiene sedes a las que permite asistencia
          if (count($otrasSedes) > 0)
            $interseccionArray = array_intersect($sedesTotalesArray, $otrasSedes);
          // primero pregunta si la reunion actual en su sede a la que pertenece ya existe en el array
          // anterior
          if (in_array($reunion->sede_id, $sedesTotalesArray))
            array_push($reunionesDisponiblesArray, $reunion->id);
          //
          elseif (count($interseccionArray) > 0)
            array_push($reunionesDisponiblesArray, $reunion->id);
        }
        $reunionesDisponiblesArray = array_unique($reunionesDisponiblesArray);

        $reportes->whereIn('reunion_id', $reunionesDisponiblesArray);

        // Reasigno sedes y reuniones
        $sedes = Sede::whereIn('id', $sedesTotalesArray)->get();
        $reuniones = Reunion::whereIn('id', $reunionesDisponiblesArray)->get();
      }
    }

    $reportes = $reportes->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);

    if ($request->reuniones_id) {
      $reunion_seleccionada = $request->reuniones_id;
      if ($reunion_seleccionada != "") {
        $reportes =  $reportes->where('reunion_id', $reunion_seleccionada);
      }
    }

    if ($request->sedes_id) {
      $sede_seleccionada = $request->sedes_id;
      $reportes = $reportes->whereHas('reunion', function ($q) use ($sede_seleccionada) {
        $q->where('sede_id', '=', $sede_seleccionada);
      });
    }

    $textoBusqueda .= '<b>, Rango </b> Del ' . $filtroFechaIni . ' al ' . $filtroFechaFin;
    $reportes = $reportes->with([
        'reunion' => fn($query) => $query->withTrashed()->with('sede')
    ])
    ->orderBy('fecha', 'desc')
    ->orderBy('id', 'desc')
    ->paginate(12);

    return view('contenido.paginas.reporte-reuniones.listar', [
      'meses' => $meses,
      'sedes' => $sedes,
      'reportes' => $reportes,
      'reuniones' => $reuniones,
      'rolActivo' => $rolActivo,
      'textoBusqueda' => $textoBusqueda,
      'filtroFechaIni' => $filtroFechaIni,
      'filtroFechaFin' => $filtroFechaFin,
      'reunionesFiltradas' => $reunionesFiltradas,
      'sedesFiltradas' => $sedesFiltradas,
      'bandera' => $bandera,
      'tagsBusqueda' => $tagsBusqueda,
    ]);
  }

  public function reporte(Reunion $reunion)
  {
    $configuracion = Configuracion::first();
    return view('contenido.paginas.reporte-reuniones.reportar', [
      'reunion' => $reunion,
      'configuracion' => $configuracion
    ]);
  }

  public function crear(Request $request, Reunion $reunion)
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reporte_reuniones.nuevo_reporte_reunion');
    $configuracion = Configuracion::first();
    $validate = [
      'fecha' => 'required',
      'hora'=> ['required'],
    ];
    if ($request->habilitarReserva) {
      $validate = array_merge($validate, [
        'díasPlazoReserva' => ['required', 'numeric', 'min:0'],
        'aforo' => ['required', 'numeric', 'min:0'],
      ]);
      if ($request->habilitarReservaInvitados) {
        $validate = array_merge($validate, [
          'cantidadInvitados' => ['required',  'numeric', 'min:0'],
        ]);
      }
    }

    $request->validate($validate);

    // Crear el reporte de la reunion
    $reporteReunion = new ReporteReunion();
    $reporteReunion->reunion_id = $reunion->id;
    $reporteReunion->fecha = $request->fecha;
    $reporteReunion->hora = $request->hora;
    $reporteReunion->observaciones = $request->observaciones;
    $reporteReunion->url = $request->url;
    $reporteReunion->iframe = $request->iframe;
    $reporteReunion->conteo_preliminar = $request->conteoPreliminar;
    $reporteReunion->portada = $reunion->portada;
    $reporteReunion->habilitar_preregistro_iglesia_infantil = $request->habilitarReserva ? true : false;
    $reporteReunion->habilitar_reserva = $request->habilitarReserva ? true : false;

    if($request->habilitarReserva)
    {
      $reporteReunion->dias_plazo_reserva = $request->díasPlazoReserva;
      $reporteReunion->aforo = $request->aforo;
      $reporteReunion->solo_reservados_pueden_asistir = $request->soloReservaronAsistir;
      $reporteReunion->habilitar_reserva_invitados = $request->habilitarReservaInvitados;
      $reporteReunion->habilitar_reserva_familiares = $request->habilitarReservaFamiliares;
      $reporteReunion->cantidad_maxima_reserva_invitados = $request->cantidadInvitados;
    }else {
      $reporteReunion->dias_plazo_reserva = null;
      $reporteReunion->aforo = null;
      $reporteReunion->solo_reservados_pueden_asistir = false; // O null, según tu base de datos
      $reporteReunion->habilitar_reserva_invitados = false;   // O null
      $reporteReunion->habilitar_reserva_familiares = false;
      $reporteReunion->cantidad_maxima_reserva_invitados = null;
    }

     if ($reporteReunion->save()) {

      // AÑADO LA PORTADA
      if ($request->foto) {
        if ($configuracion->version == 1) {
          $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/reportes-reuniones/');
          !is_dir($path) && mkdir($path, 0777, true);

          $imagenPartes = explode(';base64,', $request->foto);
          $imagenBase64 = base64_decode($imagenPartes[1]);
          $nombreFoto = 'reporte_reunion' . $reporteReunion->id . '.png';
          $imagenPath = $path . $nombreFoto;
          file_put_contents($imagenPath, $imagenBase64);
          $reporteReunion->portada = $nombreFoto;
          $reporteReunion->save();
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

    // crear las clasificaciones que existen hasta el momento, crearlas en 0;
    //"Esto en el caso de que fueran solo hombres y mujeres"
    // Tengo que crear las clasificaciones que tiene la reunion y recorrerlas y crear un attach con cada una y
    // en cantidad colocarle 0
    $clasificaciones = $reporteReunion->reunion->clasificacionesAsistentes;
    foreach ($clasificaciones as $c) {
      $c->reportesReuniones()->attach($reporteReunion->id, ['cantidad' => 0]);
    }

    return redirect()->route('reporteReunion.editar', $reporteReunion)->with('success', 'El reporte de reunión fue creado con éxito.');
  }

  public function perfil()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reporte_reuniones.opcion_ver_perfil_reporte_reunion');
    return "Aqui se va mostrar el perfil";
  }

  public function editar(ReporteReunion $reporteReunion)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reporte_reuniones.opcion_modificar_reporte_reunion');

     $configuracion = Configuracion::first();
     $reunion = $reporteReunion->reunion()->withTrashed()->first();
    return view('contenido.paginas.reporte-reuniones.editar', [
      'reporteReunion' => $reporteReunion,
      'configuracion' => $configuracion,
      'reunion' => $reunion
    ]);
  }

  public function actualizar(Request $request, ReporteReunion $reporteReunion)
  {
    $configuracion = Configuracion::first();
    $validate = [
      'fecha' => ['required'],
      'hora'=> ['required'],
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
    $reporteReunion->fecha = $request->fecha;
    $reporteReunion->hora = $request->hora;
    $reporteReunion->conteo_preliminar = $request->conteoPreliminar;
    $reporteReunion->observaciones = $request->observaciones;
    $reporteReunion->habilitar_reserva = $request->habilitarReserva;
    $reporteReunion->habilitar_preregistro_iglesia_infantil = $request->habilitarPreregistroInfantil;
    $reporteReunion->url = $request->url;
    $reporteReunion->iframe = $request->iframe;

    if($request->habilitarReserva)
    {
      $reporteReunion->dias_plazo_reserva = $request->díasPlazoReserva;
      $reporteReunion->aforo = $request->aforo;
      $reporteReunion->solo_reservados_pueden_asistir = $request->soloReservaronAsistir;
      $reporteReunion->habilitar_reserva_invitados = $request->habilitarReservaInvitados;
      $reporteReunion->habilitar_reserva_familiares = $request->habilitarReservaFamiliares;
      $reporteReunion->cantidad_maxima_reserva_invitados = $request->cantidadInvitados;
    }else {
      $reporteReunion->dias_plazo_reserva = null;
      $reporteReunion->aforo = null;
      $reporteReunion->solo_reservados_pueden_asistir = false; // O null, según tu base de datos
      $reporteReunion->habilitar_reserva_invitados = false;   // O null
      $reporteReunion->habilitar_reserva_familiares = false;
      $reporteReunion->cantidad_maxima_reserva_invitados = null;
    }

    $reporteReunion->save();


    if ($reporteReunion->save()) {

      // AÑADO LA PORTADA
      if ($request->foto) {
        if ($configuracion->version == 1) {
          $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/reportes-reuniones/');
          !is_dir($path) && mkdir($path, 0777, true);

          $imagenPartes = explode(';base64,', $request->foto);
          $imagenBase64 = base64_decode($imagenPartes[1]);
          $nombreFoto = 'reporteReunion' . $reporteReunion->id . '.png';
          $imagenPath = $path . $nombreFoto;
          file_put_contents($imagenPath, $imagenBase64);
          $reporteReunion->portada = $nombreFoto;
          $reporteReunion->save();
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


    return back()->with('success', 'Reporte de reunion actualizado exitosamente.');
  }

  public function eliminar(ReporteReunion $reporteReunion)
  {
    $ofrendasIds = $reporteReunion->ofrendas()->select('ofrendas.id')->pluck('id')->toArray();
    $reporteReunion->ofrendas()->detach();
    $reporteReunion->clasificacionesAsistentes()->detach();
    Ofrenda::destroy($ofrendasIds);
    $reporteReunion->delete();

    return redirect()
    ->route('reporteReunion.lista')
    ->with('success', 'Reporte eliminado exitosamente.');
  }

  public function añadirServidores(ReporteReunion $reporteReunion)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reporte_reuniones.opcion_subitem_anadir_servidores_reporte_reunion');

    return view('contenido.paginas.reporte-reuniones.anadir-servidores', [
      'reporteReunion' => $reporteReunion,
    ]);
  }

  public function añadirAsistentes(ReporteReunion $reporteReunion)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reporte_reuniones.opcion_anadir_asistentes_reporte_reunion');

    return view('contenido.paginas.reporte-reuniones.anadir-asistentes', [
      'reporteReunion' => $reporteReunion
    ]);
  }

  public function añadirReservas(ReporteReunion $reporteReunion)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reporte_reuniones.opcion_anadir_asistentes_reservas_reunion');

    return view('contenido.paginas.reporte-reuniones.anadir-reservas', [
      'reporteReunion' => $reporteReunion
    ]);
  }

  public function registrarAsistenciaQr(Request $request, ReporteReunion $reporteReunion)
  {
    // Obtiene el ID del usuario desde la petición AJAX
    $usuarioId = $request->input('usuarioId');

    if (!is_numeric($usuarioId) || $usuarioId <= 0) {
      return response()->json([
        'status' => 'error',
        'title' => 'QR Inválido',
        'text' => 'El código QR no contiene un ID de usuario válido.'
      ]);
    }

    // --- Aquí va TODA tu lógica de validación ---
    // (Si ya asistió, si la fecha es válida, etc.)
    // Por ejemplo:
    $asistenciaPrevia = $reporteReunion->usuarios()->where('user_id', $usuarioId)->where('asistio', true)->exists();
    if ($asistenciaPrevia) {
      return response()->json([
        'status' => 'info',
        'title' => 'Asistencia Previa',
        'text' => 'Este usuario ya tiene su asistencia registrada.'
      ]);
    }

    // Si todo está bien, registra la asistencia
    $reporteReunion->usuarios()->syncWithoutDetaching([
      $usuarioId => ['asistio' => true, 'updated_at' => now()]
    ]);

    // Finalmente, devuelve una respuesta de éxito en JSON
    return response()->json([
      'status' => 'success',
      'title' => '¡Éxito!',
      'text' => 'Asistencia registrada correctamente.'
    ]);
  }

  public function añadirIngresos(ReporteReunion $reporteReunion)
  {
    return view('contenido.paginas.reporte-reuniones.anadir-ingresos', [
      'reporteReunion' => $reporteReunion,
    ]);
  }

  public function compartirLinkReserva(ReporteReunion $reporteReunion)
  {
    $puedeCompartir = false;

    // 1. Configuración inicial
    $fechaDeLaReunion = Carbon::parse($reporteReunion->fecha);
    $horaDeLaReunion = $reporteReunion->hora;
    $diasPlazo = $reporteReunion->dias_plazo_reserva;
    $hoy = Carbon::now(); // Fecha y hora actual

    // 2. Calcular el inicio y el fin EXACTO del período de reserva
    $inicioReserva = $fechaDeLaReunion->copy()->subDays($diasPlazo)->startOfDay();
    $finReserva = $fechaDeLaReunion->copy()->setTimeFromTimeString($horaDeLaReunion)->format('Y-m-d H:i:s');

    // 3. Comprobar si la fecha actual está dentro del rango válido
    // (después del inicio Y antes del fin)
    if ($hoy->isAfter($inicioReserva) && $hoy->isBefore($finReserva)) {
      $puedeCompartir = true;
    }

    return view('contenido.paginas.reporte-reuniones.compartir-link-reserva', [
      'reporteReunion' => $reporteReunion,
      'puedeCompartir' => $puedeCompartir
    ]);
  }

  public function miReserva (Request $request, ReporteReunion $reporte, User $user = null)
  {
    $origen =  $request->query('origen', 'iglesiaVirtual');
    $usuario = $user ?? auth()->user();

    // ✅ CORRECCIÓN: El "responsable" de la reserva es el mismo usuario objetivo.
    $responsable = $usuario;

    $estaEnPlazo = $reporte->sePuedeReservar();
    $hayCupos = $reporte->hayAforoDisponible();
    $usuarioCumpleRequisitos = $reporte->elUsuarioPuedeReservar($usuario);
    //$permiteReservarParaOtros = $reporte->habilitar_reserva_familiares || $reporte->habilitar_reserva_invitados;

    if (!$estaEnPlazo){
      $descripcion = "El plazo para hacer la reserva ha caducado.";
    } elseif (!$hayCupos) {
      $descripcion = "No hay cupos disponibles.";
    } elseif (!$usuarioCumpleRequisitos) {
      $descripcion = "¿Ups! parace que no cuentas con los requisitos para poder asistira esta reunión.";
    } else {

      $reunion = $reporte->reunion()->withTrashed()->first();

      // Obtener los IDs de todos los usuarios (no invitados) que ya tienen reserva.
      $usuariosYaReservadosIds = ReservaReunion::where('reporte_reunion_id', $reporte->id)
          ->whereNotNull('user_id')
          ->pluck('user_id')
          ->toArray();

      // Comprobar si el usuario actual ya tiene reserva.
      $usuarioYaTieneReserva = in_array($usuario->id, $usuariosYaReservadosIds);

      $todosLosParientes = $usuario
      ->parientesDelUsuario()
      ->leftJoin('tipos_parentesco', 'parientes_usuarios.tipo_pariente_id', '=', 'tipos_parentesco.id')
      //->wherePivot('es_el_responsable', true)
      ->select(
        'users.*', // Seleccionamos todo de la tabla users para asegurar que el modelo esté completo
        'tipos_parentesco.nombre as nombre_parentesco',
        'tipos_parentesco.nombre_masculino',
        'tipos_parentesco.nombre_femenino',
        'parientes_usuarios.es_el_responsable',
      )
      ->get();

      $familiares = $todosLosParientes->filter(function ($pariente) use ($reporte) {
          // Por cada pariente en la colección, ejecutamos tu función de validación.
          // La función necesita recibir el objeto del usuario a validar.
          return $reporte->elUsuarioPuedeReservar($pariente);
      });

      $configuracion = Configuracion::find(1);
      $invitadosDisponibles = $reporte->cantidadDisponibleInvitados($usuario);

      return view('contenido.paginas.reporte-reuniones.mi-reserva', [
        'usuario' => $usuario,
        'origen' => $origen,
        'reporte' => $reporte,
        'usuarioCumpleRequisitos' => $usuarioCumpleRequisitos,
       // 'permiteReservarParaOtros' => $permiteReservarParaOtros,
        'reunion' => $reunion,
        'familiares' => $familiares,
        'configuracion' => $configuracion,
        'usuarioYaTieneReserva' => $usuarioYaTieneReserva,
        'usuariosYaReservadosIds' => $usuariosYaReservadosIds,
        'invitadosDisponibles' => $invitadosDisponibles
      ]);
    }

    $titulo = "No pudimos completar tu reserva";

    return view('contenido.paginas.reporte-reuniones.mensaje-error', [
      'titulo' => $titulo,
      'descripcion' => $descripcion,
      'origen' => $origen,
      'reporte' => $reporte
    ]);

  }

  public function hacerMiReserva (Request $request, ReporteReunion $reporteReunion, User $user = null)
  {
      $nuevasReservas = []; // Array para guardar las reservas recién creadas

      // 1. VALIDACIÓN INICIAL DE DATOS
        $rules = [
          'toggleMiAsistencia' => 'nullable|string',
          'toggleFamilia' => 'nullable|string',
          'familiares' => 'nullable|array',
          'familiares.*' => 'exists:users,id',
          'toggleInvitados' => 'nullable|string',
          'invitados' => 'nullable|array',
          'invitados.*.nombre' => 'required_if:toggleInvitados,1|nullable|string|max:255',
          'invitados.*.email' => 'required_if:toggleInvitados,1|nullable|email|max:255', // También requerido
      ];

      // 2. Define los mensajes de error personalizados
      $messages = [
          'invitados.*.nombre.required_if' => 'El nombre es obligatorio.',
          'invitados.*.email.required_if'  => 'El email es obligatorio.',
          'invitados.*.email.email'        => 'El formato del email no es válido.',
      ];

      // 3. Define los nombres de atributos amigables
      $attributes = [
          'invitados.*.nombre' => 'nombre del invitado',
          'invitados.*.email'  => 'email del invitado',
      ];

      // 4. Ejecuta la validación con los mensajes y atributos personalizados
      $request->validate($rules, $messages, $attributes);

      $responsable = $user ?? auth()->user();

      // --- NUEVA VALIDACIÓN DE SEGURIDAD ---
      $usuariosYaReservadosIds = ReservaReunion::where('reporte_reunion_id', $reporteReunion->id)
          ->whereNotNull('user_id')
          ->pluck('user_id')
          ->toArray();

      // Si el usuario ya tiene reserva, no debería poder volver a registrarse.
      if ($request->filled('toggleMiAsistencia') && in_array($responsable->id, $usuariosYaReservadosIds)) {
          // Ignoramos su auto-reserva para no crear un duplicado.
          $request->merge(['toggleMiAsistencia' => null]);
      }

      // Filtramos los familiares para reservar solo a los que no tienen ya una reserva.
      $familiaresParaReservar = [];
      if ($request->filled('toggleFamilia') && $request->has('familiares')) {
          $familiaresParaReservar = array_diff($request->familiares, $usuariosYaReservadosIds);
      }
      // --- FIN DE LA NUEVA VALIDACIÓN ---

      $cuposSolicitados = 0;

       // 2. CALCULAR EL TOTAL DE CUPOS NECESARIOS (sin cambios)
      if ($request->filled('toggleMiAsistencia')) {
        $cuposSolicitados++;
      }

      if ($request->filled('toggleFamilia') && $request->has('familiares'))
      {

          $cuposSolicitados += count($request->familiares);
      }
      $cantidadInvitadosSolicitados = 0;
      if ($request->toggleInvitados && $request->has('invitados'))
      {
          $cantidadInvitadosSolicitados = count($request->invitados);
          $cuposSolicitados += $cantidadInvitadosSolicitados;
      }
      if ($cuposSolicitados === 0) {
          return back()->with('danger', 'Debes seleccionar al menos una persona para la reserva.');
      }

      try {
          // 3. EJECUTAR TODO DENTRO DE UNA TRANSACCIÓN
          DB::transaction(function () use ($request, $reporteReunion, $responsable, $cuposSolicitados, $cantidadInvitadosSolicitados, $familiaresParaReservar,  &$nuevasReservas) {

              // Bloqueamos el reporte para evitar que otros procesos lo modifiquen mientras verificamos el aforo.
              $reporteReunion = ReporteReunion::where('id', $reporteReunion->id)->lockForUpdate()->first();


              if ($cantidadInvitadosSolicitados > 0) {
                // Obtenemos cuántos cupos para invitados le quedan a este usuario
                $invitadosDisponibles = $reporteReunion->cantidadDisponibleInvitados($responsable);

                // Comparamos los que solicita con los que tiene disponibles
                if ($cantidadInvitadosSolicitados > $invitadosDisponibles) {
                    throw ValidationException::withMessages([
                        'danger' => 'Solo puedes registrar ' . $invitadosDisponibles . ' invitados más para esta reserva.'
                    ]);
                }
              }

              // Verificamos si hay suficiente aforo disponible
              $aforoDisponible = $reporteReunion->obtenerCantidadDisponible();

              if (!is_null($aforoDisponible) && $cuposSolicitados > $aforoDisponible) {
                  // Si no hay cupos, lanzamos una excepción para detener la transacción.
                  throw ValidationException::withMessages([
                      'danger' => 'Lo sentimos, no hay suficientes cupos disponibles para completar tu reserva.'
                  ]);
              }

              // 4. CREAR LOS REGISTROS DE RESERVA

              // Reserva para el usuario logueado
              if ($request->filled('toggleMiAsistencia')) {
                  $nuevasReservas[] = ReservaReunion::create([
                      'reporte_reunion_id' => $reporteReunion->id,
                      'user_id' => $responsable->id,
                      'responsable_id' => $responsable->id,
                      'autor_creacion_reserva_id' => auth()->id(),
                  ]);
              }

              // Reservas para familiares
              foreach ($familiaresParaReservar as $familiarId) {
                  $nuevasReservas[] = ReservaReunion::create([
                      'reporte_reunion_id' => $reporteReunion->id,
                      'user_id' => $familiarId,
                      'responsable_id' => $responsable->id,
                      'autor_creacion_reserva_id' => auth()->id(),
                  ]);
              }

              // Reservas para invitados
              if ($request->toggleInvitados && $request->has('invitados')) {
                  foreach ($request->invitados as $invitadoData) {
                      $nuevasReservas[] = ReservaReunion::create([
                          'reporte_reunion_id' => $reporteReunion->id,
                          'invitado' => true,
                          'nombre_invitado' => $invitadoData['nombre'],
                          'email_invitado' => $invitadoData['email'] ?? null,
                          'responsable_id' => $responsable->id,
                          'autor_creacion_reserva_id' => auth()->id(),
                      ]);
                  }
              }

              // 5. ACTUALIZAR EL CONTEO DE AFORO OCUPADO
              $reporteReunion->aforo_ocupado += $cuposSolicitados;
              $reporteReunion->save();
          });

      } catch (ValidationException $e) {
          // Si la excepción fue por falta de aforo, volvemos con el mensaje de error.
          return back()->withErrors($e->errors())->withInput();
      } catch (\Exception $e) {
          // Para cualquier otro error inesperado.
          return back()->with('danger', 'Ocurrió un error inesperado al procesar tu reserva. Por favor, inténtalo de nuevo.')->withInput();
      }

      // --- ✅ NUEVO: ENVIAR CORREOS DESPUÉS DE LA TRANSACCIÓN ---
      foreach ($nuevasReservas as $reserva) {
          $destinatarioEmail = $reserva->invitado ? $reserva->email_invitado : $reserva->usuario->email;
          $nombreAsistente = $reserva->invitado ? $reserva->nombre_invitado : $reserva->usuario->nombre(3);

          // Solo enviar si hay un email válido
          if (filter_var($destinatarioEmail, FILTER_VALIDATE_EMAIL)) {
              // Preparamos los datos para el correo
              $mailData = new stdClass();
              $mailData->subject = 'Tu reserva para: ' . $reporteReunion->reunion->nombre;
              $mailData->saludo = 'si';
              $mailData->nombre = $nombreAsistente;
              $mailData->mensaje = "Te confirmamos que tu reserva ha sido realizada con éxito. Adjunto encontrarás tu ticket con el código QR para el ingreso.";

              // Generamos el PDF
              $pdfData = $reserva->generarPdf();
              $pdfFilename = 'Reserva-' . $reserva->id . '.pdf';

              // Enviamos el correo con el PDF adjunto
              Mail::to($destinatarioEmail)->send(new DefaultMail($mailData, $pdfData, $pdfFilename));
          }
      }

      // 6. REDIRECCIONAR A UNA PÁGINA DE ÉXITO
      $origen = $request->input('origen', 'iglesiaVirtual');
      return redirect()->route('reporteReunion.resumenReserva', [
          'reporteReunion' => $reporteReunion,
          'user' => $responsable,
          'origen' => $origen
      ]);
  }

  public function resumenReserva (Request $request, ReporteReunion $reporteReunion, User $user)
  {
    $origen = $request->query('origen', 'iglesiaVirtual');


    $reservas =  $reporteReunion->reservas()
    ->where(function ($query) use ($user) {
        $query->where('user_id', $user->id)
              ->orWhere('responsable_id', $user->id);
    })
    ->get();

    $reunion = $reporteReunion->reunion;
    $configuracion = Configuracion::find(1);

    return view('contenido.paginas.reporte-reuniones.mensaje-reserva-existosa', [
      'reporte' => $reporteReunion,
      'reunion' => $reunion,
      'user' => $user,
      'configuracion' => $configuracion,
      'reservas' => $reservas,
      'origen' => $origen,
    ]);
  }

  public function resumenReservaInvitado (Request $request, ReservaReunion $reserva)
  {

    $origen = $request->query('origen', 'iglesiaVirtual');
    $reporteReunion = ReporteReunion::find($reserva->reporte_reunion_id);
    $reservas =  $reporteReunion->reservas()->where('id', $reserva->id)->get();

    $reunion = $reporteReunion->reunion;
    $configuracion = Configuracion::find(1);

    return view('contenido.paginas.reporte-reuniones.mensaje-reserva-existosa', [
      'reporte' => $reporteReunion,
      'reunion' => $reunion,
      'user' => null,
      'configuracion' => $configuracion,
      'reservas' => $reservas,
      'origen' => $origen,
    ]);
  }

  /*private function generarPdfParaReserva(ReservaReunion $reserva): string
  {
      $configuracion = Configuracion::find(1);
      $reporte = $reserva->reporte;
      $reunion = $reporte->reunion()->withTrashed()->first();
      $nombreAsistente = $reserva->invitado ? $reserva->nombre_invitado : $reserva->usuario->nombre(3);

      $dataQr = json_encode([
          'id' => $reserva->id,
          'nombre' => $nombreAsistente,
          'tipo' => 'reserva'
      ]);

      $pdf = PDF::loadView('contenido.paginas.reporte-reuniones.codigoQr', [
          'title' => 'Reserva QR - ' . $reserva->id,
          'configuracion' => $configuracion,
          'dataQr' => $dataQr,
          'reunion' => $reunion,
          'reporte' => $reporte,
          'nombreAsistente' => $nombreAsistente,
          'reserva' => $reserva,
      ]);

      // Devuelve el contenido del PDF como un string
      return $pdf->output();
  }*/

  public function descargarQrReserva(ReservaReunion $reserva)
  {
    $pdfData = $reserva->generarPdf($reserva);
    $filename = 'Reserva QR - ' . $reserva->id . '.pdf';

    return response($pdfData)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', "inline; filename=\"{$filename}\"");
  }

  public function iglesiaVirtual (Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reporte_reuniones.subitem_proximas_reuniones');

    $configuracion = Configuracion::first();
    $textoBusqueda = '';
    $tagsBusqueda = [];
    $bandera = 0;

    $sedes = Sede::get();
    $reuniones = Reunion::withTrashed()->get(); // Obtenemos también las borradas para los filtros
    $meses = Helpers::meses('largo');
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $user = auth()->user();

    // --- NUEVO: Capturamos el estado del switch ---
    $filtroElegibles = count($request->all()) === 0 ? true : $request->has('filtro_elegibles');

    // Construcción de la consulta base
    $reportesQuery = ReporteReunion::query();

    // Filtros de fecha
    $filtroFechaIni = $request->filtroFechaIni ? Carbon::parse($request->filtroFechaIni)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
    $filtroFechaFin = $request->filtroFechaFin ? Carbon::parse($request->filtroFechaFin)->format('Y-m-d') : Carbon::now()->addDays(30)->format('Y-m-d');
    $reportesQuery->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);

    // Filtros de reuniones y sedes desde la UI
    $reunionesFiltradas = $request->input('reuniones_id', []);
    if (!empty($reunionesFiltradas)) {
        $bandera = 1;
        $reportesQuery->whereIn('reunion_id', $reunionesFiltradas);
        // Lógica para los tags (sin cambios)
        foreach($reuniones->whereIn('id', $reunionesFiltradas) as $reunion) {
            $tag = new stdClass();
            $tag->label = $reunion->nombre;
            $tag->field = 'reuniones_id';
            $tag->value = $reunion->id;
            $tag->fieldAux = '';
            $tagsBusqueda[] = $tag;
        }
    }

    $sedesFiltradas = $request->input('sedes_id', []);
    if (!empty($sedesFiltradas)) {
        $bandera = 1;
        $reportesQuery->whereHas('reunion', function ($q) use ($sedesFiltradas) {
            $q->whereIn('sede_id', $sedesFiltradas);
        });
        // Lógica para los tags (sin cambios)
        foreach($sedes->whereIn('id', $sedesFiltradas) as $sede) {
            $tag = new stdClass();
            $tag->label = $sede->nombre;
            $tag->field = 'sedes_id'; // Corregido para que coincida con el name del select
            $tag->value = $sede->id;
            $tag->fieldAux = '';
            $tagsBusqueda[] = $tag;
        }
    }

    // --- LÓGICA PRINCIPAL: FILTRADO CONDICIONAL Y PAGINACIÓN ---
    if ($filtroElegibles) {
        // Si el switch está activo, filtramos en PHP
        $bandera = 1; // Marcamos que hay un filtro activo
        $tag = new stdClass();
        $tag->label = 'Solo donde puedo asistir';
        $tag->field = 'filtro_elegibles';
        $tag->value = 'on';
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;

        // 1. Obtenemos TODOS los reportes que cumplen los filtros de la base de datos
        $todosLosReportes = $reportesQuery->with([
            'reunion' => fn($query) => $query->withTrashed()->with('sede')
        ])->orderBy('fecha', 'desc')->orderBy('id', 'desc')->get();

        // 2. Filtramos la colección usando la función del modelo
        $reportesFiltrados = $todosLosReportes->filter(function ($reporte) use ($user) {
            return $reporte->elUsuarioPuedeReservar($user);
        });

        // 3. Paginamos manualmente la colección ya filtrada
        $page = $request->get('page', 1);
        $perPage = 12;
        $reportes = new LengthAwarePaginator(
            $reportesFiltrados->forPage($page, $perPage),
            $reportesFiltrados->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

    } else {
        // Si el switch está inactivo, paginamos directamente desde la base de datos (comportamiento original)
        $reportes = $reportesQuery->with([
            'reunion' => fn($query) => $query->withTrashed()->with('sede')
        ])
        ->orderBy('fecha', 'desc')
        ->orderBy('id', 'desc')
        ->paginate(12);
    }

    return view('contenido.paginas.reporte-reuniones.iglesia-virtual', [
        'meses' => $meses,
        'sedes' => $sedes,
        'reportes' => $reportes,
        'reuniones' => $reuniones,
        'rolActivo' => $rolActivo,
        'textoBusqueda' => $textoBusqueda,
        'filtroFechaIni' => $filtroFechaIni,
        'filtroFechaFin' => $filtroFechaFin,
        'reunionesFiltradas' => $reunionesFiltradas,
        'sedesFiltradas' => $sedesFiltradas,
        'bandera' => $bandera,
        'tagsBusqueda' => $tagsBusqueda,
        'configuracion' => $configuracion,
        'filtroElegibles' => $filtroElegibles, // Pasamos el estado del switch a la vista
    ]);
  }


  public function proximasReuniones (Request $request)
  {

    $configuracion = Configuracion::first();
    $textoBusqueda = '';
    $tagsBusqueda = [];
    $bandera = 0;

    $sedes = Sede::get();
    $reuniones = Reunion::withTrashed()->get(); // Obtenemos también las borradas para los filtros
    $meses = Helpers::meses('largo');


    // Construcción de la consulta base
    $reportesQuery = ReporteReunion::query();

    // Filtros de fecha
    $filtroFechaIni = $request->filtroFechaIni ? Carbon::parse($request->filtroFechaIni)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
    $filtroFechaFin = $request->filtroFechaFin ? Carbon::parse($request->filtroFechaFin)->format('Y-m-d') : Carbon::now()->addDays(30)->format('Y-m-d');
    $reportesQuery->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);

    // Filtros de reuniones y sedes desde la UI
    $reunionesFiltradas = $request->input('reuniones_id', []);
    if (!empty($reunionesFiltradas)) {
        $bandera = 1;
        $reportesQuery->whereIn('reunion_id', $reunionesFiltradas);
        // Lógica para los tags (sin cambios)
        foreach($reuniones->whereIn('id', $reunionesFiltradas) as $reunion) {
            $tag = new stdClass();
            $tag->label = $reunion->nombre;
            $tag->field = 'reuniones_id';
            $tag->value = $reunion->id;
            $tag->fieldAux = '';
            $tagsBusqueda[] = $tag;
        }
    }

    $sedesFiltradas = $request->input('sedes_id', []);
    if (!empty($sedesFiltradas)) {
        $bandera = 1;
        $reportesQuery->whereHas('reunion', function ($q) use ($sedesFiltradas) {
            $q->whereIn('sede_id', $sedesFiltradas);
        });
        // Lógica para los tags (sin cambios)
        foreach($sedes->whereIn('id', $sedesFiltradas) as $sede) {
            $tag = new stdClass();
            $tag->label = $sede->nombre;
            $tag->field = 'sedes_id'; // Corregido para que coincida con el name del select
            $tag->value = $sede->id;
            $tag->fieldAux = '';
            $tagsBusqueda[] = $tag;
        }
    }

    // Si el switch está inactivo, paginamos directamente desde la base de datos (comportamiento original)
    $reportes = $reportesQuery->with([
        'reunion' => fn($query) => $query->withTrashed()->with('sede')
    ])
    ->orderBy('fecha', 'desc')
    ->orderBy('id', 'desc')
    ->paginate(12);

    return view('contenido.paginas.reporte-reuniones.proximas-reuniones', [
        'meses' => $meses,
        'sedes' => $sedes,
        'reportes' => $reportes,
        'reuniones' => $reuniones,
        'textoBusqueda' => $textoBusqueda,
        'filtroFechaIni' => $filtroFechaIni,
        'filtroFechaFin' => $filtroFechaFin,
        'reunionesFiltradas' => $reunionesFiltradas,
        'sedesFiltradas' => $sedesFiltradas,
        'bandera' => $bandera,
        'tagsBusqueda' => $tagsBusqueda,
        'configuracion' => $configuracion,
    ]);
  }

  public function eliminarReserva(Request $request, ReservaReunion $reserva)
  {

      // 1. Validar que la reserva no esté registrada como una asistencia.
      if ($reserva->registrada) {
          return back()->with('danger', 'No se puede eliminar una reserva que ya ha sido registrada como asistencia.');
      }

      // ✅ CAMBIO 1: Guardamos los datos necesarios ANTES de eliminar
      $reporte = $reserva->reporte;
      $responsable = $reserva->responsable_id; // Asumiendo que tienes una relación 'responsable' en el modelo ReservaReunion
      $origen = $request->input('origen', 'iglesiaVirtual'); // Obtenemos el origen del input oculto

      try {

          $destinatarioEmail = $reserva->invitado ? $reserva->email_invitado : $reserva->usuario->email;
          $nombreAsistente = $reserva->invitado ? $reserva->nombre_invitado : $reserva->usuario->nombre(3);

          // 2. Usar una transacción para asegurar la integridad de los datos.
          DB::transaction(function () use ($reserva) {
              // Obtenemos el reporte asociado
              $reporte = $reserva->reporte;

              // Eliminamos la reserva
              $reserva->delete();

              // Decrementamos el aforo ocupado solo si el reporte tiene un aforo definido
              if (!is_null($reporte->aforo)) {
                  $reporte->decrement('aforo_ocupado');
              }
          });



          // Solo enviar si hay un email válido
          if (filter_var($destinatarioEmail, FILTER_VALIDATE_EMAIL)) {
              // Preparamos los datos para el correo
              $mailData = new stdClass();
              $mailData->subject = 'Tu reserva para: ' . $reporte->reunion->nombre;
              $mailData->saludo = 'si';
              $mailData->nombre = $nombreAsistente;
              $mailData->mensaje = "Queremos informarte que tu reserva para <b>".$reporte->reunion->nombre."</b> ha sido cancelada. ";

              // Enviamos el correo con el PDF adjunto
              Mail::to($destinatarioEmail)->send(new DefaultMail($mailData));
          }

      } catch (\Exception $e) {
          // Para cualquier otro error inesperado.
          return back()->with('danger', 'Ocurrió un error inesperado al eliminar la reserva.');
      }

      // 3. Redireccionar con un mensaje de éxito.

      if($responsable)
      {
        return redirect()->route('reporteReunion.resumenReserva', [
            'reporteReunion' => $reporte,
            'user' => $responsable,
            'origen' => $origen
        ])->with('success', 'Reserva eliminada correctamente.');
      }else{
        if($origen=='proximasReuniones')
        {
          return redirect()->route('reporteReunion.proximasReuniones')
          ->with('success', 'Reserva eliminada correctamente.');
        }else{
          return redirect()->route('reporteReunion.añadirReservas', [
              'reporteReunion' => $reporte,
          ])->with('success', 'Reserva eliminada correctamente.');
        }
      }
  }

  public function reservarComoInvitado(Request $request)
  {
      // 1. VALIDAR LOS DATOS DEL FORMULARIO
      // Se combinan las reglas de Livewire con la validación del reporte_id
      $request->validate([
          'nombreInvitado' => 'required|string|max:255',
          'emailInvitado' => 'required|email|max:255',
          'reporte_id' => 'required|exists:reporte_reuniones,id'
      ], [
          'nombre.required' => 'El nombre es obligatorio.',
          'email.required'  => 'El correo electrónico es obligatorio.',
          'email.email'     => 'El formato del correo electrónico no es válido.',
      ]);

      // 2. OBTENER LA REUNIÓN Y COMPROBAR EL AFORO
      $reporte = ReporteReunion::find($request->input('reporte_id'));


      if (!$reporte->hayAforoDisponible()) {
          // Si no hay cupos, redirigimos de vuelta al formulario con un error
          return redirect()->back()
              ->withInput() // Devuelve los datos para no tener que escribirlos de nuevo
              ->with('danger', '¡Ups! Se agotaron los cupos mientras completabas el formulario.');
      }

      // 3. CREAR LA RESERVA DEL INVITADO
      $reserva = ReservaReunion::create([
          'reporte_reunion_id' => $reporte->id,
          'invitado' => true,
          'nombre_invitado' => $request->input('nombreInvitado'),
          'email_invitado' => $request->input('emailInvitado'),
          'responsable_id' => null, // Sin responsable, es un invitado
          'autor_creacion_reserva_id' => null, // No hay usuario autenticado
      ]);

      // 4. ACTUALIZAR EL AFORO DE LA REUNIÓN
      $reporte->increment('aforo_ocupado');

      // 5. ENVIAR CORREO DE CONFIRMACIÓN CON PDF
      $emailInvitado = $request->input('emailInvitado');
      if (filter_var($emailInvitado, FILTER_VALIDATE_EMAIL)) {
          // Preparamos los datos para el correo
          $mailData = new stdClass();
          $mailData->subject = 'Tu reserva para: ' . $reporte->reunion->nombre;
          $mailData->saludo = 'si';
          $mailData->nombre = $request->input('nombre');
          $mailData->mensaje = "Te confirmamos que tu reserva ha sido realizada con éxito. Adjunto encontrarás tu ticket con el código QR para el ingreso.";

          // Generamos el PDF
          $pdfData = $reserva->generarPdf();
          $pdfFilename = 'Reserva-' . $reserva->id . '.pdf';

          // Enviamos el correo con el PDF adjunto
          Mail::to($emailInvitado)->send(new DefaultMail($mailData, $pdfData, $pdfFilename));
      }

      // 6. REDIRIGIR CON MENSAJE DE ÉXITO
      return redirect()
      ->route('reporteReunion.resumenReservaInvitado', [
        'reserva' => $reserva, // Laravel tomará el ID del objeto $reserva recién creado
        'origen' => 'proximasReuniones'
      ])
      ->with('success', '¡Tu reserva ha sido confirmada! Aquí tienes el resumen.');
  }

  public function exportarReservasExcel ($reporteReunionId)
  {
    // Genera un nombre de archivo dinámico
        $fileName = 'reservas-reporte-reunion-' . $reporteReunionId . '-' . now()->format('Y-m-d') . '.xlsx';

        // Llama a la clase de exportación, le pasa el ID de la reunión
        // y le dice al navegador que descargue el archivo.
        return Excel::download(new ReservasReporteReunionExport((int) $reporteReunionId), $fileName);
  }

  public function exportarAsistenciasExcel ($reporteReunionId)
  {
      $fileName = 'asistencias-reporte-reunion-' . $reporteReunionId . '-' . now()->format('Y-m-d') . '.xlsx';
      return Excel::download(new AsistenciasReporteReunionExport((int) $reporteReunionId), $fileName);
  }
}
