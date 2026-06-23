<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Barrio;
use App\Models\Configuracion;
use App\Models\Sede;
use App\Models\TipoSede;
use App\Models\TipoUsuario;
use App\Models\SedeDestinatario;
use App\Models\User;
use App\Models\TipoVinculacion;
use App\Models\Matricula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use \stdClass;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DashboardCosechaExport;
use App\Exports\DetalleConsolidacionKpiDashboardExport;
use App\Models\BitacoraSede;
use App\Models\BitacoraTipoUsuario;
use App\Models\BitacoraEstadoCivil;
use App\Models\BitacoraTareaConsolidacion;
use App\Models\BitacoraIntegranteGrupo;
use App\Models\BitacoraCrecimientoUsuario;
use App\Models\ReporteBajaAlta;
use App\Models\BloqueDashboardConsolidacion;
use App\Models\FiltroConsolidacion;


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
    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $rolActivo->verificacionDelPermiso('sedes.subitem_lista_sedes');

    $buscar = '';
    
    // Jerarquía: Las restricciones (ID o Ministerio) tienen prioridad sobre el permiso global
    if ($rolActivo->lista_sedes_sede_id || $rolActivo->hasPermissionTo('sedes.lista_sedes_solo_ministerio')) {
        $query = auth()->user()->sedesEncargadas('query');
    } elseif ($rolActivo->hasPermissionTo('sedes.lista_sedes_todas')) {
        $query = Sede::query();
    } else {
        $query = Sede::whereRaw('1=2');
    }

    // Busqueda por palabra clave
    if ($request->buscar) {
      $buscar = htmlspecialchars($request->buscar);
      $buscar_saneado = Helpers::sanearStringConEspacios($buscar);
      $palabras = explode(' ', $buscar_saneado);

      foreach ($palabras as $palabra) {
        $query->where(function ($q) use ($palabra) {
          $q->where('sedes.nombre', 'LIKE', "%{$palabra}%");
          if (is_numeric($palabra)) {
            $q->orWhere('sedes.id', $palabra);
          }
        });
      }
      $buscar = $request->buscar;
    }

    $sedes = $query->orderBy('sedes.id', 'desc')->paginate(12);


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
    $sede->foto = "default.png";
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
        $imagenPartes = explode(';base64,', $request->foto);
        $imagenBase64 = base64_decode($imagenPartes[1]);
        $nombreFoto = 'sede' . $sede->id . '.png';
        $rutaDestino = 'img/sedes/' . $nombreFoto;
        
        \Illuminate\Support\Facades\Storage::put($rutaDestino, $imagenBase64);
        
        $sede->foto = $nombreFoto;
        $sede->save();
      }
    }

    return back()->with('success', "La sede <b>" . $sede->nombre . "</b> fue creada con éxito.");
  }

  public function modificar(Sede $sede)
  {
    $this->authorize('modificar', $sede);
    $configuracion = Configuracion::find(1);
    $tiposSedes = TipoSede::orderBy('id', 'asc')->get();

    return view(
      'contenido.paginas.sedes.modificar',
      [
        'sede' => $sede,
        'tiposSedes' => $tiposSedes,
        'configuracion' => $configuracion
      ]
    );
  }

  public function editar(Request $request, Sede $sede)
  {
    $this->authorize('modificar', $sede);
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
    // $sede->foto = "default.png";
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
      // Eliminar foto anterior si existe
      if ($sede->foto && $sede->foto !== 'default.png' && $sede->foto !== 'sede.png' && \Illuminate\Support\Facades\Storage::exists('img/sedes/' . $sede->foto)) {
          \Illuminate\Support\Facades\Storage::delete('img/sedes/' . $sede->foto);
      }

      $imagenPartes = explode(';base64,', $request->foto);
      $imagenBase64 = base64_decode($imagenPartes[1]);
      $nombreFoto = 'sede' . $sede->id . '.png';
      $rutaDestino = 'img/sedes/' . $nombreFoto;
      
      \Illuminate\Support\Facades\Storage::put($rutaDestino, $imagenBase64);
      $sede->foto = $nombreFoto;
      $sede->save();
    }

    $sede->save();

    return back()->with('success', "La sede <b>" . $sede->nombre . "</b> fue actualizada con éxito.");
  }

  public function perfil(Sede $sede)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $this->authorize('verPerfil', $sede);
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
        'rolActivo' => $rolActivo,
        'sede' => $sede,
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
    $this->authorize('eliminar', $sede);
    $configuracion = Configuracion::find(1);

    if ($sede->foto && $sede->foto !== 'default.png' && $sede->foto !== 'sede.png' && Storage::exists('img/sedes/' . $sede->foto)) {
        Storage::delete('img/sedes/' . $sede->foto);
    }

    $sede->resetearSede();
    $sede->delete();

    return redirect()->route('sede.lista')->with('success', "La sede <b>" . $sede->nombre . "</b> fue eliminada con éxito.");
  }

  private function getConsolidacionData(Sede $sede, Request $request): array
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $this->authorize('dashboardConsolidacion', $sede);

    // Lógica para Rango de Fechas
    $rangoFechas = $request->rango_fechas;

    if ($rangoFechas) {
      $fechas = explode(' a ', $rangoFechas);
      if (count($fechas) >= 2) {
        $inicio = Carbon::parse(trim($fechas[0]))->startOfDay();
        $fin = Carbon::parse(trim($fechas[1]))->endOfDay();
      } else {
        $inicio = Carbon::parse(trim($fechas[0]))->startOfDay();
        $fin = Carbon::parse(trim($fechas[0]))->endOfDay();
      }
    } else {
      $inicio = Carbon::now()->startOfMonth();
      $fin = Carbon::now()->endOfMonth();
      $rangoFechas = $inicio->format('Y-m-d') . ' a ' . $fin->format('Y-m-d');
    }

    $sedeId = $sede->id;

    // --- CALLBACK DE FILTRO DE SEDE (Histórico basado en bitácoras) ---
    $filtroSedeCallback = function ($query) use ($inicio, $fin, $sedeId) {
      $query->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $sedeId) {
        $subQuery->whereBetween('created_at', [$inicio, $fin])
          ->where('sede_id_nuevo', $sedeId)
          ->whereRaw('id = (
                        SELECT MAX(bs.id) 
                        FROM bitacora_sedes as bs
                        WHERE bs.user_id = bitacora_sedes.user_id 
                        AND bs.created_at BETWEEN ? AND ?
                    )', [$inicio, $fin]);
      });
    };

    // --- CÁLCULOS TIPO CONSOLIDACION DASHBOARD ---

    // 1. Total Cosecha
    $totalCosecha = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) {
              $q->where(function ($q2) {
                $q2->where('habilitado_para_consolidacion', true)
                  ->orWhere('es_miembro_oficial', true);
              });
            });
        });
      })
      ->tap($filtroSedeCallback)
      ->count();

    // 2. Cosecha Efectiva
    $cosechaEfectiva = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) {
              $q->where(function ($q2) {
                $q2->where('habilitado_para_consolidacion', true)
                  ->orWhere('es_miembro_oficial', true);
              });
            });
        });
      })
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
          $sub->whereBetween('created_at', [$inicio, $fin]);
        })
          ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
            $sub->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->where('dado_baja', false);
          });
      })
      ->tap($filtroSedeCallback)
      ->count();

    $porcentajeEfectividad = $totalCosecha > 0 ? round(($cosechaEfectiva / $totalCosecha) * 100, 2) : 0;
    $cosechaDesercion = $totalCosecha - $cosechaEfectiva;

    // Vinculaciones
    $userIdsCosecha = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) {
              $q->where(function ($q2) {
                $q2->where('habilitado_para_consolidacion', true)
                  ->orWhere('es_miembro_oficial', true);
              });
            });
        });
      })
      ->tap($filtroSedeCallback)
      ->pluck('id');

    $vinculacionesCosecha = TipoVinculacion::withCount(['usuarios' => function ($query) use ($userIdsCosecha) {
      $query->withTrashed()->whereIn('users.id', $userIdsCosecha);
    }])->get();

    // --- INDICADOR 2: ESCUELAS ---
    $subQueryLatestDate = Matricula::whereIn('user_id', $userIdsCosecha)
      ->whereBetween('fecha_matricula', [$inicio, $fin])
      ->whereHas('escuela', function ($q) {
        $q->where('habilitada_consolidacion', true);
      })
      ->select('user_id', DB::raw('MAX(fecha_matricula) as max_fecha'))
      ->groupBy('user_id');

    $latestMatriculaIds = Matricula::joinSub($subQueryLatestDate, 'latest_dates', function ($join) {
      $join->on('matriculas.user_id', '=', 'latest_dates.user_id')
        ->on('matriculas.fecha_matricula', '=', 'latest_dates.max_fecha');
    })
      ->whereHas('escuela', function ($q) {
        $q->where('habilitada_consolidacion', true);
      })
      ->select(DB::raw('MAX(matriculas.id) as max_id'))
      ->groupBy('matriculas.user_id')
      ->pluck('max_id');

    $matriculasCosecha = Matricula::whereIn('id', $latestMatriculaIds)->get();
    $totalMatriculas = $matriculasCosecha->count();

    // Sector vs Templo
    $matriculasSectorBase = Matricula::whereIn('id', $latestMatriculaIds)
      ->whereHas('horarioMateriaPeriodo.horarioBase.aula.tipo', function ($q) {
        $q->where('sector', true);
      });
    $matriculasSector = $matriculasSectorBase->count();

    $matriculasTemploBase = Matricula::whereIn('id', $latestMatriculaIds)
      ->whereHas('horarioMateriaPeriodo.horarioBase.aula.tipo', function ($q) {
        $q->where('sector', false);
      });
    $matriculasTemplo = $matriculasTemploBase->count();

    // Edades
    $configuracion = Configuracion::find(1);
    $limiteEdad = $configuracion->limite_menor_edad ?? 18;

    $calcDistribucion = function ($coleccion, $limite) {
      $adultos = 0;
      $menores = 0;
      foreach ($coleccion as $m) {
        if ($m->user && $m->user->fecha_nacimiento) {
          $fechaMatricula = Carbon::parse($m->fecha_matricula);
          $edad = $m->user->fecha_nacimiento->diffInYears($fechaMatricula);
          if ($edad < $limite) {
            $menores++;
          } else {
            $adultos++;
          }
        } else {
          $adultos++;
        }
      }
      return ['adultos' => $adultos, 'menores' => $menores];
    };

    $calcDistribucionMembresia = function ($coleccion, $limite) {
      $adultos = 0;
      $menores = 0;
      foreach ($coleccion as $u) {
        if ($u->fecha_nacimiento) {
          $edad = $u->fecha_nacimiento->age;
          if ($edad < $limite) {
            $menores++;
          } else {
            $adultos++;
          }
        } else {
          $adultos++;
        }
      }
      return ['adultos' => $adultos, 'menores' => $menores];
    };

    $matriculasSectorData = $matriculasSectorBase->with('user:id,fecha_nacimiento')->get();
    $matriculasTemploData = $matriculasTemploBase->with('user:id,fecha_nacimiento')->get();

    $distSector = $calcDistribucion($matriculasSectorData, $limiteEdad);
    $distTemplo = $calcDistribucion($matriculasTemploData, $limiteEdad);

    $sectorAdultos = $distSector['adultos'];
    $sectorMenores = $distSector['menores'];
    $temploAdultos = $distTemplo['adultos'];
    $temploMenores = $distTemplo['menores'];

    $userIdsMatriculados = Matricula::whereIn('id', $latestMatriculaIds)
      ->where('bloqueado', false)
      ->pluck('user_id')
      ->unique();

    $matriculasUnionLibre = User::withTrashed()->whereIn('id', $userIdsMatriculados)
      ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
        $query->whereBetween('created_at', [$inicio, $fin])
          ->whereRaw('id = (SELECT MAX(b_ec.id) FROM bitacora_estados_civiles as b_ec WHERE b_ec.user_id = bitacora_estados_civiles.user_id AND b_ec.created_at BETWEEN ? AND ?)', [$inicio, $fin])
          ->whereHas('estadoCivilNuevo', function ($q) {
            $q->where('es_union_libre', true);
          });
      })->count();

    $matriculasAptos = $totalMatriculas - $matriculasUnionLibre;

    $matriculasDeserciones = Matricula::whereIn('id', $latestMatriculaIds)
      ->where('bloqueado', true)
      ->count();

    $matriculasEfectivos = $totalMatriculas - $matriculasDeserciones;
    $porcentajeEfectividadMatriculas = $totalMatriculas > 0 ? round(($matriculasEfectivos / $totalMatriculas) * 100, 2) : 0;

    // --- GRÁFICAS SEMANALES ---
    $fechasCosecha = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) {
              $q->where(function ($q2) {
                $q2->where('habilitado_para_consolidacion', true)
                  ->orWhere('es_miembro_oficial', true);
              });
            });
        });
      })
      ->tap($filtroSedeCallback)
      ->pluck('created_at');

    $fechasDesercion = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) {
              $q->where(function ($q2) {
                $q2->where('habilitado_para_consolidacion', true)
                  ->orWhere('es_miembro_oficial', true);
              });
            });
        });
      })
      ->whereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
        $sub->whereBetween('created_at', [$inicio, $fin])
          ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
          ->where('dado_baja', true);
      })
      ->tap($filtroSedeCallback)
      ->pluck('created_at');

    $cosechaPorSemana = $fechasCosecha->groupBy(function ($date) {
      return Carbon::parse($date)->startOfWeek()->format('Y-m-d');
    });

    $desercionPorSemana = $fechasDesercion->groupBy(function ($date) {
      return Carbon::parse($date)->startOfWeek()->format('Y-m-d');
    });

    $inicioSemana = $inicio->copy()->startOfWeek();
    $finSemana = $fin->copy()->startOfWeek();
    if ($finSemana->lt($inicioSemana)) $finSemana = $inicioSemana->copy();

    $periodo = \Carbon\CarbonPeriod::create($inicioSemana, '1 week', $finSemana);
    $datosGraficaSemanal = [];

    foreach ($periodo as $fecha) {
      $lunes = $fecha->format('Y-m-d');
      $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
      $cantidad = isset($cosechaPorSemana[$lunes]) ? $cosechaPorSemana[$lunes]->count() : 0;
      $cantidadDesercion = isset($desercionPorSemana[$lunes]) ? $desercionPorSemana[$lunes]->count() : 0;
      $datosGraficaSemanal[] = ['x' => $domingoLabel, 'y' => $cantidad, 'y_desercion' => $cantidadDesercion];
    }

    // --- INDICADOR 3: MEMBRESÍAS ---
    $usuariosMiembrosOficialesQuery = User::withTrashed()
      ->whereIn('id', $userIdsCosecha)
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) {
              $q->where('es_miembro_oficial', true);
            });
        });
      })
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
          $sub->whereBetween('created_at', [$inicio, $fin]);
        })
          ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
            $sub->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->where('dado_baja', false);
          });
      });

    $totalMiembros = (clone $usuariosMiembrosOficialesQuery)->count();

    $miembrosColeccion = (clone $usuariosMiembrosOficialesQuery)->with('tipoVinculacion')->get();

    $miembrosTraslados = $miembrosColeccion->filter(function ($u) {
      return optional($u->tipoVinculacion)->viene_de_otra_iglesia == true;
    });
    $miembrosBautismos = $miembrosColeccion->filter(function ($u) {
      return optional($u->tipoVinculacion)->viene_de_otra_iglesia == false;
    });

    $miembrosTrasladosCount = $miembrosTraslados->count();
    $miembrosBautismosCount = $miembrosBautismos->count();

    $distTraslados = $calcDistribucionMembresia($miembrosTraslados, $limiteEdad);
    $distBautismos = $calcDistribucionMembresia($miembrosBautismos, $limiteEdad);

    $trasladosAdultos = $distTraslados['adultos'];
    $trasladosMenores = $distTraslados['menores'];
    $bautismosAdultos = $distBautismos['adultos'];
    $bautismosMenores = $distBautismos['menores'];

    // Formalización Unión Libre
    $totalUnionLibreMatriculados = User::withTrashed()->whereIn('id', $userIdsMatriculados)
      ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
        $query->whereBetween('created_at', [$inicio, $fin])
          ->whereRaw('id = (SELECT MAX(b_ec.id) FROM bitacora_estados_civiles as b_ec WHERE b_ec.user_id = bitacora_estados_civiles.user_id AND b_ec.created_at BETWEEN ? AND ?)', [$inicio, $fin])
          ->whereHas('estadoCivilNuevo', function ($q) {
            $q->where('es_union_libre', true);
          });
      })->count();

    $miembrosFormalizados = (clone $usuariosMiembrosOficialesQuery)
      ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
        $query->whereBetween('created_at', [$inicio, $fin])
          ->whereRaw('id = (SELECT MAX(b_ec.id) FROM bitacora_estados_civiles as b_ec WHERE b_ec.user_id = bitacora_estados_civiles.user_id AND b_ec.created_at BETWEEN ? AND ?)', [$inicio, $fin])
          ->whereHas('estadoCivilNuevo', function ($q) {
            $q->where('es_union_libre', false);
          });
      })->count();

    $pendientesMembresiaUnionLibre = $totalUnionLibreMatriculados - $miembrosFormalizados;

    $miembrosUbicados = (clone $usuariosMiembrosOficialesQuery)
      ->whereHas('bitacorasIntegranteGrupo', function ($query) use ($inicio, $fin) {
        $query->whereBetween('created_at', [$inicio, $fin])
          ->whereRaw('id = (SELECT MAX(b3.id) FROM bitacora_integrantes_grupo as b3 WHERE b3.user_id = bitacora_integrantes_grupo.user_id AND b3.created_at BETWEEN ? AND ?)', [$inicio, $fin])
          ->where('estado_vinculacion', true);
      })->count();

    $porcentajeEfectividadMembresia = $totalMiembros > 0 ? round(($miembrosUbicados / $totalMiembros) * 100, 2) : 0;
    $efectividadMembresiasAptos = $matriculasEfectivos > 0 ? round(($totalMiembros / $matriculasEfectivos) * 100, 2) : 0;

    // --- GRÁFICAS ADICIONALES ---
    // Gráfica de Matrículas Semanal
    $fechasMatriculas = Matricula::whereIn('id', $latestMatriculaIds)
      ->pluck('fecha_matricula');

    $matriculasPorSemana = $fechasMatriculas->groupBy(function ($date) {
      return Carbon::parse($date)->startOfWeek()->format('Y-m-d');
    });

    $datosMatriculasSemanal = [];
    foreach ($periodo as $fecha) {
      $lunes = $fecha->format('Y-m-d');
      $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
      $cantidad = isset($matriculasPorSemana[$lunes]) ? $matriculasPorSemana[$lunes]->count() : 0;
      $datosMatriculasSemanal[] = ['x' => $domingoLabel, 'y' => $cantidad];
    }

    // Gráfica de Vinculación Semanal
    $vinculacionesIds = $vinculacionesCosecha->pluck('id');
    $vinculacionesNombres = $vinculacionesCosecha->pluck('nombre', 'id');

    $seriesVinculacion = [];
    foreach ($vinculacionesIds as $vId) {
      $seriesVinculacion[$vId] = [
        'name' => $vinculacionesNombres[$vId],
        'data' => []
      ];
    }

    foreach ($periodo as $fecha) {
      $inicioS = $fecha->copy()->startOfDay();
      $finS = $fecha->copy()->endOfWeek()->endOfDay();

      $usuariosSemana = User::withTrashed()
        ->whereBetween('created_at', [$inicio, $fin]) // Filtro general de rango
        ->whereBetween('created_at', [$inicioS, $finS]) // Filtro de la semana
        ->where(function ($query) use ($inicio, $fin) {
          $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
            $subQuery->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->whereHas('tipoUsuarioNuevo', function ($q) {
                $q->where('habilitado_para_consolidacion', true)
                  ->orWhere('es_miembro_oficial', true);
              });
          });
        })
        ->tap($filtroSedeCallback)
        ->get();

      foreach ($vinculacionesIds as $vId) {
        $count = $usuariosSemana->where('tipo_vinculacion_id', $vId)->count();
        $seriesVinculacion[$vId]['data'][] = $count;
      }
    }

    $datosVinculacionSemanal = [
      'labels' => $datosGraficaSemanal ? array_column($datosGraficaSemanal, 'x') : [],
      'series' => array_values($seriesVinculacion)
    ];

    return [
      'sede' => $sede,
      'rangoFechas' => $rangoFechas,
      'totalCosecha' => $totalCosecha,
      'cosechaEfectiva' => $cosechaEfectiva,
      'cosechaDesercion' => $cosechaDesercion,
      'porcentajeEfectividad' => $porcentajeEfectividad,
      'vinculacionesCosecha' => $vinculacionesCosecha,
      'datosGraficaSemanal' => $datosGraficaSemanal,
      'totalMatriculas' => $totalMatriculas,
      'matriculasSector' => $matriculasSector,
      'matriculasTemplo' => $matriculasTemplo,
      'sectorAdultos' => $sectorAdultos,
      'sectorMenores' => $sectorMenores,
      'temploAdultos' => $temploAdultos,
      'temploMenores' => $temploMenores,
      'matriculasUnionLibre' => $matriculasUnionLibre,
      'matriculasAptos' => $matriculasAptos,
      'matriculasDeserciones' => $matriculasDeserciones,
      'matriculasEfectivos' => $matriculasEfectivos,
      'porcentajeEfectividadMatriculas' => $porcentajeEfectividadMatriculas,
      'totalMiembros' => $totalMiembros,
      'miembrosUbicados' => $miembrosUbicados,
      'miembrosTraslados' => $miembrosTrasladosCount,
      'miembrosBautismos' => $miembrosBautismosCount,
      'trasladosAdultos' => $trasladosAdultos,
      'trasladosMenores' => $trasladosMenores,
      'bautismosAdultos' => $bautismosAdultos,
      'bautismosMenores' => $bautismosMenores,
      'totalUnionLibreMatriculados' => $totalUnionLibreMatriculados,
      'miembrosFormalizados' => $miembrosFormalizados,
      'pendientesMembresiaUnionLibre' => $pendientesMembresiaUnionLibre,
      'porcentajeEfectividadMembresia' => $porcentajeEfectividadMembresia,
      'efectividadMembresiasAptos' => $efectividadMembresiasAptos,
      'datosMatriculasSemanal' => $datosMatriculasSemanal,
      'datosVinculacionSemanal' => $datosVinculacionSemanal,
      'rolActivo' => $rolActivo,
      'configuracion' => $configuracion,
      'inicio' => $inicio,
      'fin' => $fin,
    ];
  }

  public function dashboardConsolidacion(Sede $sede, Request $request)
  {
    $this->authorize('dashboardConsolidacion', $sede);
    $data = $this->getConsolidacionData($sede, $request);
    return view('contenido.paginas.sedes.dashboard-consolidacion', $data);
  }

  public function downloadExportSede(Sede $sede, Request $request)
  {
    $data = $this->getConsolidacionData($sede, $request);

    $inicio = $data['inicio'];
    $fin = $data['fin'];

    $tiposVinculaciones = TipoVinculacion::orderBy('id', 'asc')->get();
    $titulosVinculaciones = $tiposVinculaciones->pluck('nombre')->toArray();

    $textoRango = $inicio->format('Y-m-d') . ' a ' . $fin->format('Y-m-d');
    $filtrosExtra = "Sede: {$sede->nombre} | Rango de fechas: {$textoRango}";

    $metricasSede = $data;

    $fila = [
      'Sede / Bloque' => $sede->nombre,
      'Total Cosecha' => $metricasSede['totalCosecha'] == 0 ? '0' : $metricasSede['totalCosecha'],
      'Deserciones' => $metricasSede['cosechaDesercion'] == 0 ? '0' : $metricasSede['cosechaDesercion'],
      'Cosecha Efectiva' => $metricasSede['cosechaEfectiva'] == 0 ? '0' : $metricasSede['cosechaEfectiva'],
      'Efectividad (%)' => $metricasSede['porcentajeEfectividad'] == 0 ? '0' : $metricasSede['porcentajeEfectividad'],
    ];

    foreach ($tiposVinculaciones as $tv) {
      $valorVinculacion = $metricasSede['vinculacionesCosecha']->where('id', $tv->id)->first()->usuarios_count ?? 0;
      $fila[$tv->nombre] = $valorVinculacion == 0 ? '0' : $valorVinculacion;
    }

    $fila['Total matrículas'] = $metricasSede['totalMatriculas'] == 0 ? '0' : $metricasSede['totalMatriculas'];
    $fila['Matrículas efectivas'] = $metricasSede['matriculasEfectivos'] == 0 ? '0' : $metricasSede['matriculasEfectivos'];
    $fila['Efectividad de matrículas (%)'] = $metricasSede['porcentajeEfectividadMatriculas'] == 0 ? '0' : $metricasSede['porcentajeEfectividadMatriculas'];
    $fila['Templo'] = $metricasSede['matriculasTemplo'] == 0 ? '0' : $metricasSede['matriculasTemplo'];
    $fila['Sector'] = $metricasSede['matriculasSector'] == 0 ? '0' : $metricasSede['matriculasSector'];
    $fila['Sector Adultos'] = $metricasSede['sectorAdultos'] == 0 ? '0' : $metricasSede['sectorAdultos'];
    $fila['Sector Warriors'] = $metricasSede['sectorMenores'] == 0 ? '0' : $metricasSede['sectorMenores'];
    $fila['Templo Adultos'] = $metricasSede['temploAdultos'] == 0 ? '0' : $metricasSede['temploAdultos'];
    $fila['Templo Warriors'] = $metricasSede['temploMenores'] == 0 ? '0' : $metricasSede['temploMenores'];
    $fila['Aptos'] = $metricasSede['matriculasAptos'] == 0 ? '0' : $metricasSede['matriculasAptos'];
    $fila['Unión Libre'] = $metricasSede['matriculasUnionLibre'] == 0 ? '0' : $metricasSede['matriculasUnionLibre'];

    $fila['Total Miembros'] = $metricasSede['totalMiembros'] == 0 ? '0' : $metricasSede['totalMiembros'];
    $fila['Ef. Matrículas a Membresías (%)'] = $metricasSede['efectividadMembresiasAptos'] == 0 ? '0' : $metricasSede['efectividadMembresiasAptos'];
    $fila['Ubicados en Grupos'] = $metricasSede['miembrosUbicados'] == 0 ? '0' : $metricasSede['miembrosUbicados'];
    $fila['Ef. Ubicación en Grupos (%)'] = $metricasSede['porcentajeEfectividadMembresia'] == 0 ? '0' : $metricasSede['porcentajeEfectividadMembresia'];
    $fila['Total Unión Libre'] = $metricasSede['totalUnionLibreMatriculados'] == 0 ? '0' : $metricasSede['totalUnionLibreMatriculados'];
    $fila['Pendientes'] = $metricasSede['pendientesMembresiaUnionLibre'] == 0 ? '0' : $metricasSede['pendientesMembresiaUnionLibre'];
    $fila['Formalizados'] = $metricasSede['miembrosFormalizados'] == 0 ? '0' : $metricasSede['miembrosFormalizados'];
    $fila['Ef. Formalización (%)'] = $metricasSede['porcentajeEfectividadMembresia'] == 0 ? '0' : $metricasSede['porcentajeEfectividadMembresia']; 
    $fila['Total Traslados'] = $metricasSede['miembrosTraslados'] == 0 ? '0' : $metricasSede['miembrosTraslados'];
    $fila['Adultos (Traslados)'] = $metricasSede['trasladosAdultos'] == 0 ? '0' : $metricasSede['trasladosAdultos'];
    $fila['Warriors (Traslados)'] = $metricasSede['trasladosMenores'] == 0 ? '0' : $metricasSede['trasladosMenores'];
    $fila['Total Bautismos'] = $metricasSede['miembrosBautismos'] == 0 ? '0' : $metricasSede['miembrosBautismos'];
    $fila['Adultos (Bautismos)'] = $metricasSede['bautismosAdultos'] == 0 ? '0' : $metricasSede['bautismosAdultos'];
    $fila['Warriors (Bautismos)'] = $metricasSede['bautismosMenores'] == 0 ? '0' : $metricasSede['bautismosMenores'];

    $dataExport = [$fila];

    return Excel::download(new DashboardCosechaExport($dataExport, $titulosVinculaciones, $filtrosExtra), 'Dashboard_Cosecha_Sede_' . $sede->nombre . '_' . $inicio->format('Y-m-d') . '.xlsx');
  }

  public function detalleKpi(Sede $sede, Request $request)
  {
    $datos = $this->getKpiQuery($sede, $request);

    $usuarios = $datos['query']->paginate(25)->withQueryString();

    return view('contenido.paginas.sedes.detalle-kpi', [
      'sede' => $sede,
      'usuarios' => $usuarios,
      'kpi' => $datos['kpi'],
      'rangoFechas' => $datos['rangoFechas'],
    ]);
  }

  public function exportarDetalleKpi(Sede $sede, Request $request)
  {
    $datos = $this->getKpiQuery($sede, $request);

    // Obtener todos los registros sin paginación para exportar
    $usuarios = $datos['query']->get();

    // Adaptar datos para el exportador existente
    $datosExport = [
      'kpi' => $datos['kpi'],
      'sedeDetalle' => $sede,
      'rangoFechas' => $datos['rangoFechas'],
      'bloquesSeleccionados' => [], // No aplica para dashboard de sede individual
      'sedesSeleccionadas' => [$sede->id],
    ];

    return Excel::download(
      new DetalleConsolidacionKpiDashboardExport($usuarios, $datosExport),
      'detalle_kpi_sede_' . $sede->nombre . '.xlsx'
    );
  }

  private function getKpiQuery(Sede $sede, Request $request): array
  {
    $kpi = $request->kpi ?? 'cosecha_total';
    $rangoFechas = $request->rango_fechas;
    $search = $request->buscar;

    // Fechas (Misma lógica que el dashboard)
    $inicio = Carbon::now()->startOfMonth()->toDateTimeString();
    $fin = Carbon::now()->toDateTimeString();

    if ($rangoFechas) {
      $fechas = explode(' a ', $rangoFechas);
      $inicio = Carbon::parse($fechas[0])->startOfDay()->toDateTimeString();
      $fechaRawFin = isset($fechas[1]) ? $fechas[1] : $fechas[0];
      $fin = Carbon::parse($fechaRawFin)->endOfDay()->toDateTimeString();
    } else {
      $rangoFechas = Carbon::parse($inicio)->format('Y-m-d') . ' a ' . Carbon::parse($fin)->format('Y-m-d');
    }

    $filtroSedeCallback = function ($query) use ($inicio, $fin, $sede) {
      $query->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $sede) {
        $subQuery->whereBetween('created_at', [$inicio, $fin])
          ->where('sede_id_nuevo', $sede->id)
          ->whereRaw('id = (
                        SELECT MAX(bs.id) 
                        FROM bitacora_sedes as bs
                        WHERE bs.user_id = bitacora_sedes.user_id 
                        AND bs.created_at BETWEEN ? AND ?
                    )', [$inicio, $fin]);
      });
    };

    $query = User::withTrashed()->tap($filtroSedeCallback);

    switch ($kpi) {
        // ================= TAB 1: COSECHA =================
      case 'cosecha_total':
        $query->whereBetween('created_at', [$inicio, $fin])
          ->whereHas('bitacorasTipoUsuario', function ($q) use ($inicio, $fin) {
            $q->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->whereHas('tipoUsuarioNuevo', function ($q2) {
                $q2->where('habilitado_para_consolidacion', true)
                  ->orWhere('es_miembro_oficial', true);
              });
          });
        break;
      case 'cosecha_efectiva':
        $query->whereBetween('created_at', [$inicio, $fin])
          ->whereHas('bitacorasTipoUsuario', function ($q) use ($inicio, $fin) {
            $q->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->whereHas('tipoUsuarioNuevo', function ($q2) {
                $q2->where('habilitado_para_consolidacion', true)
                  ->orWhere('es_miembro_oficial', true);
              });
          })
          ->where(function ($q) use ($inicio, $fin) {
            $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
              $sub->whereBetween('created_at', [$inicio, $fin]);
            })
              ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                  ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                  ->where('dado_baja', false);
              });
          });
        break;
      case 'deserciones':
        $query->whereBetween('created_at', [$inicio, $fin])
          ->whereHas('bitacorasTipoUsuario', function ($q) use ($inicio, $fin) {
            $q->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->whereHas('tipoUsuarioNuevo', function ($q2) {
                $q2->where('habilitado_para_consolidacion', true)
                  ->orWhere('es_miembro_oficial', true);
              });
          })
          ->whereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
            $sub->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->where('dado_baja', true);
          });
        break;

        // ================= TAB 2: ESCUELAS =================
      case 'total_matriculas':
        $query->whereHas('matriculas', function ($q) use ($inicio, $fin) {
          $q->whereBetween('fecha_matricula', [$inicio, $fin])
            ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
        });
        break;
      case 'matriculas_sector':
        $query->whereHas('matriculas', function ($q) use ($inicio, $fin) {
          $q->whereBetween('fecha_matricula', [$inicio, $fin])
            ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true)->where('es_de_sector', true));
        });
        break;
      case 'matriculas_templo':
        $query->whereHas('matriculas', function ($q) use ($inicio, $fin) {
          $q->whereBetween('fecha_matricula', [$inicio, $fin])
            ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true)->where('es_de_sector', false));
        });
        break;
      case 'matriculas_deserciones':
        $query->whereHas('matriculas', function ($q) use ($inicio, $fin) {
          $q->whereBetween('fecha_matricula', [$inicio, $fin])
            ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
        })->whereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
          $sub->whereBetween('created_at', [$inicio, $fin])
            ->where('dado_baja', true);
        });
        break;
      case 'matriculas_efectivos':
        $query->whereHas('matriculas', function ($q) use ($inicio, $fin) {
          $q->whereBetween('fecha_matricula', [$inicio, $fin])
            ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
        })->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
          $sub->whereBetween('created_at', [$inicio, $fin])
            ->where('dado_baja', true);
        });
        break;
      case 'matriculas_aptos':
        // Cosecha Efectiva
        $query->whereBetween('created_at', [$inicio, $fin])
          ->whereHas('bitacorasTipoUsuario', function ($q) use ($inicio, $fin) {
            $q->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->whereHas('tipoUsuarioNuevo', function ($q2) {
                $q2->where('habilitado_para_consolidacion', true)
                  ->orWhere('es_miembro_oficial', true);
              });
          })
          ->where(function ($q) use ($inicio, $fin) {
            $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
              $sub->whereBetween('created_at', [$inicio, $fin]);
            })
              ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                  ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                  ->where('dado_baja', false);
              });
          });
        break;
      case 'matriculas_union_libre':
        $matriculadosEfectivosIds = User::withTrashed()
          ->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $sede) {
            $subQuery->whereBetween('created_at', [$inicio, $fin])
              ->where('sede_id_nuevo', $sede->id)
              ->whereRaw('id = (SELECT MAX(bs.id) FROM bitacora_sedes as bs WHERE bs.user_id = bitacora_sedes.user_id AND bs.created_at BETWEEN ? AND ?)', [$inicio, $fin]);
          })
          ->whereHas('matriculas', function ($q) use ($inicio, $fin) {
            $q->whereBetween('fecha_matricula', [$inicio, $fin])
              ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
          })->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
            $sub->whereBetween('created_at', [$inicio, $fin])
              ->where('dado_baja', true);
          })->pluck('id');

        $unionLibreIds = [];
        if ($matriculadosEfectivosIds->isNotEmpty()) {
          $subQueryBitacora = BitacoraEstadoCivil::whereIn('user_id', $matriculadosEfectivosIds)
            ->whereBetween('created_at', [$inicio, $fin])
            ->select('user_id', DB::raw('MAX(created_at) as max_created_at'))
            ->groupBy('user_id');

          $latestIds = BitacoraEstadoCivil::joinSub($subQueryBitacora, 'latest_helper', function ($join) {
            $join->on('bitacora_estados_civiles.user_id', '=', 'latest_helper.user_id')
              ->on('bitacora_estados_civiles.created_at', '=', 'latest_helper.max_created_at');
          })->select(DB::raw('MAX(bitacora_estados_civiles.id) as max_id'))
            ->groupBy('bitacora_estados_civiles.user_id')
            ->pluck('max_id');

          $unionLibreIds = BitacoraEstadoCivil::whereIn('id', $latestIds)
            ->whereHas('estadoCivilNuevo', fn ($q) => $q->where('es_union_libre', true))
            ->pluck('user_id');
        }
        $query->whereIn('id', $unionLibreIds);
        break;

        // ================= TAB 3: MEMBRESÍA =================
      case 'total_miembros':
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) {
              $q->where('es_miembro_oficial', true);
            });
        })
          ->where(function ($query) use ($inicio, $fin) {
            $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
              $sub->whereBetween('created_at', [$inicio, $fin]);
            })
              ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                  ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                  ->where('dado_baja', false);
              });
          });
        break;
      case 'miembros_ubicados':
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', fn ($q) => $q->where('es_miembro_oficial', true));
        })
          ->where(function ($query) use ($inicio, $fin) {
            $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
              $sub->whereBetween('created_at', [$inicio, $fin]);
            })
              ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                  ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                  ->where('dado_baja', false);
              });
          })
          ->whereHas('bitacorasIntegranteGrupo', function ($query) use ($inicio, $fin) {
            $query->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b3.id) FROM bitacora_integrantes_grupo as b3 WHERE b3.user_id = bitacora_integrantes_grupo.user_id AND b3.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->where('estado_vinculacion', true);
          });
        break;
      case 'bautismos':
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', fn ($q) => $q->where('es_miembro_oficial', true));
        })
          ->where(function ($query) use ($inicio, $fin) {
            $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
              $sub->whereBetween('created_at', [$inicio, $fin]);
            })
              ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                  ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                  ->where('dado_baja', false);
              });
          })
          ->where(function ($query) {
            $query->whereDoesntHave('tipoVinculacion')
              ->orWhereHas('tipoVinculacion', function ($q) {
                $q->where('viene_de_otra_iglesia', false);
              });
          });
        break;
      case 'traslados':
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', fn ($q) => $q->where('es_miembro_oficial', true));
        })
          ->where(function ($query) use ($inicio, $fin) {
            $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
              $sub->whereBetween('created_at', [$inicio, $fin]);
            })
              ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                  ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                  ->where('dado_baja', false);
              });
          })
          ->whereHas('tipoVinculacion', function ($q) {
            $q->where('viene_de_otra_iglesia', true);
          });
        break;
      case 'union_libre_matriculados':
        // Parte A: Pendientes (Matriculados en periodo en Unión Libre)
        $idsA = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
          ->tap($filtroSedeCallback)
          ->whereHas('matriculas', function ($q) use ($inicio, $fin) {
            $q->whereBetween('fecha_matricula', [$inicio, $fin])
              ->where('bloqueado', false)
              ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
          })
          ->whereHas('bitacorasEstadoCivil', function ($q) use ($inicio, $fin) {
            $q->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b_ec.id) FROM bitacora_estados_civiles as b_ec WHERE b_ec.user_id = bitacora_estados_civiles.user_id AND b_ec.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->whereHas('estadoCivilNuevo', fn ($q2) => $q2->where('es_union_libre', true));
          })->pluck('id');

        // Parte B: Miembros Formalizados (Matrimonio actual, Union Libre previo)
        $idsB = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
          ->tap($filtroSedeCallback)
          ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
            $subQuery->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->whereHas('tipoUsuarioNuevo', fn ($q) => $q->where('es_miembro_oficial', true));
          })
          ->where(function ($q) use ($inicio, $fin) {
            $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
              $sub->whereBetween('created_at', [$inicio, $fin]);
            })
              ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                  ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                  ->where('dado_baja', false);
              });
          })
          ->whereHas('bitacorasEstadoCivil', function ($q) use ($inicio, $fin) {
            $q->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b_ec2.id) FROM bitacora_estados_civiles as b_ec2 WHERE b_ec2.user_id = bitacora_estados_civiles.user_id AND b_ec2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->whereHas('estadoCivilNuevo', fn ($q2) => $q2->where('es_matrimonio', true));
          })
          ->whereHas('bitacorasEstadoCivil', function ($q) use ($inicio, $fin) {
            $q->whereBetween('created_at', [$inicio, $fin])
              ->whereHas('estadoCivilNuevo', fn ($q2) => $q2->where('es_union_libre', true));
          })->pluck('id');

        $query->whereIn('id', $idsA->merge($idsB)->unique());
        break;
      case 'miembros_formalizados':
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) {
              $q->where('es_miembro_oficial', true);
            });
        })
          ->where(function ($q) use ($inicio, $fin) {
            $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
              $sub->whereBetween('created_at', [$inicio, $fin]);
            })
              ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                  ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                  ->where('dado_baja', false);
              });
          })
          ->whereHas('bitacorasEstadoCivil', function ($q2) use ($inicio, $fin) {
            $q2->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b_ec2.id) FROM bitacora_estados_civiles as b_ec2 WHERE b_ec2.user_id = bitacora_estados_civiles.user_id AND b_ec2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->whereHas('estadoCivilNuevo', function ($q3) {
                $q3->where('es_matrimonio', true);
              });
          })
          ->whereHas('bitacorasEstadoCivil', function ($q2) use ($inicio, $fin) {
            $q2->whereBetween('created_at', [$inicio, $fin])
              ->whereHas('estadoCivilNuevo', function ($q3) {
                $q3->where('es_union_libre', true);
              });
          });
        break;
      case 'pendientes_membresia_union_libre':
        $idsMatriculados = User::withTrashed()
          ->tap($filtroSedeCallback)
          ->whereHas('matriculas', function ($q) use ($inicio, $fin) {
            $q->whereBetween('fecha_matricula', [$inicio, $fin])
              ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
          })->pluck('id');

        $query->whereIn('id', $idsMatriculados)
          ->whereHas('bitacorasEstadoCivil', function ($q) use ($inicio, $fin) {
            $q->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(b_ec.id) FROM bitacora_estados_civiles as b_ec WHERE b_ec.user_id = bitacora_estados_civiles.user_id AND b_ec.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->whereHas('estadoCivilNuevo', function ($q2) {
                $q2->where('es_union_libre', true);
              });
          });
        break;

      default:
        if (str_starts_with($kpi, 'cosecha_vinculacion_')) {
          $vinculacionId = str_replace('cosecha_vinculacion_', '', $kpi);
          $query->whereBetween('created_at', [$inicio, $fin])
            ->whereHas('bitacorasTipoUsuario', function ($q) use ($inicio, $fin) {
              $q->whereBetween('created_at', [$inicio, $fin])
                ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                ->whereHas('tipoUsuarioNuevo', function ($q2) {
                  $q2->where('habilitado_para_consolidacion', true)
                    ->orWhere('es_miembro_oficial', true);
                });
            })->where('tipo_vinculacion_id', $vinculacionId);
        } else if (str_starts_with($kpi, 'traslados_')) {
          $edadRef = Carbon::now()->subYears(18)->format('Y-m-d');
          $esAdulto = str_contains($kpi, 'adultos');

          $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
            $subQuery->whereBetween('created_at', [$inicio, $fin])
              ->whereHas('tipoUsuarioNuevo', fn ($q) => $q->where('es_miembro_oficial', true));
          })->whereHas('tipoVinculacion', function ($q) {
            $q->where('viene_de_otra_iglesia', true);
          });

          if ($esAdulto) {
            $query->whereNotNull('fecha_nacimiento')->where('fecha_nacimiento', '<=', $edadRef);
          } else {
            $query->where(function ($q) use ($edadRef) {
              $q->whereNull('fecha_nacimiento')->orWhere('fecha_nacimiento', '>', $edadRef);
            });
          }
        } else if (str_starts_with($kpi, 'bautismos_')) {
          $edadRef = Carbon::now()->subYears(18)->format('Y-m-d');
          $esAdulto = str_contains($kpi, 'adultos');

          $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
            $subQuery->whereBetween('created_at', [$inicio, $fin])
              ->whereHas('tipoUsuarioNuevo', fn ($q) => $q->where('es_miembro_oficial', true));
          })->whereHas('tipoVinculacion', function ($q) {
            $q->where('viene_de_otra_iglesia', false);
          });

          if ($esAdulto) {
            $query->whereNotNull('fecha_nacimiento')->where('fecha_nacimiento', '<=', $edadRef);
          } else {
            $query->where(function ($q) use ($edadRef) {
              $q->whereNull('fecha_nacimiento')->orWhere('fecha_nacimiento', '>', $edadRef);
            });
          }
        }
        break;
    }

    if ($search) {
      $buscarSaneado = strtolower(Helpers::sanearStringConEspacios($search));
      $query->where(function ($q) use ($search, $buscarSaneado) {
        $q->whereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', segundo_nombre, primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw('LOWER(telefono_movil) LIKE LOWER(?)', [$search . '%'])
          ->orWhereRaw('LOWER(email) LIKE LOWER(?)', ['%' . $search . '%'])
          ->orWhereRaw('LOWER(identificacion) LIKE LOWER(?)', [$search . '%']);
      });
    }

    return [
      'query' => $query,
      'kpi' => $kpi,
      'rangoFechas' => $rangoFechas,
    ];
  }
}
