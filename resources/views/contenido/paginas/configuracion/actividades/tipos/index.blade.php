@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Gestionar tipos de actividad')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('content')

<h4 class=" mb-1 fw-semibold text-primary">Tipos de actividad</h4>

<div class="d-flex flex-row-reverse mb-4">
    <a href="{{ route('gestionar-tipos-de-actividad.nuevo') }}" class="btn btn-primary rounded-pill px-7 py-2">
      <i class="ti ti-plus me-2"></i> Nuevo
    </a>
</div>

@include('layouts.status-msn')

<form id="formBuscar" class="forms-sample" method="GET" action="{{ route('gestionar-tipos-de-actividad.index') }}">
  <div class="row mt-3">
    <div class="col-12 col-md-4">
      <div class="input-group input-group-merge bg-white">
        <input id="buscar" name="buscar" type="text" value="{{ $buscar }}" class="form-control" placeholder="Busqueda por nombre..." aria-describedby="btnBusqueda">
        @if($buscar)
        <span id="borrarBusquedaPorPalabra" class="input-group-text cursor-pointer"><i class="ti ti-x"></i></span>
        @else
        <span class="input-group-text"><i class="ti ti-search"></i></span>
        @endif
      </div>
    </div>
  </div>
  <div class="row mt-3">
    <span class="text-black">{{ $tiposActividad->total() > 1 ? $tiposActividad->total().' Tipos de actividad' : $tiposActividad->total().' Tipo de actividad' }}</span>
  </div>
</form>

<div class="row g-6 mt-1 mb-5" id="elementos-container">
  @if($tiposActividad->count() > 0)
    @foreach($tiposActividad as $tipo)
    <div class="col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header pb-2">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              
              <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $tipo->nombre }}</h5>
            </div>
            <div class="ms-auto">
              <div class="dropdown zindex-2 p-1 float-end">
                <button type="button" class="btn dropdown-toggle hide-arrow btn btn-sm waves-effect text-black border p-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="{{ route('gestionar-tipos-de-actividad.editar', $tipo->id) }}">
                      <i class="ti ti-edit me-2"></i> Editar
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body pt-0">
          <p class="text-muted small mb-3 text-truncate-2" title="{{ $tipo->descripcion }}">
            {{ $tipo->descripcion }}
          </p>
          
          <div class="row g-1 mt-2">
            <div class="col-12 p-0">
              <p class="mb-0 small text-black">Inscripción: <span class="fw-bold">{{ $tipo->requiere_inscripcion ? 'Sí' : 'No' }}</span></p>
            </div>
            <div class="col-12 p-0">
              <p class="mb-0 small text-black">Tipo escuelas: <span class="fw-bold">{{ $tipo->tipo_escuelas ? 'Sí' : 'No' }}</span></p>
            </div>
            <div class="col-12 p-0">
              <p class="mb-0 small text-black">Requiere inicio sesión: <span class="fw-bold">{{ $tipo->requiere_inicio_sesion ? 'Sí' : 'No' }}</span></p>
            </div>
            <div class="col-12 p-0">
              <p class="mb-0 small text-black">Gratuita: <span class="fw-bold">{{ $tipo->es_gratuita ? 'Sí' : 'No' }}</span></p>
            </div>
          </div>
        </div>       
      </div>
    </div>
    @endforeach
  @else
    <div class="col-12">
      <div class="card border shadow-none">
        <div class="card-body text-center py-5">
          <i class="ti ti-search fs-1 text-muted mb-2"></i>
          <h6>No se encontraron tipos de actividad{{ $buscar ? ' que coincidan con "' . $buscar . '"' : '.' }}</h6>
        </div>
      </div>
    </div>
  @endif
</div>

<div class="row my-3 text-black">
  @if($tiposActividad)
  <p> {{$tiposActividad->lastItem()}} <b>de</b> {{$tiposActividad->total()}} <b>registros - Página</b> {{ $tiposActividad->currentPage() }} </p>
  {!! $tiposActividad->appends(request()->input())->links() !!}
  @endif
</div>

@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const buscarInput = document.getElementById('buscar');
    const btnBorrarBusquedaPorPalabra = document.getElementById('borrarBusquedaPorPalabra');
    const formularioBuscar = document.getElementById('formBuscar');
    let timeoutId;
    const delay = 1000;

    if (buscarInput) {
      buscarInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        if (this.value.length >= 3) {
          timeoutId = setTimeout(() => {
            formularioBuscar.submit();
          }, delay);
        } else if (this.value.length == 0) {
          formularioBuscar.submit();
        }
      });
    }

    if (btnBorrarBusquedaPorPalabra) {
      btnBorrarBusquedaPorPalabra.addEventListener('click', function() {
        buscarInput.value = "";
        formularioBuscar.submit();
      });
    }
  });
</script>
@endsection
