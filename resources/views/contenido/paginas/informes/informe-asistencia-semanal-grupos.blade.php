@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Informes')

<!-- Page -->
@section('page-style')
@vite([

'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
])
@endsection


@section('page-script')
<script>
  $(document).ready(function() {
    $('.selectTiposDeGrupo').select2({
      placeholder: "Selecciona los tipos de grupo",
      allowClear: true,
      closeOnSelect: false
    });

    $('.selectClasificacionesAsistentes').select2({
      placeholder: "Selecciona los tipos de grupo",
      allowClear: true,
      closeOnSelect: false
    });
  });
</script>

<script>
    // Se ejecuta cuando todo el contenido de la página se ha cargado
    document.addEventListener('DOMContentLoaded', function () {

        // 1. Obtenemos los elementos con los que vamos a trabajar
        const selectorPeriodo = document.getElementById('periodo');
        const divAnio = document.getElementById('div-anio');
        const divSemana = document.getElementById('div-semana');

        // 2. Creamos una función para manejar la lógica de mostrar/ocultar
        function actualizarVisibilidad() {
            // Obtenemos el valor seleccionado actualmente
            const rangoSeleccionado = selectorPeriodo.value;

            if (rangoSeleccionado === 'semana') {
                // Si se selecciona 'Por semana', mostramos el input de semana y ocultamos el de año
                divSemana.style.display = 'block';
                divAnio.style.display = 'none';
            } else {
                // Para cualquier otra opción, mostramos el input de año y ocultamos el de semana
                divSemana.style.display = 'none';
                divAnio.style.display = 'block';
            }
        }

        // 3. Le decimos al selector que ejecute nuestra función CADA VEZ que cambie su valor
        selectorPeriodo.addEventListener('change', actualizarVisibilidad);

        // Opcional pero recomendado: Ejecuta la función una vez al cargar la página
        // para asegurar que el estado inicial es el correcto, especialmente si usas Select2.
        actualizarVisibilidad();
    });
</script>
@endsection

@section('content')
<h4 class="mb-1 fw-semibold text-primary">{{ $informe->nombre }}</h4>
<p class="mb-4 text-black">{{ $informe->descripcion }}</p>


  @include('layouts.status-msn')

  <form id="formulario" class="forms-sample" method="GET" action="{{ route($informe->link, $informe->id) }}">
    <div id="div-filtros" class="row">
      <!-- Información principal -->

      <div class="col-12">
        <div class="card mb-4">
          <div class="card-body">
            <div class="row mb-4">

              @livewire('Grupos.grupos-para-busqueda',[
              'id' => 'grupo',
              'class' => 'col-12 col-md-4 mb-3',
              'label' => 'Selecciona el grupo',
              'conDadosDeBaja' => 'no',
              'grupoSeleccionadoId' => old('grupo', $request->grupo)   ,
              'estiloSeleccion' => 'pequeno',
              'obligatorio' => true
              ])

              <div class="mb-3 col-12 col-md-3">
                <label class="form-label" for="periodo">Periodo</label>
                <select id="periodo" name="periodo" class="select2 form-select" data-allow-clear="true">
                  <option value="">Seleccione un periodo</option>
                  @foreach ($periodos as $grupo)
                    <optgroup label="{{ $grupo['label'] }}">
                      @foreach ($grupo['options'] as $value => $label)
                        <option value="{{ $value }}" {{ old('periodo', $request->periodo) == $value ? 'selected' : '' }}> {{ $label }}</option>
                      @endforeach
                    </optgroup>
                  @endforeach
                </select>

                @if($errors->has('periodo'))
                    <div class="text-danger form-label">{{ $errors->first('periodo') }}</div>
                @endif
              </div>


              <div id="div-anio" class="form-group mb-3 col-12 col-md-3" style="{{ old('rango', $rango ?? '') === 'semana' ? 'display: none;' : '' }}">
                  <label class="form-label">Año</label>
                  <input id="año" name="año" type="number" class="number form-control" value="{{old('año', date('Y')) }}">
                  @if($errors->has('año')) <div class="text-danger form-label">{{ $errors->first('año') }}</div> @endif
              </div>

              <div id="div-semana" class="form-group mb-3 col-12 col-md-3" style="{{ old('rango', $rango ?? '') === 'semana' ? '' : 'display: none;' }}">
                  <label class="form-label">Seleccione semana</label>
                  <input id="semana" name="semana" type="week" value="{{ old('semana', $request->semana) }}" class="form-control">
                  @if($errors->has('semana')) <div class="text-danger form-label">{{ $errors->first('semana') }}</div> @endif
              </div>

              <div class="form-group mb-3 col-12 col-md-2">
                <label class="form-label" for="dia_reunion">
                  Día de corte
                </label>
                <select id="día_de_corte" name="día_de_corte" class="select2 form-select" data-allow-clear="true">
                  <option value="" selected>Ninguno</option>
                  @foreach (Helper::diasDeLaSemana() as $dia)
                  <option value="{{$dia->id}}" {{ old('día_de_corte', $request->día_de_corte)==$dia->id ? 'selected' : '' }}>{{$dia->nombre}}</option>
                  @endforeach
                </select>
                @if($errors->has('día_de_corte')) <div class="text-danger form-label">{{ $errors->first('día_de_corte') }}</div> @endif
              </div>



              <!-- Por tipo de grupo -->
              <div id="divTiposDeGrupo" class="col-12 col-md-12 mb-3">
                <label for="filtroPorTipoDeGrupo" class="form-label">Selecciona los tipos de grupo </label>
                <select id="filtroPorTipoDeGrupo" name="filtroPorTipoDeGrupo[]" class="selectTiposDeGrupo form-select" multiple>
                  @foreach($tiposDeGrupo as $tipoGrupo)
                  <option value="{{ $tipoGrupo->id }}" {{ in_array($tipoGrupo->id, old('filtroPorTipoDeGrupo') ? old('filtroPorTipoDeGrupo') : $filtroTipoGrupos  ) ? "selected" : "" }}>{{ $tipoGrupo->nombre }}</option>
                  @endforeach
                </select>
                @if($errors->has("filtroPorTipoDeGrupo") ) <div class="text-danger form-label">{{ $errors->first("filtroPorTipoDeGrupo") }}</div> @endif
              </div>
              <!-- Por tipo de grupo -->

              <!-- Clasificacion asistentes -->
              <div id="divClasificacionAsistentes" class="col-12 col-md-12 mb-3">
                <label for="filtroPorClasificacionAsistentes" class="form-label">Añadir clasificaciones </label>
                <select id="filtroPorClasificacionAsistentes" name="filtroPorClasificacionAsistentes[]" class="selectClasificacionesAsistentes form-select" multiple>
                  @foreach($clasificacionAsistentes as $clasificacion)
                  <option value="{{ $clasificacion->id }}" {{ in_array($clasificacion->id, old('filtroPorClasificacionAsistentes') ? old('filtroPorClasificacionAsistentes') : $filtroClasificacionAsistentes  ) ? "selected" : "" }}>{{ $clasificacion->nombre }}</option>
                  @endforeach
                </select>
                @if($errors->has("filtroPorClasificacionAsistentes") ) <div class="text-danger form-label">{{ $errors->first("filtroPorClasificacionAsistentes") }}</div> @endif
              </div>
              <!-- Clasificacion asistentes -->

              <div class="form-group mb-3 col-6 col-md-3">
                  <div class="form-label">¿Incluir grupos nuevos?</div>
                  <label class="switch switch-lg my-auto">
                      <input id="incluirGruposNuevos" name="incluirGruposNuevos" type="checkbox" @checked(old('incluirGruposNuevos', $request->incluirGruposNuevos == true)) class="switch-input" />
                      <span class="switch-toggle-slider">
                          <span class="switch-on">SI</span>
                          <span class="switch-off">NO</span>
                      </span>
                      <span class="switch-label"></span>
                  </label>
              </div>

              <div class="form-group mb-3 col-6 col-md-3">
                  <div class="form-label">¿Incluir grupos dados de baja?</div>
                  <label class="switch switch-lg my-auto">
                      <input id="incluirGruposDadosDeBaja" name="incluirGruposDadosDeBaja" type="checkbox" @checked(old('incluirGruposDadosDeBaja', $request->incluirGruposDadosDeBaja == true)) class="switch-input" />
                      <span class="switch-toggle-slider">
                          <span class="switch-on">SI</span>
                          <span class="switch-off">NO</span>
                      </span>
                      <span class="switch-label"></span>
                  </label>
              </div>


              <!-- Botón -->
              <div class="col-12 col-md-6 mb-3 d-flex align-items-end justify-content-center justify-content-md-end">
                <button type="submit" class="btn rounded-pill btn-primary waves-effect waves-light btnOk">Aceptar</button>
              </div>

            </div>
          </div>
        </div>
      </div>
      <!-- Información principal /-->
    </div>
  </form>


  @if($request->grupo)

  {{-- INICIO: Botón de descarga --}}
  <a href="{{ route('informes.exportarInformeAsistenciaSemanalGrupos', ['informe' => $informe->id] + request()->query()) }}" class="btn btn-outline-secondary waves-effect px-2 px-md-3 mb-3">
    <span class="d-none d-md-block fw-semibold">Descargar excel</span><i class="ti ti-file-download ms-1"></i>
  </a>

  {{-- FIN: Botón de descarga --}}

  <div class="card mb-5">
    <h5 class="card-header">Tabla resumen</h5>
    <div class="table-responsive text-nowrap">
      <table class="table table-bordered table-striped text-center">
        <thead>
          <tr class="text-center ">
              <th class="fw-bold">MES</th>
              @foreach ($encabezadosAgrupados as $data)
                  <th colspan="{{ $data['colspan'] }}" class="fw-bold">{{ $data['mes'] }}</th>
              @endforeach
              <th rowspan="2" class="align-middle fw-bold">PROMEDIOS</th>
          </tr>
          {{-- Segunda fila del encabezado: Fechas --}}
          <tr class="text-center">
              <th class="fw-bold">FECHAS</th>
              @foreach ($encabezadosAgrupados as $data)
                  @foreach ($data['semanas'] as $semana)
                      <th class="fw-bold">{{ $semana['dias'] }}</th>
                  @endforeach
              @endforeach
          </tr>
        </thead>
        <tbody>

            {{-- Filas dinámicas de Clasificaciones --}}
            @foreach ($clasificacionesSeleccionadas as $clasificacion)
                <tr>
                    <td class="text-left fw-semibold text-black">Total {{ $clasificacion->nombre }}</td>
                    @foreach ($encabezadosAgrupados as $grupoEncabezado)
                        @foreach ($grupoEncabezado['semanas'] as $semana)
                            <td class="fw-semibold text-black">{{ $resumen['clasificaciones'][$clasificacion->id][$semana['semana']] ?? 0 }}</td>
                        @endforeach
                    @endforeach
                    <td class="fw-semibold text-black">{{ number_format($resumen['promedios']['clasificaciones'][$clasificacion->id] ?? 0) }}</td>
                </tr>
            @endforeach

            {{-- Fila de Asistencias --}}
            <tr class="table-light">
                <td class="text-left fw-semibold">Total Asistencias</td>
                @foreach ($encabezadosAgrupados as $grupoEncabezado)
                    @foreach ($grupoEncabezado['semanas'] as $semana)
                        <td class="text-left fw-semibold">{{ $resumen['asistencias'][$semana['semana']] ?? 0 }}</td>
                    @endforeach
                @endforeach
                <td class="text-left fw-semibold">{{ number_format($resumen['promedios']['asistencias'] ?? 0) }}</td>
            </tr>
            {{-- Fila de Inasistencias --}}
            <tr class="table-light">
                <td class="text-left fw-semibold">Total Inasistencias</td>
                @foreach ($encabezadosAgrupados as $grupoEncabezado)
                    @foreach ($grupoEncabezado['semanas'] as $semana)
                        <td class="text-left fw-semibold">{{ $resumen['inasistencias'][$semana['semana']] ?? 0 }}</td>
                    @endforeach
                @endforeach
                <td class="text-left fw-semibold">{{ number_format($resumen['promedios']['inasistencias'] ?? 0) }}</td>
            </tr>
            {{-- Fila de Nro. Reportes --}}
            <tr class="table-light">
                <td class="text-left fw-semibold">Total Nro. Reportes</td>
                @foreach ($encabezadosAgrupados as $grupoEncabezado)
                    @foreach ($grupoEncabezado['semanas'] as $semana)
                        <td class="text-left fw-semibold">{{ $resumen['reportes'][$semana['semana']] ?? 0 }}</td>
                    @endforeach
                @endforeach
                <td class="text-left fw-semibold"> {{ $resumen['promedios']['reportes']['real'] ?? 0 }} / {{ $resumen['promedios']['reportes']['esperado'] ?? 0 }}</td>
            </tr>

        </tbody>
      </table>
    </div>
  </div>


  <div class="card">
    <h5 class="card-header">Tabla general</h5>
    <div class="table-responsive text-nowrap">
      <table class="table table-bordered">
        <thead>
          <tr class="text-center ">
              <th class="fw-bold">MES</th>
              @foreach ($encabezadosAgrupados as $data)
                  <th colspan="{{ $data['colspan'] }}" class="fw-bold">{{ $data['mes'] }}</th>
              @endforeach
              <th rowspan="2" class="align-middle fw-bold">PROMEDIOS</th>
          </tr>
          {{-- Segunda fila del encabezado: Fechas --}}
          <tr class="text-center">
              <th class="fw-bold">FECHAS</th>
              @foreach ($encabezadosAgrupados as $data)
                  @foreach ($data['semanas'] as $semana)
                      <th class="fw-bold">{{ $semana['dias'] }}</th>
                  @endforeach
              @endforeach
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">

          @forelse ($dataPivoteada as $nombreGrupo => $datosGrupo)

              {{-- Info del grupo --}}
              <tr class="table-success">
                  <th colspan="{{ count($encabezados) + 2 }}" class="text-left">
                    <p class="fs-5 fw-semibold mb-0 text-black">{{ $nombreGrupo }}</p>
                    <small>{{ $datosGrupo['tipo_grupo'] }}</small>
                    @if (!empty($datosGrupo['encargados']))
                    <div class="d-flex flex-column mt-1">
                      @foreach ($datosGrupo['encargados'] as $encargado)
                      <div class="d-flex flex-row">
                        <i class="{{ $encargado->tipo_usuario_icono }}" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="{{ $encargado->tipo_usuario_nombre }}" data-bs-original-title="{{ $encargado->tipo_usuario_nombre }}"></i>
                        <div class="d-flex flex-column">
                          <small class="fw-semibold ms-1 text-black">{{ $encargado->encargado_nombre }}</small>
                        </div>
                      </div>
                      @endforeach
                    </div>
                    @endif
                  </th>
              </tr>

              {{-- FILAS DINÁMICAS PARA LAS CLASIFICACIONES --}}
              @foreach ($clasificacionesSeleccionadas as $clasificacion)
              <tr>
                  <td class="text-left text-black">{{ $clasificacion->nombre }}</td>
                  @foreach ($encabezadosAgrupados as $grupo)
                      @foreach ($grupo['semanas'] as $semana)
                          <td class="text-center">
                            @if ($semana['semana'] < $datosGrupo['semana_apertura'])
                              -
                            @else
                              {{-- Buscamos el valor para esta clasificación y esta semana --}}
                              {{ $datosGrupo['datos_semanales'][$semana['semana']]['clasificaciones'][$clasificacion->id] ?? 0 }}
                            @endif
                          </td>
                      @endforeach
                  @endforeach

                  {{-- PROMEDIO --}}
                  <td class="fw-bold text-center">{{ number_format($datosGrupo['promedios']['clasificaciones'][$clasificacion->id] ?? 0, 0) }}</td>
              </tr>
              @endforeach

              {{-- Asistencias --}}
              <tr class="table-light">
                  <td class="text-left fw-semibold">Asistencias</td>
                  @foreach ($encabezadosAgrupados as $grupo)
                      @foreach ($grupo['semanas'] as $semana)
                          <td class="text-center">
                              @if ($semana['semana'] < $datosGrupo['semana_apertura'])
                              -
                              @else
                              {{-- Buscamos el valor de asistencias para esa semana --}}
                              {{ $datosGrupo['datos_semanales'][$semana['semana']]['asistencias'] ?? 0 }}
                              @endif
                          </td>
                      @endforeach
                  @endforeach

                  {{-- PROMEDIO --}}
                  <td class="fw-bold text-center">{{ number_format($datosGrupo['promedios']['asistencias'] ?? 0, 0) }}</td>
              </tr>

              {{-- Inasistencias --}}
              <tr class="table-light">
                  <td class="text-left fw-semibold">Inasistencias</td>
                  @foreach ($encabezadosAgrupados as $grupo)
                      @foreach ($grupo['semanas'] as $semana)
                          <td class="text-center">
                            @if ($semana['semana'] < $datosGrupo['semana_apertura'])
                            -
                            @else
                            {{ $datosGrupo['datos_semanales'][$semana['semana']]['inasistencias'] ?? 0 }}
                            @endif
                          </td>
                      @endforeach
                  @endforeach

                  {{-- PROMEDIO --}}
                  <td class="fw-bold text-center">{{ number_format($datosGrupo['promedios']['inasistencias'] ?? 0, 0) }}</td>
              </tr>

              {{-- CANTIDAD DE REPORTES --}}
              <tr class="table-light">
                  <td class="text-left fw-semibold">Nro. Reportes</td>
                  @foreach ($encabezadosAgrupados as $grupo)
                      @foreach ($grupo['semanas'] as $semana)
                          <td class="text-center">
                            @if ($semana['semana'] < $datosGrupo['semana_apertura'])
                            -
                            @else
                              {{ $datosGrupo['datos_semanales'][$semana['semana']]['reportes'] ?? 0 }}
                            @endif
                          </td>
                      @endforeach
                  @endforeach
                  {{-- PROMEDIO --}}
                  <td class="fw-bold text-center">{{ $datosGrupo['promedios']['reportes']['real'] ?? 0 }} / {{ $datosGrupo['promedios']['reportes']['esperado'] ?? 0 }}</td>
              </tr>

          @empty
              <tr>
                  <td colspan="{{ count($encabezados) + 2 }}" class="text-center">
                      No se encontraron datos para el periodo seleccionado.
                  </td>
              </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @endif



@endsection
