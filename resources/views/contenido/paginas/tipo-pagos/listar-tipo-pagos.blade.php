@extends('layouts.layoutMaster')

@section('title', 'Listado de Tipos de Pago')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')

<div class="container-fluid mt-4">
  <div class="row g-4 mb-4">
    <div class="col-12 text-end">
        <h4 class="float-start mb-1 fw-semibold text-primary">Tipos de pago</h4>
        <a href="{{ route('tipo-pagos.creacionTipoPagos') }}" class="btn btn-primary rounded-pill px-4">
            <i class="ti ti-plus me-1"></i> Crear nuevo
        </a>
    </div>
  </div>

  <div class="row g-4 mb-4">
      @forelse ($tipoPagos as $pago)
      <div class="col-md-6 col-lg-4">
          <div class="card h-100">
              <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                      <div class="d-flex align-items-center gap-3">

                        <div>
                            <h5 class="card-title text-black mb-0 fw-bold">{{ $pago->nombre }}</h5>
                            <small class="text-muted">ID: {{ $pago->id }}</small>
                        </div>
                      </div>

                      <div class="d-flex gap-2">
                           <a href="{{ route('tipo-pagos.actualizacionTipoPagos', $pago->id) }}"
                             class="btn btn-icon btn-text-secondary p-0 rounded-circle bg-transparent border-0 shadow-none"
                             data-bs-toggle="tooltip"
                             title="Editar">
                              <i class="ti ti-edit ti-md text-black"></i>
                           </a>

                           <form action="{{ route('tipo-pagos.eliminarTipoPagos', $pago->id) }}" method="POST" class="delete-form m-0" onsubmit="return confirmDelete(event, this)">
                               @csrf
                               @method('DELETE')
                               <button type="submit" class="btn btn-icon btn-text-secondary p-0 rounded-circle bg-transparent border-0 shadow-none" data-bs-toggle="tooltip" title="Eliminar">
                                   <i class="ti ti-trash ti-md text-black"></i>
                               </button>
                           </form>
                      </div>
                  </div>

                  <div class="mb-3">
                       <p class="mb-0 fw-semibold text-dark small"><b>Cuenta SAP:</b> {{ $pago->cuenta_sap }}</p>
                       <p class="mb-0 text-muted small mt-1">{{ Str::limit($pago->observaciones, 80) }}</p>
                  </div>

                  <div class="d-flex flex-wrap gap-2 mt-3">
                       {{-- Botón de estado (AJAX) --}}
                       <button type="button"
                          id="btn-estado-{{ $pago->id }}"
                          onclick="confirmarCambioEstado({{ $pago->id }}, '{{ $pago->nombre }}')"
                          class="btn btn-sm badge {{ $pago->activo ? 'bg-label-success' : 'bg-label-danger' }} border-0"
                          style="cursor: pointer;">
                          {{ $pago->activo ? 'Activo' : 'Inactivo' }}
                       </button>

                       @if($pago->habilitado_punto_pago)
                           <span class="badge bg-label-info">Punto de Pago</span>
                       @endif
                       @if($pago->habilitado_donacion)
                           <span class="badge bg-label-success">Donaciones</span>
                       @endif
                       @if($pago->permite_personas_externas)
                           <span class="badge bg-label-warning">Externos</span>
                       @endif
                  </div>
              </div>
          </div>
      </div>
      @empty
      <div class="col-12">
        <div class="card border">
          <div class="card-body">
            <center>
              <i class="ti ti-wallet fs-1 pb-1"></i>
              <h6 class="text-center">No hay tipos de pago registrados.</h6>
            </center>
          </div>
        </div>
      </div>
      @endforelse
  </div>

  <div class="row">
      <div class="col-12 d-flex justify-content-end">
           {{ $tipoPagos->links('pagination::bootstrap-5') }}
      </div>
  </div>
</div>

@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@push('scripts')
<script>
  // 1. Funciones para cambiar estado (AJAX)
  function confirmarCambioEstado(id, nombre) {
    Swal.fire({
      title: '¿Cambiar estado?',
      text: `Vas a cambiar el estado de "${nombre}"`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, cambiar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        ejecutarCambioEstado(id);
      }
    })
  }

  function ejecutarCambioEstado(id) {
    const url = `/tipo_pagos/cambiar-estado/${id}`;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const boton = document.getElementById(`btn-estado-${id}`);
          if (data.nuevo_estado) {
            boton.classList.remove('bg-label-danger');
            boton.classList.add('bg-label-success');
            boton.innerText = 'Activo';
          } else {
            boton.classList.remove('bg-label-success');
            boton.classList.add('bg-label-danger');
            boton.innerText = 'Inactivo';
          }
          Swal.fire('¡Actualizado!', data.mensaje, 'success');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
      });
  }

  function confirmDelete(event, form) {
      event.preventDefault();
      Swal.fire({
          title: '¿Estás seguro?',
          text: "¡No podrás revertir esto!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Sí, eliminarlo',
          cancelButtonText: 'Cancelar'
      }).then((result) => {
          if (result.isConfirmed) {
              form.submit();
          }
      });
      return false;
  }

  // 2. Lógica para mostrar la alerta
  document.addEventListener('DOMContentLoaded', function() {
    let isBack = false;
    const entries = performance.getEntriesByType("navigation");
    if (entries.length > 0 && entries[0].type === 'back_forward') {
      isBack = true;
    }
    if (window.performance && window.performance.navigation.type === 2) {
      isBack = true;
    }

    if (!isBack) {
      @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: "{{ session('success') }}",
        timer: 3000,
        showConfirmButton: false
      });
      @endif
    }
  });

  window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
      Swal.close();
    }
  });
</script>
@endpush
