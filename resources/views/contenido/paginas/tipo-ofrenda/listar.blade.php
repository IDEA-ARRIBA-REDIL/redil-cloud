@extends('layouts/layoutMaster')

@section('title', 'Tipos de ofrenda')

{{-- Estilos de SweetAlert2 y Select2 --}}
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('content')

<div class="container mt-4">
  <!-- Header de Navegación y Acciones -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Tipos de ofrenda</h4>
      </div>
      <p class="text-black mb-0 small">
        Aquí podrás gestionar los tipos de ofrenda.
      </p>
    </div>

    <div>
      <!-- Botón Crear -->
      <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#crearModal">
        <i class="ti ti-plus me-1"></i> Nuevo tipo de ofrenda
      </button>
    </div>
  </div>

  <div class="row g-4 mb-4">
    @forelse ($tiposOfrendas as $tipo)
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <h5 class="card-title text-black mb-1 fw-bold">{{ $tipo->nombre }}</h5>
              <small class="text-muted">ID: {{ $tipo->id }}</small>
            </div>

            <div class="d-flex gap-2">
              <!-- Botón Editar -->
              <button class="btn btn-icon btn-text-secondary p-0 rounded-circle bg-transparent border-0 shadow-none btn-edit"
                data-id="{{ $tipo->id }}"
                data-nombre="{{ $tipo->nombre }}"
                data-descripcion="{{ $tipo->descripcion }}"
                data-codigo_sap="{{ $tipo->codigo_sap }}"
                data-generica="{{ $tipo->generica }}"
                data-formulario_donaciones="{{ $tipo->formulario_donaciones }}"
                data-tipo_reunion="{{ $tipo->tipo_reunion }}"
                data-ofrenda_obligatoria="{{ $tipo->ofrenda_obligatoria }}"
                data-destinatarios="{{ $tipo->destinatarios->pluck('id')->toJson() }}"
                data-bs-toggle="tooltip"
                title="Editar">
                <i class="ti ti-edit ti-md text-black"></i>
              </button>

              <!-- Botón Eliminar -->
              <form action="{{ route('tipo-ofrenda.eliminar', $tipo) }}" method="POST" class="delete-form m-0">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-icon btn-text-secondary p-0 rounded-circle bg-transparent border-0 shadow-none btn-delete" data-bs-toggle="tooltip" title="Eliminar">
                  <i class="ti ti-trash ti-md text-black"></i>
                </button>
              </form>
            </div>
          </div>

          <div class="mb-3">
             <p class="mb-0   fw-semibold text-dark small"><b>Descripción:</b> {{ Str::limit($tipo->descripcion, 100) }}</p>
          </div>

          <div class="row g-2">
            <div class="col-12">
               <span class="fw-semibold small text-black">Código SAP:</span> {{ $tipo->codigo_sap ?? 'N/A' }}
            </div>

            <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                @if($tipo->generica)
                  <span class="badge bg-label-info">Genérica</span>
                @endif
                @if($tipo->ofrenda_obligatoria)
                  <span class="badge bg-label-warning">Obligatoria</span>
                @endif
                @if($tipo->formulario_donaciones)
                  <span class="badge bg-label-success">Donaciones</span>
                @endif
                @if($tipo->tipo_reunion)
                  <span class="badge bg-label-primary">Reunión</span>
                @endif
                @if(!$tipo->generica && !$tipo->ofrenda_obligatoria && !$tipo->formulario_donaciones && !$tipo->tipo_reunion)
                  <span class="badge bg-label-secondary">Sin atributos</span>
                @endif
            </div>

            @if($tipo->formulario_donaciones)
            <div class="col-12 mt-2">
              <span class="fw-semibold small text-black d-block mb-1">Destinatarios donaciones:</span>
              <div class="d-flex flex-wrap gap-1">
                @forelse($tipo->destinatarios as $dest)
                  <span class="badge bg-label-dark">{{ $dest->nombre }}</span>
                @empty
                  <span class="text-muted small fst-italic">Ninguno asignado</span>
                @endforelse
              </div>
            </div>
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
            <i class="ti ti-carousel-vertical fs-1 pb-1"></i>
            <h6 class="text-center">No hay tipos de ofrenda registrados.</h6>
          </center>
        </div>
      </div>
    </div>
    @endforelse
  </div>
</div>

<!-- Modal Editar (único) -->
<div class="modal fade" id="editarModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content" id="formEditar">
      @csrf
      @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">Editar Tipo de Ofrenda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" class="form-control" id="editNombre" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" id="editDescripcion" required></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Código SAP</label>
          <input type="text" name="codigo_sap" class="form-control" id="editCodigoSap">
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="generica" id="editGenerica" value="1">
          <label class="form-check-label" for="editGenerica">¿Es genérica?</label>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="formulario_donaciones" id="editFormularioDonaciones" value="1">
          <label class="form-check-label" for="editFormularioDonaciones">¿Se usa en formulario de donaciones?</label>
        </div>

        <div class="mb-3 ps-4 d-none" id="contenedorDestinatariosEditar">
          <label class="form-label">Destinatarios habilitados para donación</label>
          <select name="destinatarios[]" id="editDestinatarios" class="form-select select2-destinatarios-editar" multiple="multiple" data-placeholder="Selecciona uno o más destinatarios">
            @foreach($destinatarios ?? [] as $destinatario)
              <option value="{{ $destinatario->id }}">{{ $destinatario->nombre }}</option>
            @endforeach
          </select>
          <small class="text-muted">El donante podrá elegir entre estos destinatarios al seleccionar este tipo de ofrenda.</small>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="tipo_reunion" id="editTipoReunion" value="1">
          <label class="form-check-label" for="editTipoReunion">¿Está asociada a un tipo de reunión?</label>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="ofrenda_obligatoria" id="editOfrendaObligatoria" value="1">
          <label class="form-check-label" for="editOfrendaObligatoria">¿Es una ofrenda obligatoria?</label>
        </div>
      </div>

      <div class="modal-footer">
         <button class="btn btn-primary rounded-pill waves-effect waves-light">Actualizar</button>
        <button type="btn" type="button" class="btn btn-outline-secondary rounded-pill waves-effect waves-light" data-bs-dismiss="modal">Cancelar</button>

      </div>
    </form>
  </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="crearModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="{{ route('tipo-ofrenda.crear') }}" method="POST" class="modal-content">
      @csrf
      <div class="modal-header text-white">
        <h5 class="modal-title">Nuevo Tipo de Ofrenda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Código SAP</label>
          <input type="text" name="codigo_sap" class="form-control">
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="generica" id="crearGenerica" value="1">
          <label class="form-check-label" for="crearGenerica">¿Es genérica?</label>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="formulario_donaciones" id="crearFormularioDonaciones" value="1">
          <label class="form-check-label" for="crearFormularioDonaciones">¿Se usa en formulario de donaciones?</label>
        </div>

        <div class="mb-3 ps-4 d-none" id="contenedorDestinatariosCrear">
          <label class="form-label">Destinatarios habilitados para donación</label>
          <select name="destinatarios[]" id="crearDestinatarios" class="form-select select2-destinatarios-crear" multiple="multiple" data-placeholder="Selecciona uno o más destinatarios">
            @foreach($destinatarios ?? [] as $destinatario)
              <option value="{{ $destinatario->id }}">{{ $destinatario->nombre }}</option>
            @endforeach
          </select>
          <small class="text-muted">El donante podrá elegir entre estos destinatarios al seleccionar este tipo de ofrenda.</small>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="tipo_reunion" id="crearTipoReunion" value="1">
          <label class="form-check-label" for="crearTipoReunion">¿Está asociada a un tipo de reunión?</label>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="ofrenda_obligatoria" id="crearOfrendaObligatoria" value="1">
          <label class="form-check-label" for="crearOfrendaObligatoria">¿Es una ofrenda obligatoria?</label>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary rounded-pill waves-effect waves-light">Guardar</button>
        <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect waves-light" data-bs-dismiss="modal">Cancelar</button>

      </div>
    </form>
  </div>
</div>
@endsection

{{-- Scripts de SweetAlert2 y Select2 --}}
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const crearCheckDonaciones = document.getElementById("crearFormularioDonaciones");
    const contenedorCrear = document.getElementById("contenedorDestinatariosCrear");
    const editCheckDonaciones = document.getElementById("editFormularioDonaciones");
    const contenedorEditar = document.getElementById("contenedorDestinatariosEditar");

    if (window.jQuery && $.fn.select2) {
      $('#crearDestinatarios').select2({
        dropdownParent: $('#crearModal'),
        width: '100%'
      });
      $('#editDestinatarios').select2({
        dropdownParent: $('#editarModal'),
        width: '100%'
      });
    }

    if (crearCheckDonaciones) {
      crearCheckDonaciones.addEventListener("change", function() {
        contenedorCrear.classList.toggle("d-none", !this.checked);
      });
    }

    if (editCheckDonaciones) {
      editCheckDonaciones.addEventListener("change", function() {
        contenedorEditar.classList.toggle("d-none", !this.checked);
      });
    }

    document.querySelectorAll(".btn-edit").forEach(button => {
      button.addEventListener("click", function() {
        // Llenar el formulario
        const form = document.getElementById("formEditar");
        const id = this.dataset.id;
        form.action = `/tipo-ofrenda/${id}`;

        document.getElementById("editNombre").value = this.dataset.nombre;
        document.getElementById("editDescripcion").value = this.dataset.descripcion;
        document.getElementById("editCodigoSap").value = this.dataset.codigo_sap;

        // Checkbox
        document.getElementById("editGenerica").checked = this.dataset.generica == 1;
        const usaDonaciones = this.dataset.formulario_donaciones == 1;
        document.getElementById("editFormularioDonaciones").checked = usaDonaciones;
        document.getElementById("editTipoReunion").checked = this.dataset.tipo_reunion == 1;
        document.getElementById("editOfrendaObligatoria").checked = this.dataset.ofrenda_obligatoria == 1;

        contenedorEditar.classList.toggle("d-none", !usaDonaciones);

        let destinatariosIds = [];
        try {
          destinatariosIds = JSON.parse(this.dataset.destinatarios || "[]").map(String);
        } catch (e) {
          destinatariosIds = [];
        }

        if (window.jQuery && $.fn.select2) {
          $('#editDestinatarios').val(destinatariosIds).trigger('change');
        } else {
          const selectEl = document.getElementById("editDestinatarios");
          Array.from(selectEl.options).forEach(opt => {
            opt.selected = destinatariosIds.includes(String(opt.value));
          });
        }

        // Mostrar el modal manualmente
        const modal = new bootstrap.Modal(document.getElementById("editarModal"));
        modal.show();
      });
    });


    // Mensaje de éxito después de guardar/actualizar/eliminar
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
