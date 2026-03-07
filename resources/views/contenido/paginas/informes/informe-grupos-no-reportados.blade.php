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
        <div class="card ">
          <div class="card-body">
            <div class="row ">

              @livewire('Grupos.grupos-para-busqueda',[
              'id' => 'grupo',
              'class' => 'col-12 col-md-6 mb-3',
              'label' => 'Selecciona el grupo',
              'conDadosDeBaja' => 'no',
              'grupoSeleccionadoId' => old('grupo') ? old('grupo') : $request->grupo   ,
              'estiloSeleccion' => 'pequeno',
              'obligatorio' => true
              ])

              <!-- semana Inicial -->
              <div id="divSemana" class="mb-2 col-12 col-md-6 mb-3">
                <label for="semana" class="form-label">Semana inicial</label>
                <input id="semana" value="{{ old('grupo') ? old('semana') : $request->semana }}" type="week" name="semana" class="form-control">
                @if($errors->has("semana") ) <div class="text-danger form-label">{{ $errors->first("semana") }}</div> @endif
              </div>

              <!-- Tipo de grupo -->
              <div id="divTiposDeGrupo" class="col-12 col-md-10 mb-3">
                <label for="filtroPorTipoDeGrupo" class="form-label">Selecciona los tipos de grupo </label>
                <select id="filtroPorTipoDeGrupo" name="filtroPorTipoDeGrupo[]" class="selectTiposDeGrupo form-select" multiple>
                  @foreach($tiposDeGrupo as $tipoGrupo)
                  <option value="{{ $tipoGrupo->id }}" {{ in_array($tipoGrupo->id, old('filtroPorTipoDeGrupo') ? old('filtroPorTipoDeGrupo') : $filtroTipoGrupos  ) ? "selected" : "" }}>{{ $tipoGrupo->nombre }}</option>
                  @endforeach
                </select>
                @if($errors->has("filtroPorTipoDeGrupo") ) <div class="text-danger form-label">{{ $errors->first("filtroPorTipoDeGrupo") }}</div> @endif
              </div>
              <!-- Tipo de grupo -->

              <!-- Botón -->
              <div class="col-12 col-md-2 mb-3 d-flex align-items-end">
                <button type="submit" class="btn rounded-pill btn-primary waves-effect waves-light btnOk">Aceptar</button>
              </div>

            </div>
          </div>
        </div>
      </div>
      <!-- Información principal /-->
    </div>
  </form>


  @if($grupos)

  {{-- INICIO: Botón de descarga --}}
   <a href="{{ route('informes.exportarInformeDeGruposNoReportados', ['informe' => $informe->id] + request()->query()) }}" class="btn btn-outline-secondary waves-effect px-2 px-md-3 mb-3 mt-3">
    <span class="d-none d-md-block fw-semibold">Descargar excel</span><i class="ti ti-file-download ms-1"></i>
  </a>
  {{-- FIN: Botón de descarga --}}

  <p class="card-header text-black small mb-3"> <b>No reportados:</b> Son los grupos que no crearon un reporte o que no lo finalizaron - <b>No realizados:</b> Son los grupos que reportaron que no lo realizaron. </p>
 
  <div class="card mb-5">
    <h5 class="card-header text-black">Tabla resumen</h5>
    
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead class="table-light">
          <tr>
            <th>Grupo</th>
            <th class="text-center text-black">No reportados</th>
            <th class="text-center text-black">No realizados</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <tr>
            <td>
              <span class="fw-medium text-black"> {{ $grupoSeleccionado->nombre }} </span>
            </td>
            <td class="text-center text-black">{{ $resumenReporte['No reportado'] }}</td>
            <td class="text-center text-black">{{ $resumenReporte['No realizado'] }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>


  <div class="card">
    <h5 class="card-header text-black">Tabla general</h5>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead class="table-light">
          <tr>
            <th>Grupo</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th>Motivo</th>
            <th>Descripción</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
        @foreach ($grupos as $grupo)
          <tr>
            <td>
              <span class="fw-medium text-black"><small class="text-black"> {{ $grupo->nombre }} </small></span>
              <br>
              <small class="text-black">
                 {{ $datosEncargados->get($grupo->id)?->pluck('nombre_completo')->implode(', ') }}
              </small>
            </td>
            <td>
              <span class="fw-medium text-black"><small> {{ $grupo->nombreTipo }} </small></span>
            </td>
            <td class="text-black"><small>{{ $grupo->estado_reporte }}</small></td>
            <td class="text-black"><small>{{ $grupo->nombre_motivo }}</small></td>
            <td class="text-black"><small>{{ $grupo->descripcion_adicional_motivo }}</small></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>


  <div class="row my-3">
    @if($grupos)
    <p> {{$grupos->lastItem()}} <b>de</b> {{$grupos->total()}} <b>grupos - Página</b> {{ $grupos->currentPage() }} </p>
    {!! $grupos->appends(request()->input())->links() !!}
    @endif
  </div>

  @endif

@endsection
