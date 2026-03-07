@extends('layouts/layoutMaster')

@section('title', 'User Profile - Profile')

@section('vendor-style')
  @vite([
  'resources/assets/vendor/scss/pages/page-profile.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  ])
@endsection

<!-- Page -->
@section('vendor-script')
  @vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js',
  ])
@endsection

@section('page-script')

<script>
  function darBajaAlta(usuarioId, $tipo)
  {
    Livewire.dispatch('abrirModalBajaAlta', { usuarioId: usuarioId, tipo: $tipo });
  }

  function comprobarSiTieneRegistros(usuarioId)
  {
    Livewire.dispatch('comprobarSiTieneRegistros', { usuarioId: usuarioId });
  }

  function eliminacionForzada(usuarioId)
  {
    Livewire.dispatch('confirmarEliminacion', { usuarioId: usuarioId });
  }
</script>

<script>
  let cardColor, headingColor, labelColor, borderColor, legendColor;

  const chartColors = {
    column: {
      series1: '#826af9',
      series2: '#d2b0ff',
      bg: '#f8d3ff'
    },
    donut: {
      series1: '#fee802',
      series2: '#3fd0bd',
      series3: '#826bf8',
      series4: '#2b9bf4'
    },
    area: {
      series1: '#29dac7',
      series2: '#60f2ca',
      series3: '#a5f8cd'
    }
  };

  // grafico reporte reunion
  const graficoReportesReunion = document.querySelector('#graficoReportesReunion'),
    dataReportesReunion = JSON.parse(<?php print json_encode(json_encode($dataReportesReunion)); ?>),
    serieReporesReunion = JSON.parse(<?php print json_encode(json_encode($serieReporesReunion)); ?>),
    graficoReportesReunionConfig = {
      chart: {
        height: 200,
        type: 'area',
        parentHeightOffset: 0,
        toolbar: {
          show: false
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: false,
        curve: 'straight'
      },
      legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'start',
        labels: {
          colors: legendColor,
          useSeriesColors: false
        }
      },
      grid: {
        borderColor: borderColor,
        xaxis: {
          lines: {
            show: true
          }
        }
      },
      colors: [chartColors.area.series1],
      series: [{
        name: 'Asistencias',
        data: dataReportesReunion
      }, ],
      xaxis: {
        categories: serieReporesReunion,
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            colors: labelColor,
            fontSize: '13px'
          }
        }
      },
      yaxis: {
        min: 0,
        labels: {
          formatter: function(val) {
            return val.toFixed(0)
          },
          style: {
            colors: labelColor,
            fontSize: '13px'
          }
        }
      },


      fill: {
        opacity: 1,
        type: 'solid'
      },
      tooltip: {
        shared: false
      }
    };
  if (typeof graficoReportesReunion !== undefined && graficoReportesReunion !== null) {
    areaChartReunion = new ApexCharts(graficoReportesReunion, graficoReportesReunionConfig);
    areaChartReunion.render();
  }
  // grafico reporte reunion

  // grafico reporte grupo
  const graficoReportesGrupo = document.querySelector('#graficoReportesGrupo'),
    dataReportesGrupo = JSON.parse(<?php print json_encode(json_encode($dataReportesGrupo)); ?>),
    serieReporesGrupo = JSON.parse(<?php print json_encode(json_encode($serieReporesGrupo)); ?>),
    graficoReportesGrupoConfig = {
      chart: {
        height: 200,
        type: 'area',
        parentHeightOffset: 0,
        toolbar: {
          show: false
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: false,
        curve: 'straight'
      },
      legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'start',
        labels: {
          colors: legendColor,
          useSeriesColors: false
        }
      },
      grid: {
        borderColor: borderColor,
        xaxis: {
          lines: {
            show: true
          }
        }
      },
      colors: [chartColors.area.series1],
      series: [{
        name: 'Asistencias',
        data: dataReportesGrupo
      }, ],
      xaxis: {
        categories: serieReporesGrupo,
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            colors: labelColor,
            fontSize: '13px'
          }
        }
      },
      yaxis: {
        min: 0,
        labels: {
          formatter: function(val) {
            return val.toFixed(0)
          },
          style: {
            colors: labelColor,
            fontSize: '13px'
          }
        }
      },


      fill: {
        opacity: 1,
        type: 'solid'
      },
      tooltip: {
        shared: false
      }
    };
  if (typeof graficoReportesGrupo !== undefined && graficoReportesGrupo !== null) {
    areaChartGrupo = new ApexCharts(graficoReportesGrupo, graficoReportesGrupoConfig);
    areaChartGrupo.render();
  }
  // grafico reporte grupo
</script>

@endsection


@section('content')

  @include('layouts.status-msn')
  @livewire('Usuarios.modal-baja-alta')

  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-5">
        <div class="user-profile-header-banner ">
          <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img//usuarios/banner-usuario/profile-banner.png') : $configuracion->ruta_almacenamiento.'/img//usuarios/banner-usuario/profile-banner.png' }}" alt="Banner image" class="rounded-top">
        </div>
        <div class="user-profile-header d-flex flex-column flex-md-row text-md-start text-center mb-8 mx-5">
          <div class="flex-shrink-0 mt-n5 mx-md-0 mx-auto">
            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$usuario->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$usuario->foto }}" alt="{{ $usuario->foto }}" class="d-block h-auto ms-0 ms-md-4 rounded-circle user-profile-img">
          </div>
          <div class="flex-grow-1 mt-3 mt-md-5">
            <div class="d-flex align-items-md-end align-items-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
              <div class="user-profile-info">
                <h5 class="mb-2 mt-md-6 fw-semibold">{{ $usuario->nombre(3) }}</h5>
                <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                  <li class="list-inline-item d-flex gap-1">
                    <span class="badge text-white rounded-pill px-6 fw-light " style="background-color: {{ $usuario->tipoUsuario->color }}">
                      <i class="{{ $usuario->tipoUsuario->icono }} fs-6"></i> {{ $usuario->tipoUsuario->nombre }}
                    </span>
                  </li>
                </ul>
              </div>
              <div class="dropdown">
                <button type="button" class="btn btn-sm p-2 rounded-3 btn-outline-primary waves-effectdropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="mx-1">Gestionar perfil</span>
                  <i class="pl-5 ti ti-edit"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">

                  @if($rolActivo->hasPermissionTo('personas.opcion_dar_de_alta_asistente'))
                    @if($usuario->trashed())
                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="darBajaAlta('{{$usuario->id}}', 'alta')">Dar de alta</a></li>
                    @endif
                  @endif

                  <!-- opcion modificar  -->
                  @if($usuario->esta_aprobado==TRUE)
                    @foreach( auth()->user()->formularios(2, $usuario->edad()) as $formulario)
                      @can('modificarUsuarioPolitica', [App\Models\User::class, $formulario])
                        <li><a class="dropdown-item" href="{{ route('usuario.modificar', [$formulario, $usuario]) }}">{{$formulario->label}}</a></li>
                      @endcan
                    @endforeach
                  @elseif ($usuario->esta_aprobado==FALSE)
                    @if($rolActivo->hasPermissionTo('personas.privilegio_modificar_asistentes_desaprobados'))
                      @foreach( auth()->user()->formularios(2, $usuario->edad()) as $formulario)
                        @can('modificarUsuarioPolitica', [App\Models\User::class, $formulario])
                          <li><a class="dropdown-item" href="{{ route('usuario.modificar', [$formulario, $usuario]) }}">{{$formulario->label}}</a></li>
                        @endcan
                      @endforeach
                    @endif
                  @endif
                  <!-- / opcion modificar  -->

                  @can('informacionCongregacionalPolitica', $usuario)
                  <li><a class="dropdown-item" href="{{ route('usuario.informacionCongregacional', ['formulario' => 0 ,'usuario' => $usuario]) }}">Info. congregacional</a></li>
                  @endcan

                  @can('relacionesFamiliaresUsuarioPolitica', $usuario)
                  <li><a class="dropdown-item" href="{{ route('usuario.relacionesFamiliares', ['formulario' => 0 , 'usuario' => $usuario]) }}">Relaciones familiares</a></li>
                  @endcan

                  @can('geoasignacionUsuarioPolitica', $usuario)
                  <li><a class="dropdown-item" href="{{ route('usuario.geoAsignacion', ['formulario' => 0 ,'usuario' => $usuario]) }}">Geo asignación</a></li>
                  @endif

                  @if($rolActivo->hasPermissionTo('personas.opcion_cambiar_contrasena_asistente'))
                  <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalCambioContrasena" onclick="event.preventDefault(); document.getElementById('formCambioContrasena').setAttribute('action', 'usuarios/{{$usuario->id}}/cambiar-contrasena');">Cambiar contraseña</a></li>

                  <form method="POST" id="cambiarContraseñaDefault_{{$usuario->id}}" action="{{ route('usuario.cambiarContrasenaDefault',  ['usuario' => $usuario ]) }}">
                    @csrf
                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('cambiarContraseñaDefault_{{$usuario->id}}').submit();">Cambiar contraseña default</a></li>
                  </form>
                  @endif

                  @if($rolActivo->hasPermissionTo('personas.opcion_descargar_qr'))
                  <li><a class="dropdown-item" href="{{ route('usuario.descargarCodigoQr', $usuario) }}">Código QR</a></li>
                  @endif

                  <hr class="dropdown-divider">
                  @if($rolActivo->hasPermissionTo('personas.opcion_dar_de_baja_asistente'))
                    @if($usuario->trashed()!=TRUE)
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="darBajaAlta('{{$usuario->id}}', 'baja')">Dar de baja</a></li>
                    @endif
                  @endif
                  @if($rolActivo->hasPermissionTo('personas.opcion_eliminar_asistente'))
                    @if($usuario->trashed()!=TRUE)
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="comprobarSiTieneRegistros('{{$usuario->id}}')">Eliminar</a></li>
                    @endif
                  @endif
                  @if($rolActivo->hasPermissionTo('personas.eliminar_asistentes_forzadamente'))
                    @if($usuario->trashed()==TRUE)
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="eliminacionForzada('{{$usuario->id}}')">Eliminación forzada </a></li>
                    @endif
                  @endif

                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Header -->

  @if($configuracion->vista_perfil_usuario_clasica==false)
  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-10 p-1 border-1">
        <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">
            @can('verPerfilUsuarioPolitica', [$usuario, 'principal'])
            <li class="nav-item flex-fill"><a id="tap-principal" href="{{ route('usuario.perfil', $usuario) }}" class="nav-link p-3 waves-effect waves-light" data-tap="principal"><i class='ti-xs ti ti-user-check me-2'></i> Principal</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'familia'])
            <li class="nav-item flex-fill"><a id="tap-familia" href="{{ route('usuario.perfil.familia', $usuario) }}" class="nav-link p-3 waves-effect waves-light "  data-tap="familia"><i class='ti-xs ti ti-home-heart me-2'></i> Familia</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'congregacion'])
            <li class="nav-item flex-fill"><a id="tap-congregacion" href="{{ route('usuario.perfil.congregacion', $usuario) }}" class="nav-link p-3 waves-effect waves-light active" data-tap="congregacion"><i class='ti-xs ti ti-building-church me-2'></i> Congregación</a></li>
            @endcan

            <li class="nav-item flex-fill"><a id="tap-otro1" href="{{ route('usuario.historial-escuelas', $usuario) }}" class="nav-link p-3 waves-effect waves-light" data-tap="escuelas"><i class='ti-xs ti ti-school me-2'></i> Escuelas</a></li>
            <li class="nav-item flex-fill"><a id="tap-otro2"href="javascript:void(0);" class="nav-link p-3 waves-effect waves-light" data-tap="otro2"><i class='ti-xs ti ti-report-money me-2'></i> Financiera</a></li>
            <li class="nav-item flex-fill"><a id="tap-otro3"href="javascript:void(0);" class="nav-link p-3 waves-effect waves-light" data-tap="otro3"><i class='ti-xs ti ti-album me-2'></i> Hitos</a></li>
          </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->
  @endif

  <!-- Principal-->
  <div id="div-principal" class="row g-4">

    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header align-items-center">
          <p class="card-text text-uppercase fw-bold">Información congregacional</p>
        </div>
        <div class="card-body pb-3">
          <ul class="list-unstyled mb-4 mt-2">
            <li class="d-flex align-items-center mb-1"><i class="ti ti-point text-heading"></i><span class="fw-medium mx-2 text-heading">Vinculado por:</span> <span>{{ $usuario->tipo_vinculacion_id ? $usuario->tipoVinculacion->nombre : 'Sin dato' }}</span></li>
            <li class="d-flex align-items-center mb-1"><i class="ti ti-point text-heading"></i><span class="fw-medium mx-2 text-heading">Última asistencia a grupo:</span> <span>{{ $usuario->ultimo_reporte_grupo ? Carbon\Carbon::parse($usuario->ultimo_reporte_grupo)->locale('es')->isoFormat(('DD MMMM Y')) : 'Sin dato' }}</span></li>
            <li class="d-flex align-items-center mb-1"><i class="ti ti-point text-heading"></i><span class="fw-medium mx-2 text-heading">Sede:</span> <span>{{ $usuario->sede_id ? $usuario->sede->nombre : 'Sin dato' }}</span></li>
            <li class="d-flex align-items-center mb-1"><i class="ti ti-point text-heading"></i><span class="fw-medium mx-2 text-heading">Roles:</span> <span>{{ count($roles) > 0 ? implode(',',$roles) : 'Sin dato' }}</span></li>
            <li class="d-flex align-items-center mb-1"><i class="ti ti-point text-heading"></i><span class="fw-medium mx-2 text-heading">Servicios prestados en grupos:</span><span> {{ $serviciosPrestadosEnGrupos->count() < 1 ? 'Sin dato' : '' }}</span></li>

            @if($serviciosPrestadosEnGrupos->count() > 0)
            <ul class="p-0 pt-2 m-0 d-flex flex-column">
              @foreach($serviciosPrestadosEnGrupos as $servicio)
              <li class="d-flex gap-3 align-items-center mb-1 mx-2 pb-1">
                <div class="badge rounded bg-label-info p-1"><i class="ti ti-circle-check ti-sm"></i></div>
                <div>
                  <h6 class="mb-0 text-nowrap">{{ $servicio->nombre }}</h6>
                  <small class="text-muted"><b>{{ $servicio->nombreTipoGrupo }}</b> | {{ $servicio->nombreGrupo }} </small>
                </div>
              </li>
              @endforeach
            </ul>
            @endif


          </ul>
        </div>
      </div>

      <!-- Ministerio a cargo -->
      @if($gruposEncargados->count() > 0)
      <div class="card card-action mb-4">
        <div class="card-header align-items-center pb-0">
          <p class="card-text text-uppercase fw-bold">Grupos que dirijo</p><br>
        </div>
        <div class="card-body pb-3">

          <ul class="p-0 m-0 d-flex flex-column">
            <li class="d-flex gap-3 align-items-center pt-2 mx-2 mb-1 pb-1">
              <div class="badge rounded bg-label-primary p-1"><i class="ti ti-users-group ti-sm"></i></div>
              <div>
                <h6 class="mb-0 text-nowrap">Grupos directos</h6>
                <small class="text-muted">{{ $totalGruposDirectos }}</small>
              </div>
            </li>
            <li class="d-flex gap-3 align-items-center mb-1 mx-2 pb-1">
              <div class="badge rounded bg-label-warning p-1"><i class="ti ti-users-group ti-sm"></i></div>
              <div>
                <h6 class="mb-0 text-nowrap">Grupos indirectos</h6>
                <small class="text-muted">{{ $totalGruposDirectos }}</small>
              </div>
            </li>
            <li class="d-flex gap-3 align-items-center mb-1 mx-2 pb-1">
              <div class="badge rounded bg-label-info p-1"><i class="ti ti-users-group ti-sm"></i></div>
              <div>
                <h6 class="mb-0 text-nowrap">Total grupos</h6>
                <small class="text-muted">{{ $totalGruposDirectos }}</small>
              </div>
            </li>
            <li class="d-flex gap-3 align-items-center mb-1 mx-2 pb-1">
              <div class="badge rounded bg-label-info p-1"><i class="ti ti-users ti-sm"></i></div>
              <div>
                <h6 class="mb-0 text-nowrap">Total personas</h6>
                <small class="text-muted">{{ $totalGruposDirectos }}</small>
              </div>
            </li>
          </ul>

          <ul class="list-unstyled mb-0 mt-2">
            <small class="card-text text-uppercase">Mis grupos directos</small>
            @foreach($gruposEncargados as $grupo)
            <li class="mb-1 mt-1 p-2 border rounded">
              <div class="d-flex align-items-start">
                <div class="d-flex align-items-start">
                  <div class="avatar me-2">
                    <i class="ti ti-users-group me-2 fs-1"></i>
                  </div>
                  <div class="me-2 ms-1">
                    <h6 class="mb-0">{{ $grupo->nombre }}</h6>
                    <small class="text-muted"><b>Tipo:</b> {{ $grupo->tipoGrupo->nombre }}</small>
                  </div>
                </div>
                <div class="ms-auto pt-1">
                  <div class="d-flex align-items-center">
                    @if($grupo->latitud)
                    <a href="https://www.google.com/maps/@?api=1&map_action=pano&viewpoint={{$grupo->latitud}}%2C{{$grupo->longitud}}" target="_blank"  class="text-body" data-bs-toggle="tooltip" aria-label="Ver mapa" data-bs-original-title="Ver ubicación en el mapa">
                      <i class="ti ti-map-2 me-2 ti-sm"></i></a>
                    @else
                    <a href="{{ route('grupo.georreferencia',$grupo) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Agregar georeferencia" data-bs-original-title="Este grupo no está ubicado en el mapa, por favor agrega la ubicación aquí">
                      <i class="ti ti-map-pin-plus me-2 ti-sm"></i>
                    </a>
                    @endif
                    <a href="{{ route('grupo.graficoDelMinisterio', ['idNodo' => 'G-'.$grupo->id]  ) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver gráfico ministerial " data-bs-original-title="Ver gráfico ministerial">
                      <i class="ti ti-sitemap me-2 ti-sm"></i>
                    </a>
                  </div>
                </div>
            </li>
            @endforeach
          </ul>
        </div>
      </div>
      @endif
      <!--/ Encargados directos -->

      <!-- Encargados  -->
      @if($encargadosDirectos->count() > 0 || $encargadosAscendentes->count() > 0)
      <div class="card card-action mb-4">
        <div class="card-header align-items-center">
          <p class="card-text text-uppercase fw-bold">Encargados</p>
        </div>
        <div class="card-body pb-3">
          <ul class="list-unstyled mb-0">
            @if($encargadosDirectos->count() > 0)
            <small class="card-text text-uppercase">Directos</small>
            @foreach($encargadosDirectos as $encargado)
            <li class="mb-1 mt-1 p-2 border rounded">
              <div class="d-flex align-items-start">
                <div class="d-flex align-items-start">
                  <div class="avatar me-2">
                    <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$encargado->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$encargado->foto }}" alt="Avatar" class="rounded-circle" />
                  </div>
                  <div class="me-2 ms-1">
                    <h6 class="mb-0">{{ $encargado->nombre }}</h6>
                    <small class="text-muted"><i class="ti {{ $encargado->icono }} text-heading fs-6"></i> {{ $encargado->tipo_usuario}}</small>
                  </div>
                </div>

                <div class="ms-auto pt-1">
                  @if($rolActivo->hasPermissionTo('personas.lista_asistentes_todos'))
                  <a href="{{ route('usuario.perfil', $encargado) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                    <i class="ti ti-user-check me-2 ti-sm"></i></a>
                  </a>
                  @endif
                </div>
              </div>
            </li>
            @endforeach
            @endif
            @if($encargadosAscendentes->count() > 0 )
            <small class="card-text text-uppercase">Ascendentes</small>
            @foreach($encargadosAscendentes as $encargado)
            <li class="mb-1 mt-1 p-2 border rounded">
              <div class="d-flex align-items-start">
                <div class="d-flex align-items-start">
                  <div class="avatar me-2">
                    <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$encargado->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$encargado->foto }}" alt="Avatar" class="rounded-circle" />
                  </div>
                  <div class="me-2 ms-1">
                    <h6 class="mb-0">{{ $encargado->nombre(3) }}</h6>
                    <small class="text-muted"><i class="ti {{ $encargado->tipoUsuario->icono }} text-heading fs-6"></i> {{ $encargado->tipoUsuario->nombre }}</small>
                  </div>
                </div>

                <div class="ms-auto pt-1">
                  @if($rolActivo->hasPermissionTo('personas.lista_asistentes_todos'))
                  <a href="{{ route('usuario.perfil', $encargado) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil">
                    <i class="ti ti-user-check me-2 ti-sm"></i></a>
                  </a>
                  @endif
                </div>

              </div>
            </li>
            @endforeach
            @endif

          </ul>
        </div>
      </div>
      @endif
      <!--/ Encargados  -->

      <!-- Grupos -->
      @if($gruposDondeAsiste->count() > 0 || $gruposAscendentes->count() > 0 || $gruposExcluidos->count() > 0)
      <div class="card card-action mb-4">
        <div class="card-header align-items-center">
          <p class="card-text text-uppercase fw-bold">Grupos</p>
        </div>
        <div class="card-body pb-3">
          <ul class="list-unstyled mb-0">
            @if($gruposDondeAsiste->count() > 0)
            <small class="card-text text-uppercase">Asiste a</small>
            @foreach($gruposDondeAsiste as $grupo)
            <li class="mb-1 mt-1 p-2 border rounded">
              <div class="d-flex align-items-start">
                <div class="d-flex align-items-start">
                  <div class="avatar me-2">
                    <i class="ti ti-users-group me-2 fs-1"></i>
                  </div>
                  <div class="me-2 ms-1">
                    <h6 class="mb-0">{{ $grupo->nombre }}</h6>
                    <small class="text-muted"><b>Tipo:</b> {{ $grupo->tipoGrupo->nombre }}</small>
                  </div>
                </div>

                <div class="ms-auto pt-1">
                  @if($rolActivo->hasPermissionTo('grupos.lista_grupos_todos'))
                  <a href="{{ route('grupo.perfil', $grupo) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil del grupo">
                    <i class="ti ti-id me-2 ti-sm"></i></a>
                  </a>
                  @endif
                </div>
              </div>
            </li>
            @endforeach
            @endif

            @if($gruposAscendentes->count() > 0)
            <small class="card-text text-uppercase">Ascendentes</small>
            @foreach($gruposAscendentes as $grupo)
            <li class="mb-1 mt-1 p-2 border rounded">
              <div class="d-flex align-items-start">
                <div class="d-flex align-items-start">
                  <div class="avatar me-2">
                    <i class="ti ti-users-group me-2 fs-1"></i>
                  </div>
                  <div class="me-2 ms-1">
                    <h6 class="mb-0">{{ $grupo->nombre }}</h6>
                    <small class="text-muted"><b>Tipo:</b> {{$grupo->nombreTipo}}</small>
                  </div>
                </div>
                <div class="ms-auto pt-1">
                  @if($rolActivo->hasPermissionTo('grupos.lista_grupos_todos'))
                  <a href="{{ route('grupo.perfil', $grupo) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil del grupo">
                    <i class="ti ti-id me-2 ti-sm"></i></a>
                  </a>
                  @endif
                </div>
              </div>
            </li>
            @endforeach
            @endif

            @if($gruposExcluidos->count() > 0)
            <small class="card-text text-uppercase">Excluidos</small>
            @foreach($gruposExcluidos as $grupo)
            <li class="mb-3 mt-1">
              <div class="d-flex align-items-start">
                <div class="d-flex align-items-start">
                  <div class="avatar me-2">
                    <i class="ti ti-users-group me-2 fs-1"></i>
                  </div>
                  <div class="me-2 ms-1 p-2 border rounded">
                    <h6 class="mb-0">{{ $grupo->nombre }}</h6>
                    <small class="text-muted"><b>Tipo:</b> {{$grupo->tipoGrupo->nombre}}</small>
                  </div>
                </div>
                <div class="ms-auto pt-1">
                  @if($rolActivo->hasPermissionTo('grupos.lista_grupos_todos'))
                  <a href="{{ route('grupo.perfil', $grupo) }}" target="_blank" class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil" data-bs-original-title="Ver perfil del grupo">
                    <i class="ti ti-id me-2 ti-sm"></i></a>
                  </a>
                  @endif
                </div>
              </div>
            </li>
            @endforeach
            @endif
          </ul>
        </div>
      </div>
      @endif
      <!--/ Grupos -->

      <!-- Peticiones -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <p class="card-text text-uppercase fw-bold"><i class="ti ti-message ms-n1 me-2"></i>Peticiones</p>
        </div>
        <div class="card-body pb-20">
          <ul class="timeline ms-1 mb-0">
            @foreach ($peticiones as $peticion)
            <li class="timeline-item timeline-item-transparent ps-4">
              <span class="timeline-point {{ $peticion->estado== 3 ? 'timeline-point-warning' : ($peticion->estado== 2 ? 'timeline-point-success' : 'timeline-point-primary') }}"></span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <h6 class="mb-0 ml-1 fw-bold">{{ $peticion->tipoPeticion->nombre }}</h6>
                  <span class="badge rounded-pill {{ $peticion->estado== 2 ? 'bg-label-success' : ($peticion->estado== 1 ? 'bg-label-primary' : 'bg-label-warning') }}">
                    {{ $peticion->estado== 3 ? 'Atendida' : ($peticion->estado== 2 ? 'Finalizada' : 'Iniciada') }}
                  </span>
                </div>
                <small class="text-muted"><i class="ti ti-calendar"></i> {{ $peticion->fecha ?  Carbon\Carbon::parse($peticion->fecha)->locale('es')->isoFormat(('DD MMMM Y')) : 'Sin dato' }}</small>

                @if($peticion->estado==1)
                <div class="accordion mt-3" id="peticion{{$peticion->id}}">
                  <div class="card accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                      <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionPeticion{{$peticion->id}}" aria-expanded="true" aria-controls="accordionPeticion{{$peticion->id}}">
                        Petición
                      </button>
                    </h2>

                    <div id="accordionPeticion{{$peticion->id}}" class="accordion-collapse collapse" data-bs-parent="#peticion{{$peticion->id}}">
                      <div class="accordion-body">
                        {{ $peticion->descripcion }}
                      </div>
                    </div>
                  </div>
                </div>
                @endif

                @if($peticion->estado==2)
                <div class="accordion mt-3" id="respuestaPeticion{{$peticion->id}}">
                  <div class="card accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                      <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionPeticionRespuesta{{$peticion->id}}" aria-expanded="true" aria-controls="accordionPeticionRespuesta{{$peticion->id}}">
                        Repuesta
                      </button>
                    </h2>

                    <div id="accordionPeticionRespuesta{{$peticion->id}}" class="accordion-collapse collapse" data-bs-parent="#respuestaPeticion{{$peticion->id}}">
                      <div class="accordion-body">
                        {{ $peticion->respuesta }}
                      </div>
                    </div>
                  </div>
                </div>
                @endif
            </li>
            @endforeach
          </ul>
        </div>
      </div>
      <!--/ Peticiones -->

    </div>

    <div class="col-md-6">

      <!-- Grafico de asistencia a reunión -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title text-uppercase mb-0 fw-bold">Gráfico asistencia a reuniones</h6>
            <small class="text-muted">
              Asistencias de los últimos 12 meses
            </small>
          </div>
        </div>
        <div class="card-body">
          <div id="graficoReportesReunion"></div>
          <center>
            <small class="text-muted">
              Última asistencia a la reunión: <b>{{ $usuario->ultimo_reporte_reunion ? Carbon\Carbon::parse($usuario->ultimo_reporte_reunion)->locale('es')->isoFormat(('DD MMMM Y')) : 'Sin dato' }}</b>
            </small>
          </center>

        </div>
      </div>
      <!-- /Grafico de asistencia a reunión -->

      <!-- Grafico de asistencia al grupo -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h6 class="card-title text-uppercase mb-0 fw-bold">Gráfico asistencia al grupo</h6>
            <small class="text-muted">
              Asistencias de los últimos 12 meses
            </small>
          </div>
        </div>
        <div class="card-body">
          <div id="graficoReportesGrupo"></div>
          <center>
            <small class="text-muted">
              Última asistencia al grupo: <b>{{ $usuario->ultimo_reporte_grupo ? Carbon\Carbon::parse($usuario->ultimo_reporte_grupo)->locale('es')->isoFormat(('DD MMMM Y')) : 'Sin dato' }}</b>
            </small>
          </center>

        </div>
      </div>
      <!-- /Grafico de asistencia a reunión -->

      @if($rolActivo->hasPermissionTo('personas.ver_panel_pasos_crecimiento_perfil'))
      <!-- Procesos de crecimiento -->
      <div class="card">
        <div class="card-header d-flex justify-content-between">
          <p class="card-text text-uppercase fw-bold"><i class="ti ti-list-details ms-n1 me-2"></i>Procesos de crecimiento</p>
        </div>
        <div class="card-body pb-20">
          <ul class="timeline ms-1 mb-0">
            @foreach ($pasosDeCrecimiento as $paso)
            <li class="timeline-item timeline-item-transparent ps-4">
              <span class="timeline-point timeline-point-{{ $paso->clase_color }}"></span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <h6 class="mb-0 ml-1 fw-bold">{{ $paso->nombre }}</h6>
                  <span class="badge rounded-pill bg-label-{{ $paso->clase_color }}">
                    {{ $paso->estado_nombre }}
                  </span>
                </div>
                <small class="text-muted"><i class="ti ti-calendar"></i> {{ $paso->estado_fecha ?  Carbon\Carbon::parse($paso->estado_fecha)->locale('es')->isoFormat(('DD MMMM Y')) : 'Sin dato' }}</small>
                <p class="mb-2 d-none"><b>Detalle: </b>{{ $paso->detalle_paso ? $paso->detalle_paso : 'No detallado' }}</p>

                @if($paso->detalle_paso)
                <div class="accordion mt-3" id="accordion{{$paso->id}}">
                  <div class="card accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                      <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionPaso{{$paso->id}}" aria-expanded="true" aria-controls="#accordionPaso{{$paso->id}}">
                        Detalle
                      </button>
                    </h2>

                    <div id="accordionPaso{{$paso->id}}" class="accordion-collapse collapse" data-bs-parent="accordion{{$paso->id}}">
                      <div class="accordion-body">
                        {{ $paso->detalle_paso }}
                      </div>
                    </div>
                  </div>
                </div>
                @endif
            </li>
            @endforeach
          </ul>
        </div>
      </div>
      <!--/ Procesos de crecimiento -->
      @endif
    </div>

  </div>
  <!--/ Principal-->

  <!-- Modal cambio de contraseña -->
  <div class="modal fade" id="modalCambioContrasena" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
      <form id="formCambioContrasena" class="forms-sample" method="POST" action="">
        @csrf
        <div class="modal-content">
          <div class="modal-header d-flex flex-column">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">

            <div class="text-center mb-4">
              <h3 class="role-title mb-2"><i class="ti ti-password ti-lg"></i> Cambio de contraseña</h3>
              <p class="text-muted">La contraseña debe contener como mínimo 5 caracteres, una letra minúscula y un número.</p>
            </div>

            <div class="row">

              <!-- Nueva Contrasena -->
              <div class="col-12 mb-3">
                <label class="form-label" for="nueva_contrasena">Nueva contraseña</label>
                <input id="nueva_contrasena" name="password" value="" type="password" class="form-control" required pattern="(?=.*\d)(?=.*[A-Za-z]).{5,}" title="La contraseña debe contener como minimo 5 caracteres alfanumericos, es decir, debe contener como minimo letras y numeros.  "/>
              </div>

              <!-- Confirmar Contrasena -->
              <div class="col-12 mb-3">
                <label class="form-label" for="confirmar_contrasena">Confirmar contraseña</label>
                <input id="confirmar_contrasena" name="password_confirmation" value="" type="password" class="form-control" required pattern="(?=.*\d)(?=.*[A-Za-z]).{5,}" title="La contraseña debe contener como minimo 5 caracteres alfanumericos, es decir, debe contener como minimo letras y numeros.  "/>
              </div>

            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn rounded-pill btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn rounded-pill btn-primary"><i class="ti ti-donwload ml-3"></i> Guardar </button>
          </div>
        </div>
      </form>
    </div>
  </div>

@endsection
