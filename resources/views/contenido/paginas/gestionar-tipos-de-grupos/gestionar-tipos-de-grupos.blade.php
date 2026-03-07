@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Gestionar tipos de grupos')

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

<h4 class=" mb-1 fw-semibold text-primary">Tipos de grupos</h4>

<div class="d-flex flex-row-reverse mb-4">
    {{-- Botón para ir a la página de creación --}}
    <a href="{{ route('gestionar-tipos-de-grupos.nuevo') }}" class="btn btn-primary rounded-pill px-7 py-2">
      <i class="ti ti-plus me-2"></i> Nuevo
    </a>
</div>

@include('layouts.status-msn')

<form id="formBuscar" class="forms-sample" method="GET" action="{{ route('gestionar-tipos-de-grupos.listar') }}">
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
    <span class="text-black">{{ $tiposGrupos->total() > 1 ? $tiposGrupos->total().' Tipos de grupos' : $tiposGrupos->total().' Tipo de grupo' }}</span>
  </div>
</form>

<div class="row g-6 mt-1 mb-5" id="elementos-container">
  @if($tiposGrupos->count() > 0)
    @foreach($tiposGrupos as $tipoGrupo)
    <div class="col-md-6 col-lg-4" id="tipoGrupo-{{$tipoGrupo->id}}">
      <div class="card h-100 {{ !$tipoGrupo->estado ? 'border-danger' : '' }}">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              @if($tipoGrupo->icono)
                <div class="me-2">
                  <i class="{{ $tipoGrupo->icono }} ti-md text-primary"></i>
                </div>
              @endif
              <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $tipoGrupo->nombre }}</h5>
            </div>
            <div class="ms-auto">
              <div class="dropdown zindex-2 p-1 float-end">
                <button type="button" class="btn dropdown-toggle hide-arrow btn btn-sm waves-effect text-black border p-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="{{ route('gestionar-tipos-de-grupos.editarTipoDeGrupo', $tipoGrupo->id) }}">
                      <i class="ti ti-edit me-2"></i> Editar
                    </a>
                  </li>
                  <li>
                    <form id="estado-form-{{ $tipoGrupo->id }}"
                      action="{{ route('gestionar-tipos-de-grupos.cambiarEstadoTipoDeGrupo', $tipoGrupo->id) }}"
                      method="POST" class="m-0">
                      @csrf
                      @method('PATCH')
                      <a href="javascript:void(0);"
                        class="dropdown-item estado-btn {{ $tipoGrupo->estado ? 'text-danger' : 'text-success' }}"
                        data-form-id="estado-form-{{ $tipoGrupo->id }}"
                        data-nombre-grupo="{{ $tipoGrupo->nombre }}"
                        data-accion="{{ $tipoGrupo->estado ? 'desactivar' : 'activar' }}">
                        <i class="ti {{ $tipoGrupo->estado ? 'ti-ban' : 'ti-circle-check' }} me-2"></i>
                        {{ $tipoGrupo->estado ? 'Desactivar' : 'Activar' }}
                      </a>
                    </form>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-2">
            {{-- Estado --}}
            <div class="col-12 mb-2">
              @if(!$tipoGrupo->estado)
                <span class="badge bg-label-danger rounded-pill">Desactivado</span>
              @else
                <span class="badge bg-label-success rounded-pill">Activo</span>
              @endif
            </div>

            {{-- Días inactividad --}}
            <div class="col-12">
              <small class="text-black">Días inactividad:</small> <span class="text-black fw-semibold">{{ $tipoGrupo->tiempo_para_definir_inactivo_grupo ?? 'No definido' }}</span>
            </div>

            {{-- Seguimiento de actividad --}}
            <div class="col-12">
              <small class="text-black">Seguimiento actividad:</small> <span class="text-black fw-semibold">{{ $tipoGrupo->seguimiento_actividad ? 'Sí' : 'No' }}</span>
            </div>

            {{-- Descripción --}}
            @if($tipoGrupo->descripcion)
            <div class="col-12 mt-2">
              <p class="mb-0 text-black small text-truncate-2" title="{{ $tipoGrupo->descripcion }}">
               <b>Descripción:</b> {{ $tipoGrupo->descripcion }}
              </p>
            </div>
            @endif
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
          <h6>No se encontraron tipos de grupos{{ $buscar ? ' que coincidan con "' . $buscar . '"' : '.' }}</h6>
        </div>
      </div>
    </div>
  @endif
</div>


<div class="row my-3 text-black">
  @if($tiposGrupos)
  <p> {{$tiposGrupos->lastItem()}} <b>de</b> {{$tiposGrupos->total()}} <b>registros - Página</b> {{ $tiposGrupos->currentPage() }} </p>
  {!! $tiposGrupos->appends(request()->input())->links() !!}
  @endif
</div>


@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Lógica para búsqueda con debounce
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

    // Modal de confirmación para activar/desactivar
    const estadoButtons = document.querySelectorAll('.estado-btn');
    estadoButtons.forEach(button => {
      button.addEventListener('click', function(event) {
        event.preventDefault();
        const formId = this.getAttribute('data-form-id');
        const nombreGrupo = this.getAttribute('data-nombre-grupo');
        const accion = this.getAttribute('data-accion');

        Swal.fire({
          title: '¿Deseas ' + accion + ' "' + nombreGrupo + '"?',
          text: "Podrás revertir esta acción más tarde.",
          icon: 'warning',
          showCancelButton: true,
          focusConfirm: false,
          confirmButtonText: 'Sí, ' + accion,
          cancelButtonText: 'No',
          customClass: {
            confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
            cancelButton: 'btn btn-label-secondary waves-effect waves-light'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            const form = document.getElementById(formId);
            if (form) {
              form.submit();
            }
          }
        });
      });
    });
  });
</script>
@endsection
