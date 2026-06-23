@extends('layouts/layoutMaster')
@section('title', 'Lista de tipos de peticiones')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')

<h4 class=" mb-1 fw-semibold text-primary">Tipos de peticiones</h4>

<div class="d-flex flex-row-reverse mb-4">
    {{-- Botón para ir a la página de creación --}}
    <a href="{{ route('tipo-peticiones.nueva') }}" class="btn btn-primary rounded-pill px-7 py-2">
      <i class="ti ti-plus me-2"></i> Nueva
    </a>
</div>

@include('layouts.status-msn')

<form id="formBuscar" class="forms-sample" method="GET" action="{{ route('tipo-peticiones.listar') }}">
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
    <span class="text-black">{{ $tiposPeticiones->total() > 1 ? $tiposPeticiones->total().' Tipos de grupos' : $tiposPeticiones->total().' Tipo de grupo' }}</span>
  </div>
</form>

<div class="row g-6 mt-1 mb-5" id="elementos-container">
  @if($tiposPeticiones->count() > 0)
    @foreach($tiposPeticiones as $tipoPeticion)
    <div class="col-md-6 col-lg-4" id="tipoGrupo-{{$tipoPeticion->id}}">
      <div class="card h-100 {{ !$tipoPeticion->estado ? 'border-danger' : '' }}">
        @if($tipoPeticion->banner_email_url)
          <img src="{{ $tipoPeticion->banner_email_url }}" alt="Banner {{ $tipoPeticion->nombre }}" class="card-img-top" style="height: 120px; object-fit: cover;">
        @else
          <div class="card-img-top bg-label-primary d-flex align-items-center justify-content-center" style="height: 120px;">
            <i class="ti ti-photo ti-xl text-primary opacity-25"></i>
          </div>
        @endif
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              @if($tipoPeticion->icono)
                <div class="me-2">
                  <i class="{{ $tipoPeticion->icono }} ti-md text-primary"></i>
                </div>
              @endif
              <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $tipoPeticion->nombre }}</h5>
            </div>
            <div class="ms-auto">
              <div class="dropdown zindex-2 p-1 float-end">
                <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect"  data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="{{ route('tipo-peticiones.editar', $tipoPeticion->id) }}">
                      <i class="ti ti-edit me-2"></i> Editar
                    </a>
                  </li>                 
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-2">
          
           
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
          <h6>No se encontraron tipos de peticiones{{ $buscar ? ' que coincidan con "' . $buscar . '"' : '.' }}</h6>
        </div>
      </div>
    </div>
  @endif
</div>

<div class="row my-3 text-black">
  @if($tiposPeticiones)
  <p> {{$tiposPeticiones->lastItem()}} <b>de</b> {{$tiposPeticiones->total()}} <b>registros - Página</b> {{ $tiposPeticiones->currentPage() }} </p>
  {!! $tiposPeticiones->appends(request()->input())->links() !!}
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
