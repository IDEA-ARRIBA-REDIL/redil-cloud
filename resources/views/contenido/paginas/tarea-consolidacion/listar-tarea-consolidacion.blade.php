@extends('layouts/layoutMaster')

@section('title', 'Gestión de Tareas de Consolidación')

{{-- Estilos de SweetAlert2 --}}
@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')
<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Tareas de Consolidación</h3>
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#crearModal">
        <i class="ti ti-plus me-1"></i> Nueva Tarea
      </button>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        {{-- Quitamos 'table-striped' para un fondo blanco uniforme --}}
        <table class="table table-hover">
          <thead>
            <tr class="text-center align-middle">
              <th>ID</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Orden</th>
              <th>Default</th> {{-- Nueva Columna --}}
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($tareas as $tarea)
            <tr class="text-center align-middle">
              <td>{{ $tarea->id }}</td>
              <td>{{ $tarea->nombre }}</td>
              <td>{{ $tarea->descripcion ?? 'N/A' }}</td>
              <td>{{ $tarea->orden }}</td>
              {{-- Mostramos 'Sí' o 'No' basado en el booleano --}}
              <td>
                @if($tarea->default)
                <span class="badge bg-label-success">Sí</span>
                @else
                <span class="badge bg-label-secondary">No</span>
                @endif
              </td>
              <td class="d-flex gap-2 justify-content-center">
                <button class="btn btn-info btn-sm btn-edit"
                  data-id="{{ $tarea->id }}"
                  data-nombre="{{ $tarea->nombre }}"
                  data-descripcion="{{ $tarea->descripcion }}"
                  data-orden="{{ $tarea->orden }}"
                  data-default="{{ $tarea->default ? '1' : '0' }}"> {{-- Pasamos el dato default --}}
                  <i class="ti ti-edit"></i>
                </button>

                {{-- Añadimos 'd-inline' al form para alineación correcta --}}
                <form action="{{ route('tareas-consolidacion.eliminarTareaConsolidacion', $tarea) }}" method="POST" class="delete-form d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="btn btn-danger btn-sm btn-delete">
                    <i class="ti ti-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center">No hay tareas de consolidación registradas.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content" id="formEditar">
      @csrf
      @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title" id="editarModalLabel">Editar Tarea de Consolidación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="editNombre" class="form-label">Nombre</label>
          <input type="text" name="nombre" class="form-control" id="editNombre" required>
        </div>
        <div class="mb-3">
          <label for="editDescripcion" class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" id="editDescripcion"></textarea>
        </div>
        <div class="mb-3">
          <label for="editOrden" class="form-label">Orden</label>
          <input type="number" name="orden" class="form-control" id="editOrden">
        </div>
        {{-- Checkbox para el campo 'default' --}}
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="default" id="editDefault" value="1">
          <label class="form-check-label" for="editDefault">¿Es una tarea por defecto?</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="crearModal" tabindex="-1" aria-labelledby="crearModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('tareas-consolidacion.crearTareaConsolidacion') }}" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title" id="crearModalLabel">Nueva Tarea de Consolidación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="createNombre" class="form-label">Nombre</label>
          <input type="text" name="nombre" id="createNombre" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="createDescripcion" class="form-label">Descripción</label>
          <textarea name="descripcion" id="createDescripcion" class="form-control"></textarea>
        </div>
        <div class="mb-3">
          <label for="createOrden" class="form-label">Orden</label>
          <input type="number" name="orden" id="createOrden" class="form-control" value="0">
        </div>
        {{-- Checkbox para el campo 'default' --}}
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="default" id="createDefault" value="1">
          <label class="form-check-label" for="createDefault">¿Es una tarea por defecto?</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar Tarea</button>
      </div>
    </form>
  </div>
</div>
@endsection

{{-- Scripts --}}
@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const editarModal = new bootstrap.Modal(document.getElementById("editarModal"));
    const formEditar = document.getElementById("formEditar");

    // Lógica para el modal de EDICIÓN
    document.querySelectorAll(".btn-edit").forEach(button => {
      button.addEventListener("click", function() {
        const id = this.dataset.id;

        // Construimos la URL para la acción del formulario
        formEditar.action = `/tareas-consolidacion/actualizar-tarea-consolidacion/${id}`;

        // Llenamos los campos del modal
        document.getElementById("editNombre").value = this.dataset.nombre;
        document.getElementById("editDescripcion").value = this.dataset.descripcion;
        document.getElementById("editOrden").value = this.dataset.orden;

        // Marcamos el checkbox si 'data-default' es '1'
        document.getElementById("editDefault").checked = (this.dataset.default == '1');

        editarModal.show();
      });
    });

    // Lógica para el botón de ELIMINAR
    document.querySelectorAll(".btn-delete").forEach(button => {
      button.addEventListener("click", function(event) {
        event.preventDefault();
        const form = this.closest('.delete-form');

        Swal.fire({
          title: '¿Estás seguro?',
          text: "¡No podrás revertir esta acción!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, ¡eliminar!',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });

    // Notificación de éxito
    @if(session('success'))
    Swal.fire({
      title: "¡Éxito!",
      text: "{{ session('success') }}",
      icon: "success",
      confirmButtonText: "Aceptar"
    });
    @endif
  });
</script>
@endpush
