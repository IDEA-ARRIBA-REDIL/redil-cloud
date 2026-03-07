@extends('layouts/layoutMaster')
@section('title', 'Gestionar Tipo de Usuario')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')

<h4 class=" mb-1 fw-semibold text-primary">Tipos de usuarios</h4>

<div class="d-flex flex-row-reverse mb-4">
    <a href="{{ route('tipo-usuario.creacion') }}" class="btn btn-primary rounded-pill px-7 py-2">
      <i class="ti ti-plus me-2"></i> Nuevo
    </a>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<form id="formBuscar" class="forms-sample" method="GET" action="{{ route('tipo-usuario.listar') }}">
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
    <span class="text-black">{{ $tipoUsuarios->total() > 1 ? $tipoUsuarios->total().' Tipos de usuarios' : $tipoUsuarios->total().' Tipo de usuario' }}</span>
  </div>
</form>

<div class="row g-6 mt-1 mb-5" id="elementos-container">
  @forelse($tipoUsuarios as $tipoUsuario)
  <div class="col-md-6 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            @if($tipoUsuario->icono)
              <div class="me-2">
                <i class="{{ $tipoUsuario->icono }} ti-md text-black"></i>
              </div>
            @endif
            <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $tipoUsuario->nombre }}</h5>
          </div>
          <div class="ms-auto">
            <div class="dropdown zindex-2 p-1 float-end">
              <button type="button" class="btn dropdown-toggle hide-arrow btn btn-sm waves-effect text-black border p-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="{{ route('tipo-usuario.editar', $tipoUsuario) }}">
                    <i class="ti ti-edit me-2"></i> Editar
                  </a>
                </li>
                <li>
                  <form action="{{ route('tipo-usuario.eliminar', $tipoUsuario->id) }}" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <a href="javascript:void(0);" class="dropdown-item text-danger eliminar-btn">
                      <i class="ti ti-trash me-2"></i> Eliminar
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
            <div class="col-12 mb-2">
               <span class="badge bg-label-primary rounded-pill">Puntaje: {{ $tipoUsuario->puntaje }}</span>
            </div>
            <div class="col-12">
               <small class="text-black">Seguimiento grupo:</small> <span class="text-black fw-semibold">{{ $tipoUsuario->seguimiento_actividad_grupo ? 'Sí' : 'No' }}</span>
            </div>
            <div class="col-12">
               <small class="text-black">Seguimiento reunión:</small> <span class="text-black fw-semibold">{{ $tipoUsuario->seguimiento_actividad_reunion ? 'Sí' : 'No' }}</span>
            </div>
            <div class="col-12">
               <small class="text-black">Consolidación:</small> <span class="text-black fw-semibold">{{ $tipoUsuario->habilitado_para_consolidacion ? 'Sí' : 'No' }}</span>
            </div>
            @if($tipoUsuario->descripcion)
            <div class="col-12 mt-2">
              <p class="mb-0 text-muted small text-truncate-2" title="{{ $tipoUsuario->descripcion }}">
                {{ $tipoUsuario->descripcion }}
              </p>
            </div>
            @endif
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="card border shadow-none">
      <div class="card-body text-center py-5">
        <i class="ti ti-search fs-1 text-muted mb-2"></i>
        <h6>No se encontraron tipos de usuarios{{ $buscar ? ' que coincidan con "' . $buscar . '"' : '.' }}</h6>
      </div>
    </div>
  </div>
  @endforelse
</div>


<div class="row my-3 text-black">
  @if($tipoUsuarios)
  <p> {{$tipoUsuarios->lastItem()}} <b>de</b> {{$tipoUsuarios->total()}} <b>registros - Página</b> {{ $tipoUsuarios->currentPage() }} </p>
  {!! $tipoUsuarios->appends(request()->input())->links() !!}
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

    // Confirmación eliminar con SweetAlert2
    document.querySelectorAll('.eliminar-btn').forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        let form = this.closest('form');

        Swal.fire({
          title: '¿Estás seguro?',
          text: "No podrás revertir esto.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          customClass: {
            confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
            cancelButton: 'btn btn-label-secondary waves-effect waves-light'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  });
</script>
@endsection
