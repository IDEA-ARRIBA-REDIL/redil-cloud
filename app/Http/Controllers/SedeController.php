<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Barrio;
use App\Models\Configuracion;
use App\Models\Sede;
use App\Models\TipoSede;
use App\Models\TipoUsuario;
use App\Models\SedeDestinatario;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Storage;

use \stdClass;

use Carbon\Carbon;

class SedeController extends Controller
{

  public function mapa()
  {

    $sedes = SedeDestinatario::all();
    $centro = [
      'lat' => 4.60971, // Latitud de Bogotá
      'lng' => -74.08175 // Longitud de Bogotá
    ];
    //dd($sedes->first())
    return view('contenido.paginas.sedes.mapa',  [
      'sedes' => $sedes,
      'centro' => $centro
    ]);
  }


  public function listar(Request $request)
  {
    //return Sede::get();
    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('sedes.subitem_lista_sedes');

    $sedes = collect();
    $buscar = '';

    if (isset(Sede::find($rolActivo->lista_sedes_sede_id)->id)) {
      $sedes = Sede::where('id', '=', $rolActivo->lista_sedes_sede_id);
    }

    if ($rolActivo->hasPermissionTo('sedes.lista_sedes_todas')) {
      $sedes = Sede::whereRaw('1=1')->get();
    }

    if ($rolActivo->hasPermissionTo('sedes.lista_sedes_solo_ministerio')) {
      $sedes = auth()->user()->sedesEncargadas();
    }

    // Busqueda por palabra clave
    if ($request->buscar) {
      $buscar = htmlspecialchars($request->buscar);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $sedes = $sedes->filter(function ($sede) use ($palabra) {
          return false !== stristr(Helpers::sanearStringConEspacios($sede->nombre), $palabra) ||
            $sede->id === $palabra;
        });
      }
      $buscar = $request->buscar;
    }

    if ($sedes->count() > 0) {
      $sedes = $sedes->toQuery()->orderBy('id', 'desc')->paginate(12);
    } else {
      $sedes = Sede::whereRaw('1=2')->paginate(1);
    }


    return view(
      'contenido.paginas.sedes.listar',
      [
        'sedes' => $sedes,
        'buscar' => $buscar,
        'configuracion' => $configuracion,
        'rolActivo' => $rolActivo
      ]
    );
  }

  public function nueva()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('sedes.subitem_nueva_sede');
    $configuracion = Configuracion::find(1);
    $tiposSedes = TipoSede::orderBy('id', 'asc')->get();

    return view(
      'contenido.paginas.sedes.nueva',
      [
        'rolActivo' => $rolActivo,
        'tiposSedes' => $tiposSedes,
        'configuracion' => $configuracion
      ]
    );
  }

  public function crear(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $configuracion = Configuracion::find(1);

    // Validación
    $validacion = [
      'nombre' => ['required'],
      'tipo_de_sede' => ['required'],
      'fecha_creación' => ['required'],
      'grupoId' => ['required'],
    ];
    $request->validate($validacion);

    /*
      $table->string('foto', 20)->nullable();
    */

    $sede = new Sede;
    $sede->nombre = $request->nombre;
    $sede->telefono = $request->teléfono;
    $sede->tipo_sede_id = $request->tipo_de_sede;
    $sede->grupo_id = $request->grupoId;
    $sede->descripcion = $request->descripcion;
    $sede->fecha_creacion = $request->fecha_creación;
    $sede->capacidad = $request->capacidad;

    $sede->direccion = $request->dirección;
    $sede->barrio_id = $request->barrio_id;
    $sede->foto = "sede.png";
    $sede->default = $request->default ? TRUE : FALSE;

    if ($request->barrio_id) {
      $barrio = Barrio::find($request->barrio_id);
      $localidad = $barrio->localidad;

      if ($localidad) {
        $municipio = $localidad->municipio;

        if ($municipio) {
          $sede->municipio_id = $municipio->id;
          $departamento = $municipio->departamento;

          if ($departamento) {
            $sede->departamento_id = $departamento->id;
            $region = $departamento->region;

            if ($region) {
              $sede->region_id = $region->id;
              $pais = $region->pais;
              if ($pais) {
                $sede->pais_id = $pais->id;
                $continente = $pais->continente;
                if ($continente) {
                  $sede->continente_id = $continente->id;
                }
              }
            }
          }
        }
      }
    }

    $sede->barrio_auxiliar = $request->barrio_auxiliar;

    if ($sede->save()) {
      if ($request->foto) {
        if ($configuracion->version == 1) {
          $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/sedes/banners/');
          !is_dir($path) && mkdir($path, 0777, true);

          $imagenPartes = explode(';base64,', $request->foto);
          $imagenBase64 = base64_decode($imagenPartes[1]);
          $nombreFoto = 'sede-' . $sede->id . '.jpg';
          $imagenPath = $path . $nombreFoto;
          file_put_contents($imagenPath, $imagenBase64);
          $sede->foto = $nombreFoto;
          $sede->save();
        } else {
          /*
          $s3 = AWS::get('s3');
          $s3->putObject(array(
            'Bucket'     => $_ENV['aws_bucket'],
            'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
            'SourceFile' => "img/temp/".Input::get('foto-hide'),
          ));
          */
        }
      }
    }

    return back()->with('success', "La sede <b>" . $sede->nombre . "</b> fue creada con éxito.");
  }

  public function modificar(Sede $sede)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('sedes.opcion_modificar_sede');
    $configuracion = Configuracion::find(1);
    $tiposSedes = TipoSede::orderBy('id', 'asc')->get();

    return view(
      'contenido.paginas.sedes.modificar',
      [
        'sede' => $sede,
        'rolActivo' => $rolActivo,
        'tiposSedes' => $tiposSedes,
        'configuracion' => $configuracion
      ]
    );
  }

  public function editar(Request $request, Sede $sede)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $configuracion = Configuracion::find(1);

    // Validación
    $validacion = [
      'nombre' => ['required'],
      'tipo_de_sede' => ['required'],
      'fecha_creación' => ['required'],
      'grupoId' => ['required'],
    ];
    $request->validate($validacion);

    $sede->nombre = $request->nombre;
    $sede->telefono = $request->teléfono;
    $sede->tipo_sede_id = $request->tipo_de_sede;
    $sede->grupo_id = $request->grupoId;
    $sede->descripcion = $request->descripcion;
    $sede->fecha_creacion = $request->fecha_creación;
    $sede->capacidad = $request->capacidad;

    $sede->direccion = $request->dirección;
    $sede->barrio_id = $request->barrio_id;
    $sede->foto = "sede.png";
    $sede->default = $request->default ? TRUE : FALSE;

    if ($request->barrio_id) {
      $barrio = Barrio::find($request->barrio_id);
      $localidad = $barrio->localidad;

      if ($localidad) {
        $municipio = $localidad->municipio;

        if ($municipio) {
          $sede->municipio_id = $municipio->id;
          $departamento = $municipio->departamento;

          if ($departamento) {
            $sede->departamento_id = $departamento->id;
            $region = $departamento->region;

            if ($region) {
              $sede->region_id = $region->id;
              $pais = $region->pais;
              if ($pais) {
                $sede->pais_id = $pais->id;
                $continente = $pais->continente;
                if ($continente) {
                  $sede->continente_id = $continente->id;
                }
              }
            }
          }
        }
      }
    }

    $sede->barrio_auxiliar = $request->barrio_auxiliar;

    if ($request->foto) {
      if ($configuracion->version == 1) {
        $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/sedes/banners/');
        !is_dir($path) && mkdir($path, 0777, true);

        $imagenPartes = explode(';base64,', $request->foto);
        $imagenBase64 = base64_decode($imagenPartes[1]);
        $nombreFoto = 'sede-' . $sede->id . '.jpg';
        $imagenPath = $path . $nombreFoto;
        file_put_contents($imagenPath, $imagenBase64);
        $sede->foto = $nombreFoto;
      } else {
        /*
        $s3 = AWS::get('s3');
        $s3->putObject(array(
          'Bucket'     => $_ENV['aws_bucket'],
          'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
          'SourceFile' => "img/temp/".Input::get('foto-hide'),
        ));
        */
      }
    }

    $sede->save();

    return back()->with('success', "La sede <b>" . $sede->nombre . "</b> fue creada con éxito.");
  }

  public function perfil(Sede $sede)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('sedes.opcion_ver_perfil_sede');
    $configuracion = Configuracion::find(1);
    $meses = Helpers::meses('corto');

    $grupoPrincipal = $sede->grupo;

    // crecimientoGrupos
    $serieCrecimientoGrupos = [];
    $dataCrecimientoGrupos = [];
    $acumulador = 0;
    for ($i = 11; $i >= 0; $i--) {
      $mes = Carbon::now()->firstOfMonth()->subMonth($i)->month;
      $año = Carbon::now()->firstOfMonth()->subMonth($i)->year;
      $serieCrecimientoGrupos[] = $meses[$mes - 1];
      $cantidadGrupos = $sede->grupos()
        ->select('grupos.id', 'grupos.fecha_apertura')
        ->whereYear('grupos.fecha_apertura', $año)
        ->whereMonth('grupos.fecha_apertura', $mes)
        ->count();
      $acumulador += $cantidadGrupos;
      $dataCrecimientoGrupos[] = $acumulador;
    }

    // crecimientoPersonas
    $serieCrecimientoPersonas = [];
    $dataCrecimientoPersonas = [];
    $acumulador = 0;
    for ($i = 11; $i >= 0; $i--) {
      $mes = Carbon::now()->firstOfMonth()->subMonth($i)->month;
      $año = Carbon::now()->firstOfMonth()->subMonth($i)->year;
      $serieCrecimientoPersonas[] = $meses[$mes - 1];
      $cantidadPersonas = $sede->usuarios()
        ->select("users.id", "users.created_at")
        ->whereNull('users.deleted_at')
        ->whereYear('users.created_at', $año)
        ->whereMonth('users.created_at', $mes)
        ->count();
      $acumulador += $cantidadPersonas;
      $dataCrecimientoPersonas[] = $acumulador;
    }

    $personas = $sede->usuarios()->select('users.id', 'fecha_nacimiento', 'genero', 'tipo_usuario_id', 'genero')->get();
    $personas->map(function ($persona) {
      $persona->edad =  $persona->edad();
    });

    // edades
    $rangoEdades = Configuracion::find(1)->rangoEdad()->orderBy('id', 'asc')->get();
    $rangoEdades->map(function ($rango) use ($personas) {
      $rango->cantidad = $personas->where('edad', '>=', $rango->edad_minima)->where('edad', '<=', $rango->edad_maxima)->count();
    });

    $labelsRangoEdades = $rangoEdades->pluck('nombre')->toArray();
    $seriesRangoEdades = $rangoEdades->pluck('cantidad')->toArray();

    // tipo de usuarios
    $tiposUsuarios = TipoUsuario::select('id', 'nombre')->where('visible', true)->get();
    $tiposUsuarios->map(function ($tipo) use ($personas) {
      $tipo->cantidad = $personas->where('tipo_usuario_id', $tipo->id)->count();
    });

    $labelsTiposUsuarios = $tiposUsuarios->pluck('nombre')->toArray();
    $seriesTiposUsuarios = $tiposUsuarios->pluck('cantidad')->toArray();

    // Por sexo
    $tiposDeSexo = [];

    $cantidadMasculino = $personas->where('genero', 0)->count();
    $item = new stdClass();
    $item->nombre = 'Masculino';
    $item->cantidad = $cantidadMasculino;
    $tiposDeSexo[] = $item;

    $cantidadFemenino = $personas->where('genero', 1)->count();
    $item = new stdClass();
    $item->nombre = 'Femenino';
    $item->cantidad = $cantidadFemenino;
    $tiposDeSexo[] = $item;

    $labelsTiposSexos = ['Masculino', 'Femenino'];
    $seriesTiposSexos = [$cantidadMasculino, $cantidadFemenino];

    return view(
      'contenido.paginas.sedes.perfil',
      [
        'sede' => $sede,
        'rolActivo' => $rolActivo,
        'configuracion' => $configuracion,
        'grupoPrincipal' => $grupoPrincipal,
        'serieCrecimientoGrupos' => $serieCrecimientoGrupos,
        'dataCrecimientoGrupos' => $dataCrecimientoGrupos,
        'serieCrecimientoPersonas' => $serieCrecimientoGrupos,
        'dataCrecimientoPersonas' => $dataCrecimientoPersonas,
        'rangoEdades' => $rangoEdades,
        'labelsRangoEdades' => $labelsRangoEdades,
        'seriesRangoEdades' => $seriesRangoEdades,
        'tiposUsuarios' => $tiposUsuarios,
        'labelsTiposUsuarios' => $labelsTiposUsuarios,
        'seriesTiposUsuarios' => $seriesTiposUsuarios,
        'tiposDeSexo' => $tiposDeSexo,
        'labelsTiposSexos' => $labelsTiposSexos,
        'seriesTiposSexos' => $seriesTiposSexos,
      ]
    );
  }

  public function eliminar(Sede $sede)
  {
    $configuracion = Configuracion::find(1);

    if ($sede->foto != 'default.png')
      Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/img/sedes/banners' . '/' . $sede->foto);

    $sede->resetearSede();
    $sede->delete();

    return redirect()->route('sede.lista')->with('success', "La sede <b>" . $sede->nombre . "</b> fue eliminada con éxito.");
  }
}
