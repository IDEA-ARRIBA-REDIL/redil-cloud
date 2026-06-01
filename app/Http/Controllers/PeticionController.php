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
use App\Models\TipoPeticion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class PeticionController extends Controller
{
    public function publicaNueva()
    {
        $configuracion = Configuracion::find(1);
        $paises = Pais::all();
        $tiposPeticiones = TipoPeticion::orderBy('orden', 'asc')->get();

        return view('contenido.paginas.peticiones.publica', [
            'configuracion' => $configuracion,
            'paises' => $paises,
            'tiposPeticiones' => $tiposPeticiones,
        ]);
    }

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
                    ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', \DB::raw('COALESCE(users.genero, peticiones.genero_externo) as genero'))
                    ->get();
            }
        }

        // Filtro por fechas
        $filtroFechaIni = $request->filtroFechaIni ? Carbon::parse($request->filtroFechaIni)->format('Y-m-d') : Carbon::now()->firstOfMonth()->format('Y-m-d');
        $filtroFechaFin = $request->filtroFechaFin ? Carbon::parse($request->filtroFechaFin)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

        $peticiones = $peticiones->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);
        // $textoBusqueda .= '<b>, Rango </b> Del ' . $filtroFechaIni . ' al ' . $filtroFechaFin;

        $arrayPaises = $peticiones->where('pais_id', '!=', null)->unique('pais_id')->pluck('pais_id')->toArray();
        $paises = Pais::whereIn('id', $arrayPaises)->get();

        $paises->map(function ($pais) use ($peticiones, $tiposPeticiones) {
            $peticionesPaises = clone $peticiones;
            $pais->cantidad = $peticionesPaises->where('pais_id', $pais->id)->count();

            $tipos = [];
            foreach ($tiposPeticiones as $tipoPeticion) {
                $item = new stdClass;
                $item->id = $tipoPeticion->id;
                $item->nombre = $tipoPeticion->nombre;
                $item->cantidad = $tipoPeticion->peticiones()->where('pais_id', $pais->id)->select('id')->count();
                $tipos[] = $item;
            }

            $pais->tipos = $tipos;
        });

        // tiposPeticiones
        $tiposPeticiones->map(function ($tipoPeticion) use ($peticiones) {
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

        $item = new stdClass;
        $item->nombre = 'Total peticiones';
        $item->cantidad = $peticiones->count();
        $item->color = 'bg-label-primary';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-notes';
        $item->col = 'col-md-3 col-sm-6';
        $indicadores[] = $item;

        $item = new stdClass;
        $item->nombre = 'Total respondidas';
        $item->cantidad = $peticiones->where('estado', 2)->count();
        $item->color = 'bg-label-success';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-file-like';
        $item->col = 'col-md-3 col-sm-6';
        $indicadores[] = $item;

        $item = new stdClass;
        $item->nombre = 'Paises';
        $item->cantidad = $peticiones->groupBy('pais_id')->count();
        $item->color = 'bg-label-warning';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-world-pin';
        $item->col = 'col-md-2  col-sm-6';
        $indicadores[] = $item;

        $item = new stdClass;
        $item->nombre = 'Hombres';
        $item->cantidad = $peticiones->where('genero', 0)->count();
        $item->color = 'bg-label-warning';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-man';
        $item->col = 'col-md-2  col-sm-6';
        $indicadores[] = $item;

        $item = new stdClass;
        $item->nombre = 'Mujeres';
        $item->cantidad = $peticiones->where('genero', 1)->count();
        $item->color = 'bg-label-warning';
        $item->col = 'col-md-2 col-sm-6';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-woman';
        $indicadores[] = $item;

        if ($peticiones->count() > 0) {

            $peticiones = $peticiones->toQuery()->leftJoin('users', 'peticiones.user_id', '=', 'users.id')
                ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', \DB::raw('COALESCE(users.genero, peticiones.genero_externo) as genero'))
                ->orderBy('peticiones.id', 'desc')->paginate(12);
            $peticiones->map(function ($peticion) {

                if ($peticion->user_id) {
                    $peticion->nombreUsuario = $peticion->primer_nombre.' '.$peticion->segundo_nombre.' '.$peticion->primer_apellido;
                    $peticion->fotoUsuario = $peticion->foto;

                    $telefonosArray = [];
                    $peticion->telefono_fijo ? array_push($telefonosArray, $peticion->telefono_fijo) : '';
                    $peticion->telefono_movil ? array_push($telefonosArray, $peticion->telefono_movil) : '';
                    $peticion->telefono_otro ? array_push($telefonosArray, $peticion->telefono_otro) : '';

                    $peticion->telefonosUsuario = $telefonosArray && is_array($telefonosArray) ? implode(', ', $telefonosArray) : ' Sin datos';
                    $peticion->emailUsuario = $peticion->email ?: 'Sin dato';
                } else {
                    $peticion->nombreUsuario = $peticion->nombre_externo.' (Externo)';
                    $peticion->fotoUsuario = null;
                    $peticion->telefonosUsuario = $peticion->telefono_externo ?: 'Sin dato';
                    $peticion->emailUsuario = $peticion->email_externo ?: 'Sin dato';
                }

                // usuarioCreacion =
                $usuarioCreacion = $peticion->autorCreacion()->withTrashed()->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido')->first();
                $peticion->usuarioCreacion = ($usuarioCreacion && $peticion->user_id != $usuarioCreacion->id)
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
            'tipoPeticionSeleccionada' => $tipoPeticionSeleccionada,
        ]);
    }

    public function gestionar(Request $request, $tipo = 'pendientes')
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
                    ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', \DB::raw('COALESCE(users.genero, peticiones.genero_externo) as genero'))
                    ->get();
            }
        }

        $item = new stdClass;
        $item->nombre = 'Pendientes';
        $item->url = 'pendientes';
        $item->cantidad = $peticiones->where('estado', 1)->count();
        $item->color = 'bg-label-primary';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-clock';
        $indicadores[] = $item;

        $item = new stdClass;
        $item->nombre = 'En proceso';
        $item->url = 'en-proceso';
        $item->cantidad = $peticiones->where('estado', 3)->count();
        $item->color = 'bg-label-info';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-loader';
        $indicadores[] = $item;

        $item = new stdClass;
        $item->nombre = 'Cerradas';
        $item->url = 'cerradas';
        $item->cantidad = $peticiones->where('estado', 2)->count();
        $item->color = 'bg-label-success';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-check';
        $indicadores[] = $item;

        $indicadores = collect($indicadores);

        if ($tipo == 'pendientes' || $tipo == 'sin-responder') {
            $peticiones = $peticiones->where('estado', 1);
            $textoBusqueda .= '<b> Tipo: </b>"Pendientes"';
        } elseif ($tipo == 'cerradas' || $tipo == 'finalizadas') {
            $peticiones = $peticiones->where('estado', 2);
            $textoBusqueda .= '<b> Tipo: </b>"Cerradas"';
        } elseif ($tipo == 'en-proceso' || $tipo == 'resueltas' || $tipo == 'con-seguimiento') {
            $peticiones = $peticiones->where('estado', 3);
            $textoBusqueda .= '<b> Tipo: </b>"En proceso"';
        }

        // Filtro por persona
        if ($request->persona_id) {
            $peticiones = $peticiones->whereIn('user_id', $request->persona_id);
            $persona = User::withTrashed()->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido')->find($request->persona_id);
            $textoBusqueda .= '<b>, Peticiones de: </b>"'.$persona->nombre(3).'"';
            $bandera = 1;

            // Tag por persona
            $tag = new stdClass;
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

            $textoBusqueda .= '<b>, Tipo de peticiones: </b>"'.implode(', ', $tps).'"';
            $bandera = 1;

            $tiposDePeticiones = TipoPeticion::whereIn('id', $request->filtroTipoPeticiones)->select('id', 'nombre')->get();
            foreach ($tiposDePeticiones as $tipo) {
                // Tag por tipo de grupo
                $tag = new stdClass;
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

            $textoBusqueda .= '<b>, Paises: </b>"'.implode(', ', $textoPaises).'"';
            $bandera = 1;
        }

        // filtro por rango de fecha
        $filtroFechaIni = $request->filtroFechaIni ? $request->filtroFechaIni : Carbon::now()->firstOfYear()->format('Y-m-d');
        $filtroFechaFin = $request->filtroFechaFin ? $request->filtroFechaFin : Carbon::now()->format('Y-m-d');
        $peticiones = $peticiones->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);
        $textoBusqueda .= '<b>, Rango </b> Del '.$filtroFechaIni.' al '.$filtroFechaFin;

        // Busqueda por palabra clave
        if ($request->buscar) {
            $buscar = htmlspecialchars($request->buscar);
            $buscar = Helpers::sanearStringConEspacios($buscar);
            $buscar = str_replace(["'"], '', $buscar);
            $buscar_array = explode(' ', $buscar);

            foreach ($buscar_array as $palabra) {
                $peticiones = $peticiones->filter(function ($peticion) use ($palabra) {
                    $respuesta = stristr(Helpers::sanearStringConEspacios($peticion->primer_nombre), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->segundo_nombre), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->primer_apellido), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->segundo_apellido), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->identificacion), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->direccion), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->telefono_movil), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->email), $palabra) !== false;

                    return $respuesta;
                });
            }

            $buscar = $request->buscar;
            $textoBusqueda .= '<b>, Con busqueda: </b>"'.$buscar.'" ';
            $bandera = 1;
        }

        if ($peticiones->count() > 0) {

            $peticiones = $peticiones->toQuery()->leftJoin('users', 'peticiones.user_id', '=', 'users.id')
                ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', \DB::raw('COALESCE(users.genero, peticiones.genero_externo) as genero'))
                ->orderBy('peticiones.id', 'desc')->paginate(12);
            $peticiones->map(function ($peticion) {

                if ($peticion->user_id) {
                    $peticion->nombreUsuario = $peticion->primer_nombre.' '.$peticion->segundo_nombre.' '.$peticion->primer_apellido;
                    $peticion->fotoUsuario = $peticion->foto;

                    $telefonosArray = [];
                    $peticion->telefono_fijo ? array_push($telefonosArray, $peticion->telefono_fijo) : '';
                    $peticion->telefono_movil ? array_push($telefonosArray, $peticion->telefono_movil) : '';
                    $peticion->telefono_otro ? array_push($telefonosArray, $peticion->telefono_otro) : '';

                    $peticion->telefonosUsuario = $telefonosArray && is_array($telefonosArray) ? implode(', ', $telefonosArray) : ' Sin datos';
                    $peticion->emailUsuario = $peticion->email ?: 'Sin dato';
                } else {
                    $peticion->nombreUsuario = $peticion->nombre_externo.' (Externo)';
                    $peticion->fotoUsuario = null;
                    $peticion->telefonosUsuario = $peticion->telefono_externo ?: 'Sin dato';
                    $peticion->emailUsuario = $peticion->email_externo ?: 'Sin dato';
                }

                // usuarioCreacion =
                $usuarioCreacion = $peticion->autorCreacion()->withTrashed()->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido')->first();
                $peticion->usuarioCreacion = ($usuarioCreacion && $peticion->user_id != $usuarioCreacion->id)
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
            'bandera' => $bandera,
            'queUsuariosCargar' => $queUsuariosCargar,
            'persona' => $persona,
            'camposInformeExcel' => $camposInformeExcel,
            'pasosCrecimiento' => $pasosCrecimiento,
            'camposExtras' => $camposExtras,
            'camposPeticiones' => $camposPeticiones,
            'paises' => $paises,
            'filtroPaises' => $filtroPaises,
            'meses' => $meses,
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
        $paises = Pais::orderBy('nombre', 'asc')->get();

        $crearPeticionOtros = $rolActivo->hasPermissionTo('peticiones.crear_peticion_otros');

        return view('contenido.paginas.peticiones.nueva', [
            'rolActivo' => $rolActivo,
            'queUsuariosCargar' => $queUsuariosCargar,
            'tiposPeticiones' => $tiposPeticiones,
            'paises' => $paises,
            'crearPeticionOtros' => $crearPeticionOtros,
        ]);
    }

    public function exito(Peticion $peticion)
    {
        return view('contenido.paginas.peticiones.mensaje-peticion-exitosa', [
            'peticion' => $peticion,
        ]);
    }

    public function crear(Request $request)
    {
        $user = auth()->user();
        $crearPeticionOtros = false;

        if ($user) {
            $rolActivo = $user->roles()->wherePivot('activo', true)->first();
            $crearPeticionOtros = $rolActivo ? $rolActivo->hasPermissionTo('peticiones.crear_peticion_otros') : false;
        }

        $reglas = [
            'persona' => 'nullable|integer',
            'nombre_externo' => $crearPeticionOtros ? 'required_without:persona' : 'nullable',
            'email_externo' => 'nullable|email',
            'tipo_de_petición' => 'required',
            'descripción' => 'required',
        ];

        if (! auth()->check()) {
            $reglas['g-recaptcha-response'] = ['required', new \App\Rules\Recaptcha];
        }

        $request->validate($reglas, [
            'nombre_externo.required_without' => 'El nombre es obligatorio cuando la petición es para una persona externa.',
            'g-recaptcha-response.required' => 'Por favor, verifica que no eres un robot.',
        ]);

        $configuracion = Configuracion::find(1);
        $peticion = new Peticion;

        if ($request->persona && $crearPeticionOtros) {
            $usuario = User::find($request->persona);
            $peticion->user_id = $usuario->id;
            $peticion->pais_id = $usuario->pais_id;
            $emailDestino = $usuario->email;
            $nombreDestino = $usuario->nombre(3);
        } elseif (auth()->check() && ! $crearPeticionOtros) {
            // Si no tiene permiso, la petición es para el usuario logueado
            $usuario = auth()->user();
            $peticion->user_id = $usuario->id;
            $peticion->pais_id = $usuario->pais_id;
            $emailDestino = $usuario->email;
            $nombreDestino = $usuario->nombre(3);
        } else {
            // Es externo (solo si tiene permiso para crear para otros o si es explícitamente externo)
            $peticion->nombre_externo = $request->nombre_externo;
            $peticion->email_externo = $request->email_externo;
            $peticion->telefono_externo = $request->telefono_externo;
            $peticion->genero_externo = $request->genero_externo;
            $peticion->pais_id = $request->pais_id;
            $emailDestino = $request->email_externo;
            $nombreDestino = $request->nombre_externo;
        }

        $peticion->descripcion = $request->descripción;
        $peticion->tipo_peticion_id = $request->tipo_de_petición;
        $peticion->autor_creacion_id = auth()->check() ? auth()->user()->id : null;
        $peticion->estado = 1; // 1=Pendiente, 3=En proceso, 2=Cerrada
        $peticion->fecha = Carbon::now()->format('Y-m-d');
        $peticion->save();

        // Enviar el correo
        $mensaje = $peticion->tipoPeticion->mensaje_parte_1;
        if ($emailDestino != '' && $mensaje != '') {
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
                        'https://api.biblia.com/v1/bible/content/RVR60.txt?passage='.
                          $jsonVersiculos[$random - 1]->cita.
                          '&key='.
                          $key.
                          '&style=neVersePerLineFullReference&culture=es',
                        false,
                        stream_context_create($arrContextOptions)
                    );
                    $mensaje .=
                      '<I>'.$respuestaText.'</I> <B>('.$jsonVersiculos[$random - 1]->titulo.', RVR60)</B></p>';
                }

                $mensaje .= $peticion->tipoPeticion->mensaje_parte_2;

                $mailData = new stdClass;
                $mailData->subject = 'Petición';
                $mailData->nombre = $nombreDestino;
                $mailData->mensaje = $mensaje;

                if ($peticion->tipoPeticion->banner_email != '') {
                    $mailData->banner = tenant_asset('img/email/peticiones/'.$peticion->tipoPeticion->banner_email);
                }

                Mail::to($emailDestino)->send(new DefaultMail($mailData));

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error enviando correo de creacion de peticion ID {$peticion->id}: ".$e->getMessage());
            }
        }

        return redirect()->route('peticion.exito', $peticion->id)->with('success', 'La petición de <b>'.$nombreDestino.'</b> fue creada con éxito.');
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
        if ($parametrosBusqueda && isset($parametrosBusqueda->buscar)) {
            $buscar = htmlspecialchars($parametrosBusqueda->buscar);
            $buscar = Helpers::sanearStringConEspacios($buscar);
            $buscar = str_replace(["'"], '', $buscar);
            $buscar_array = explode(' ', $buscar);

            foreach ($buscar_array as $palabra) {
                $peticiones = $peticiones->filter(function ($peticion) use ($palabra) {
                    $respuesta = stristr(Helpers::sanearStringConEspacios($peticion->primer_nombre), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->segundo_nombre), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->primer_apellido), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->segundo_apellido), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->identificacion), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->direccion), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->telefono_movil), $palabra) !== false ||
                      stristr(Helpers::sanearStringConEspacios($peticion->email), $palabra) !== false;

                    return $respuesta;
                });
            }
        }

        // Elimino
        $cantidad = $peticiones->count();
        foreach ($peticiones as $peticion) {
            $peticion->seguimientos()->delete();
            $peticion->delete();
        }

        $mensaje = $cantidad > 1
          ? 'Las <b>'.$cantidad.'</b> peticiones fueron eliminadas con éxito.'
          : 'La petición fue eliminada con éxito.';

        return back()->with('success', $mensaje);
    }

    public function eliminacion($id)
    {
        $peticion = Peticion::find($id);
        $peticion->seguimientos()->delete();
        $peticion->delete();

        return back()->with('success', 'La petición fue eliminada con éxito.');
    }

    public function generarExcel(Request $request, $tipo)
    {

        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        // verificar si cumple el permiso
        $rolActivo->verificacionDelPermiso('peticiones.boton_descargar_excel');

        $configuracion = Configuracion::find(1);
        $camposPeticiones = collect(Helpers::camposPeticiones());
        $camposPeticiones = $camposPeticiones->whereIn('id', $request->informacionCamposPeticiones);
        $parametrosBusqueda = json_decode($request->parametrosBusqueda);

        $arrayCamposInfoPersonal = $request->informacionPersonal ? $request->informacionPersonal : []; // $arrayCamposInfoPersonal
        $arrayPasosCrecimiento = $request->informacionMinisterial ? $request->informacionMinisterial : []; // $arrayPasosCrecimiento
        $arrayDatosCongregacionales = $request->informacionCongregacional ? $request->informacionCongregacional : []; // $arrayDatosCongregacionales
        $arrayCamposExtra = $request->informacionCamposExtras ? $request->informacionCamposExtras : []; // $arrayCamposExtra

        $nombreArchivo = 'informe_peticiones_'.Carbon::now()->format('Y-m-d-H-i-s').'.xlsx';
        $directorio = 'archivos/peticiones';
        $rutaArchivo = $directorio.'/'.$nombreArchivo;

        Excel::store(
            new PeticionesExport($tipo, $parametrosBusqueda, $camposPeticiones, $arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra),
            $rutaArchivo
        );

        $downloadUrl = tenant_asset($rutaArchivo);

        return back()->with(
            'success',
            'El informe fue generado con éxito, <a href="'.$downloadUrl.'" class=" link-success fw-bold" download="'.$nombreArchivo.'"> descargalo aquí</a>'
        );
    }
}
