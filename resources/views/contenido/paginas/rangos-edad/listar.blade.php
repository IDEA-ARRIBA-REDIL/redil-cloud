@extends('layouts/layoutMaster')
@section('title', 'Gestionar Rangos de Edad')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-12 text-end">
      <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalCrear">
        <i class="ti ti-plus me-1"></i> Nuevo Rango de Edad
      </button>
    </div>
  </div>

  <div class="row g-4 mb-4">
    @forelse($rangos as $rango)
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <h5 class="card-title text-black mb-1 fw-bold">{{ $rango->nombre }}</h5>
              <small class="text-muted">ID: {{ $rango->id }}</small>
            </div>

            <div class="d-flex gap-2">
              <!-- Botón Editar -->
              <button class="btn btn-icon btn-text-secondary p-0 rounded-circle bg-transparent border-0 shadow-none btn-editar"
                data-id="{{ $rango->id }}"
                data-nombre="{{ $rango->nombre }}"
                data-configuracion="{{ $rango->configuracion_id }}"
                data-descripcion="{{ $rango->descripcion }}"
                data-minima="{{ $rango->edad_minima }}"
                data-maxima="{{ $rango->edad_maxima }}"
                data-bs-toggle="modal"
                data-bs-target="#modalEditar"
                title="Editar">
                <i class="ti ti-edit ti-md text-black"></i>
              </button>

              <!-- Botón Eliminar -->
              <form action="{{ route('rangos-edad.eliminarRangoDeEdad', $rango->id) }}" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-icon btn-text-secondary p-0 rounded-circle bg-transparent border-0 shadow-none eliminar-btn" data-bs-toggle="tooltip" title="Eliminar">
                  <i class="ti ti-trash ti-md text-black"></i>
                </button>
              </form>
            </div>
          </div>

          <div class="mb-3">
             <p class="mb-0 fw-semibold text-dark small"><b>Descripción:</b> {{ Str::limit($rango->descripcion, 100) }}</p>
          </div>

          <div class="row g-2">
            <div class="col-12">
               <span class="fw-semibold text-black">Rango de Edad:</span>
               <span class="badge bg-label-primary">{{ $rango->edad_minima }} - {{ $rango->edad_maxima }} años</span>
            </div>
          </div>

        </div>
      </div>
    </div>
    @empty
    <div class="col-12">
      <div class="card border">
        <div class="card-body">
          <center>
            <i class="ti ti-carousel-vertical fs-1 pb-1"></i>
            <h6 class="text-center">No hay rangos de edad registrados.</h6>
          </center>
        </div>
      </div>
    </div>
    @endforelse
  </div>

{{-- Modal Único Editar --}}
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEditar" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-header">
          <h5 class="modal-title">Editar Rango de Edad</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" id="editNombre" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Configuración</label>
            <input type="number" name="configuracion_id" id="editConfiguracion" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion" id="editDescripcion" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Edad Mínima</label>
            <input type="number" name="edad_minima" id="editMinima" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Edad Máxima</label>
            <input type="number" name="edad_maxima" id="editMaxima" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary rounded-pill waves-effect waves-light">Guardar cambios</button>
          <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect waves-light" data-bs-dismiss="modal">Cancelar</button>

        </div>
      </form>
    </div>
  </div>
</div>



{{-- Modal Crear --}}
<div class="modal fade" id="modalCrear" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('rangos-edad.crearRangoDeEdad') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Nuevo Rango de Edad</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Configuración</label>
            <input type="number" name="configuracion_id" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Edad Mínima</label>
            <input type="number" name="edad_minima" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Edad Máxima</label>
            <input type="number" name="edad_maxima" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary rounded-pill waves-effect waves-light">Guardar</button>
          <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect waves-light" data-bs-dismiss="modal">Cancelar</button>

        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Confirmación eliminar
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
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-secondary'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });

    // Editar dinámico
    document.querySelectorAll('.btn-editar').forEach(button => {
      button.addEventListener('click', function() {
        const id = this.dataset.id;
        document.getElementById('editNombre').value = this.dataset.nombre;
        document.getElementById('editConfiguracion').value = this.dataset.configuracion;
        document.getElementById('editDescripcion').value = this.dataset.descripcion;
        document.getElementById('editMinima').value = this.dataset.minima;
        document.getElementById('editMaxima').value = this.dataset.maxima;

        // Corrige la acción del formulario
        const form = document.getElementById('formEditar');
        form.action = "{{ route('rangos-edad.actualizarRangoDeEdad', ':id') }}".replace(':id', id);
      });
    });
  });
</script>
@endsection
