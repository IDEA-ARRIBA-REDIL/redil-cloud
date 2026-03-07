@extends('layouts/layoutMaster')

@section('title', 'Gestión de Filtros y Tareas de Consolidación')


@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')

<script>
  $(document).ready(function() {
    $('#offcanvasCreateEstadosCiviles').select2({
      dropdownParent: $('#offcanvasCreateFiltro')
    });

    $('#offcanvasEditEstadosCiviles').select2({
      dropdownParent: $('#offcanvasEditFiltro')
    });
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", () => {

    const editOffcanvasElement = document.getElementById("offcanvasEditFiltro");
    const editOffcanvas = editOffcanvasElement ? new bootstrap.Offcanvas(editOffcanvasElement) : null;
    const formEditFiltro = document.getElementById("formEditFiltro");

    const assignTaskOffcanvasElement = document.getElementById("offcanvasAssignTask");
    const assignTaskOffcanvas = assignTaskOffcanvasElement ? new bootstrap.Offcanvas(assignTaskOffcanvasElement) : null;
    const formAssignTask = document.getElementById("formAssignTask");
    const assignTaskFiltroIdInput = document.getElementById("assignTaskFiltroId");

    document.querySelectorAll(".btn-edit-filtro").forEach(button => {
      button.addEventListener("click", function() {
        if (!formEditFiltro || !editOffcanvas) {
          console.error("Error: Offcanvas/Formulario de Edición no encontrado.");
          return;
        }
        const id = this.dataset.id;
        formEditFiltro.action = `/filtros-consolidacion/actualizar-filtro-consolidacion/${id}`;
        const nombreInput = formEditFiltro.querySelector("#offcanvasEditNombre");
        const descInput = formEditFiltro.querySelector("#offcanvasEditDescripcion");
        const ordenInput = formEditFiltro.querySelector("#offcanvasEditOrden");
        if (nombreInput) nombreInput.value = this.dataset.nombre;
        if (descInput) descInput.value = this.dataset.descripcion || '';
        if (ordenInput) ordenInput.value = this.dataset.orden;

        // Agregamos al JSON
        const estadosCivilesJson = this.dataset.estadosCiviles || '[]';
        let selectedIds = JSON.parse(estadosCivilesJson);
        $('#offcanvasEditEstadosCiviles').val(selectedIds).trigger('change');


        editOffcanvas.show();
      });
    });

    document.querySelectorAll(".btn-delete").forEach(button => {
      button.addEventListener("click", function(event) {
        event.preventDefault();
        const form = this.closest('.delete-form');
        if (!form) {
          console.error("Error: Formulario '.delete-form' no hallado.");
          return;
        }
        Swal.fire({
          title: '¿Confirmar Eliminación?',
          text: "El filtro se eliminará permanentemente.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'No, cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });

    document.addEventListener('DOMContentLoaded', function() {
      // Seleccionamos todos los elementos que se pueden colapsar
      const collapseElements = document.querySelectorAll('.collapse');

      collapseElements.forEach(function(collapseEl) {
        // Escuchamos el evento que Bootstrap dispara ANTES de empezar a MOSTRAR el contenido
        collapseEl.addEventListener('show.bs.collapse', function() {
          // Buscamos el botón que controla este div en específico
          const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
          if (triggerButton) {
            const icon = triggerButton.querySelector('span.ti');
            // Cambiamos el ícono a 'menos'
            icon.classList.remove('ti-plus');
            icon.classList.add('ti-minus');
          }
        });

        // Escuchamos el evento que Bootstrap dispara ANTES de empezar a OCULTAR el contenido
        collapseEl.addEventListener('hide.bs.collapse', function() {
          const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
          if (triggerButton) {
            const icon = triggerButton.querySelector('span.ti');
            // Cambiamos el ícono a 'más'
            icon.classList.remove('ti-minus');
            icon.classList.add('ti-plus');
          }
        });
      });
    });

    // *** CORRECCIÓN AQUÍ ***
    // Este listener intercepta el envío del formulario de asignación de tareas
    if (formAssignTask) {
      formAssignTask.addEventListener('submit', async function(event) {
        event.preventDefault(); // Detiene el envío normal (que daría 404)

        const formData = new FormData(formAssignTask);

        // Extrae el filtroId de la URL 'action' que PUSIMOS en openAssignTaskOffcanvas
        // La URL es como: ".../filtros-consolidacion/5/asignar-tarea/dummy"
        // Necesitamos la parte [4] (contando desde 0)
        const actionUrlParts = formAssignTask.action.split('/');
        const filtroId = actionUrlParts[4]; // Obtiene el ID del filtro

        const tareaId = formData.get('tarea_id');
        const estadoId = formData.get('estado_tarea_consolidacion_id');
        const incluir = formData.has('incluir'); // true si está marcado

        if (!filtroId || !tareaId || !estadoId) {
          Swal.fire('Faltan Datos', 'Asegúrese de seleccionar tarea y estado.', 'warning');
          return;
        }

        // Construye la URL correcta que COINCIDE con tu web.php
        const url = `/filtros-consolidacion/${filtroId}/asignar-tarea/${tareaId}`; // Ruta POST existente
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        try {
          Swal.fire({
            title: 'Asignando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
          });

          const response = await fetch(url, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
              'Content-Type': 'application/json' // Importante para enviar JSON
            },
            // Envía los datos extra (estadoId, incluir) en el CUERPO
            body: JSON.stringify({
              estado_tarea_consolidacion_id: estadoId,
              incluir: incluir
            })
          });

          const data = await response.json();
          Swal.close();

          if (response.ok && data.success) {
            sessionStorage.setItem('expandCollapseFiltroId', filtroId);
            Swal.fire({
                title: '¡Asignada!',
                icon: 'success',
                timer: 1000,
                showConfirmButton: false
              })
              .then(() => {
                if (assignTaskOffcanvas) assignTaskOffcanvas.hide();
                location.reload();
              });
          } else {
            Swal.fire('Error', data.message || 'No se pudo asignar. Verifique si la combinación ya existe.', 'error');
          }
        } catch (error) {
          Swal.close();
          console.error('Error AJAX [submit asignarTarea]:', error);
          Swal.fire('Error de Red', 'Error de conexión al asignar tarea.', 'error');
        }
      });
    }

    // --- FIN DE LA CORRECCIÓN ---

    @if(session('success'))
    Swal.fire({
      title: "Éxito",
      text: "{{ session('success') }}",
      icon: "success",
      timer: 2000,
      showConfirmButton: false
    });
    @endif
    @if(session('error'))
    Swal.fire({
      title: "Error",
      text: "{{ session('error') }}",
      icon: "error",
      confirmButtonText: "Entendido"
    });
    @endif


    const filtroIdToExpand = "{{ session('expandCollapseFiltroId') }}"; // Usa flash session
    if (filtroIdToExpand) {
      const collapseElementId = `cardBodyFiltro${filtroIdToExpand}`;
      const collapseElement = document.getElementById(collapseElementId);
      if (collapseElement) {
        const bsCollapseInstance = bootstrap.Collapse.getOrCreateInstance(collapseElement);
        setTimeout(() => {
          bsCollapseInstance.show();
          const cardElement = document.getElementById(`filtro-card-${filtroIdToExpand}`);
          if (cardElement) {
            cardElement.scrollIntoView({
              behavior: 'smooth',
              block: 'nearest'
            });
          }
        }, 100);
      } else {
        console.warn(`Collapse #${collapseElementId} no encontrado para auto-expandir.`);
      }
      // No se necesita removeItem con flash session
    }

  }); // --- FIN DOMContentLoaded ---

  function toggleCollapse(filtroId) {
    const collapseElement = document.getElementById(`cardBodyFiltro${filtroId}`);
    if (collapseElement) {
      const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseElement);
      bsCollapse.toggle();
    } else {
      console.error(`Collapse #cardBodyFiltro${filtroId} no encontrado.`);
    }
  }

  function openAssignTaskOffcanvas(filtroId) {
    const assignTaskOffcanvasElement = document.getElementById("offcanvasAssignTask");
    const assignTaskOffcanvas = assignTaskOffcanvasElement ? bootstrap.Offcanvas.getOrCreateInstance(assignTaskOffcanvasElement) : null;
    const formAssignTask = document.getElementById("formAssignTask");

    if (assignTaskOffcanvas && formAssignTask) {
      // Establece la action para que el listener 'submit' pueda extraer el filtroId
      // La parte '/dummy' es solo para rellenar, ya que el JS construye la URL final
      formAssignTask.action = `/filtros-consolidacion/${filtroId}/asignar-tarea/dummy`;

      const tareaSelect = formAssignTask.querySelector('#assignTaskTareaId');
      const estadoSelect = formAssignTask.querySelector('#assignTaskEstadoId');
      const incluirCheckbox = formAssignTask.querySelector('#assignTaskIncluir');

      if (tareaSelect) tareaSelect.value = '';
      if (estadoSelect) estadoSelect.value = '';
      if (incluirCheckbox) incluirCheckbox.checked = true;

      assignTaskOffcanvas.show();
    } else {
      console.error("Error: Elementos del Offcanvas de Asignar Tarea no encontrados.");
      Swal.fire('Error', 'No se pudo abrir el formulario para asignar tareas.', 'error');
    }
  }

  // La función 'asignarTarea' (la AJAX antigua) ya no es necesaria aquí,
  // porque el listener 'submit' del formulario '#formAssignTask' ha tomado su lugar.

  async function desasignarTarea(filtroId, tareaId) {
    const result = await Swal.fire({
      title: '¿Quitar Tarea?',
      text: "Se desasignará la tarea.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, quitar',
      cancelButtonText: 'Cancelar'
    });
    if (result.isConfirmed) {
      const url = `/filtros-consolidacion/${filtroId}/desasignar-tarea/${tareaId}`;
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
      try {
        Swal.fire({
          title: 'Quitando...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });
        const response = await fetch(url, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        });
        const data = await response.json();
        Swal.close();
        if (response.ok && data.success) {
          sessionStorage.setItem('expandCollapseFiltroId', filtroId);
          Swal.fire({
            title: '¡Quitada!',
            icon: 'success',
            timer: 1000,
            showConfirmButton: false
          }).then(() => location.reload());
        } else {
          Swal.fire('Error', data.message || 'No se pudo quitar.', 'error');
        }
      } catch (error) {
        Swal.close();
        console.error('Error AJAX [desasignarTarea]:', error);
        Swal.fire('Error de Red', 'Error de conexión.', 'error');
      }
    }
  }
</script>


@endsection

@section('content')
<div class="container mt-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-1 fw-semibold text-primary">Filtros de consolidación</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCreateFiltro" aria-controls="offcanvasCreateFiltro">
      <i class="ti ti-plus me-1"></i> Nuevo filtro
    </button>
  </div>

  <div class="row equal-height-row g-4 mt-1">
    @forelse ($filtros as $filtro)
    <div class="col equal-height-col col-12 col-md-6" id="filtro-card-{{ $filtro->id }}">
      <div class="card rounded-3 shadow">

        {{-- Card Header - Estilo 'Persona' --}}
        <div class="card-header border-bottom d-flex px-4 pt-3 pb-1" style="background-color:#F9F9F9!important">
          <div class="flex-fill row">
            <div class=" d-flex justify-content-between align-items-center">
              <h5 class="fw-semibold ms-1 text-black m-0">
                {{ $filtro->nombre }}
              </h5>
              {{-- <span class="badge...">Si tuvieras un badge, iría aquí</span> --}}
            </div>
          </div>
          <div class="">
            <div class="ms-auto">
              <div class="dropdown zindex-2 p-1 float-end">
                <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="javascript:void(0);" onclick="openAssignTaskOffcanvas('{{ $filtro->id }}')">
                      <i class="ti ti-playlist-add me-2"></i>Asignar tarea
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item btn-edit-filtro" href="javascript:void(0);"
                      data-id="{{ $filtro->id }}"
                      data-nombre="{{ $filtro->nombre }}"
                      data-descripcion="{{ $filtro->descripcion }}"
                      data-orden="{{ $filtro->orden }}"
                      data-estados-civiles="{{ $filtro->estadosCiviles->pluck('id')->toJson() }}"
                      >
                      <i class="ti ti-pencil me-2"></i>Editar filtro
                    </a>
                  </li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li>
                    <form action="{{ route('filtros-consolidacion.eliminarFiltroConsolidacion', $filtro) }}" method="POST" class="delete-form d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="dropdown-item text-danger btn-delete">
                        <i class="ti ti-trash me-2"></i>Eliminar filtro
                      </button>
                    </form>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row mt-4">

              <div class="col-12 mt-1">
                <small class="text-black">Orden:</small>
                <small class="text-black fw-semibold">{{ $filtro->orden }}</small>
              </div>

              <div class="col-12">
                <hr class="my-3 border-1">
              </div>

              <div class="col-12 mt-1">
                <small class="text-black">Estados civiles:</small>
                <div>
                    @forelse ($filtro->estadosCiviles as $estadoCivil)
                    <span type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1">
                      {{ $estadoCivil->nombre }}
                    </span>
                    @empty
                    <small class="fw-semibold text-black text-wrap">No indicados</small>
                    @endforelse
                </div>
              </div>

              <div class="col-12">
                <hr class="my-3 border-1">
              </div>

              <div class="col-12 d-flex flex-column">
                <small class="text-black">Descripción:</small>
                <small class="fw-semibold text-black text-wrap">{{ $filtro->descripcion ?? 'N/A' }}</small>
              </div>

          </div>

          <div class="collapse" id="cardBodyFiltro{{ $filtro->id }}">
            <div class="col-12">
              <hr class="my-3 border-1">
            </div>
            <h6 class="fw-bold text-black">Tareas asociadas</h6>

            @php
            $tareasAsignadas = $filtro->condiciones->sortBy('orden');
            @endphp

            @forelse ($tareasAsignadas as $tarea)
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="d-flex flex-column">

                @php
                $estadoModel = isset($estados) && $estados instanceof \Illuminate\Support\Collection
                ? $estados->firstWhere('id', $tarea->pivot->estado_tarea_consolidacion_id) : null;
                @endphp

                <small class="text-black mb-2">{{ $tarea->nombre }}</small>

                <div class="btn-group">
                  <small class="text-black me-2 fw-semibold">Estado: </small>
                  <button type="button me-2"
                    class="btn btn-{{ $estadoModel->color ?? 'secondary' }} rounded-pill btn-xs waves-effect waves-light me-2 me-3"
                    style="font-size: 0.75rem;">
                    {{ $estadoModel->nombre ?? 'N/D' }}
                  </button>
                  <small class="text-black me-2 ms-4 fw-semibold">¿Excluido?: </small>
                  <small>{{ $tarea->pivot->incluir ? 'No' : 'Sí' }}</small>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-star">
                <a href="javascript:void(0);"
                  class="btn rounded-pill btn-icon btn-outline-danger waves-effect border-0"
                  onclick="desasignarTarea({{ $filtro->id }}, {{ $tarea->id }})"
                  title="Quitar Tarea">
                  <i class="ti ti-trash text-danger"></i>
                </a>
              </div>
            </div>
            @if (!$loop->last)
            <hr class="my-2">
            @endif
            @empty
            <p class="text-black">No hay tareas asociadas.</p>
            @endforelse

          </div>
        </div>


        {{-- Card Footer (Botón +/-) - Estilo 'Persona' --}}
        <div class="card-footer border-top p-1 mt-auto">
          <div class="d-flex justify-content-center">
            <button type="button"
              class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2"
              data-bs-toggle="collapse"
              data-bs-target="#cardBodyFiltro{{ $filtro->id }}"
              aria-expanded="false"
              aria-controls="cardBodyFiltro{{ $filtro->id }}">
              <span class="ti ti-plus"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
    @empty
    <div class="col-12">
      <div class="alert alert-warning text-center" role="alert">
        <i class="ti ti-info-circle ti-md me-2"></i> No hay filtros de consolidación registrados.
      </div>
    </div>
    @endforelse
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAssignTask" aria-labelledby="offcanvasAssignLabel">
    <div class="offcanvas-header">
      <h4 id="offcanvasAssignLabel" class="offcanvas-title fw-bold text-primary">Asignar tarea a filtro</h4>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
      <form method="POST" class="d-flex flex-column justify-content-between h-100" id="formAssignTask">
        @csrf
        <input type="hidden" name="filtro_id_hidden_for_js_only" id="assignTaskFiltroId" value=""> {{-- Name no es necesario si se pone en action --}}

        <div>
          <div class="mb-3">
            <label for="assignTaskTareaId" class="form-label">Tarea a asignar</label>
            <select name="tarea_id" id="assignTaskTareaId" class="form-select" required>
              <option value="" disabled selected>Seleccione una tarea...</option>
              @foreach($tareasDisponibles ?? [] as $tarea)
              <option value="{{ $tarea->id }}">{{ $tarea->nombre }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="assignTaskEstadoId" class="form-label">Estado Inicial</label>
            <select name="estado_tarea_consolidacion_id" id="assignTaskEstadoId" class="form-select" required>
              <option value="" disabled selected>Seleccione un estado...</option>
              @foreach($estados ?? [] as $estado)
              <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" name="incluir" id="assignTaskIncluir" value="1" checked>
            <label class="form-check-label" for="assignTaskIncluir">Incluir en filtro</label>
            <small class="d-block text-muted">Desmarque para excluir registros con esta tarea/estado.</small>
          </div>
        </div>

        {{-- Botones de Acción --}}
        <div class="mb-3 pt-3 border-top">
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Asignar tarea</button>
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCreateFiltro" aria-labelledby="offcanvasCreateLabel">
    <div class="offcanvas-header">
      <h4 id="offcanvasCreateLabel" class="offcanvas-title fw-bold text-primary">Nuevo filtro</h4>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
      <form action="{{ route('filtros-consolidacion.crearFiltroConsolidacion') }}" method="POST" class="d-flex flex-column justify-content-between h-100">
        @csrf
        <div>
          <div class="mb-3">
            <label for="offcanvasCreateNombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="offcanvasCreateNombre" class="form-control" required placeholder="Nombre descriptivo">
          </div>
          <div class="mb-3">
            <label for="offcanvasCreateDescripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" id="offcanvasCreateDescripcion" class="form-control" rows="4" placeholder="Opcional..."></textarea>
          </div>
          <div class="mb-3">
            <label for="offcanvasCreateOrden" class="form-label">Orden</label>
            <input type="number" name="orden" id="offcanvasCreateOrden" class="form-control" value="0" required>
          </div>

          <div class="mb-3">
            <label for="offcanvasCreateEstadosCiviles" class="form-label">¿Estados civiles? </label>
            <select name="estados_civiles[]" id="offcanvasCreateEstadosCiviles" class="form-control select2-multiple" multiple="multiple" style="width: 100%;">
                @foreach($estadosCiviles as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
          </div>
        </div>
        <div class="mb-3 pt-3 border-top">
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditFiltro" aria-labelledby="offcanvasEditLabel">
    <div class="offcanvas-header">
      <h4 id="offcanvasEditLabel" class="offcanvas-title fw-bold text-primary">Editar filtro</h4>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
      <form method="POST" class="d-flex flex-column justify-content-between h-100" id="formEditFiltro">
        @csrf
        @method('PUT')
        <div>
          <div class="mb-3">
            <label for="offcanvasEditNombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" id="offcanvasEditNombre" required>
          </div>
          <div class="mb-3">
            <label for="offcanvasEditDescripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="4" id="offcanvasEditDescripcion"></textarea>
          </div>
          <div class="mb-3">
            <label for="offcanvasEditOrden" class="form-label">Orden</label>
            <input type="number" name="orden" class="form-control" id="offcanvasEditOrden" required>
          </div>

          <div class="mb-3">
            <label for="offcanvasEditEstadosCiviles" class="form-label">Estados Civiles</label>
            <select name="estados_civiles[]" id="offcanvasEditEstadosCiviles" class="form-control select2-multiple" multiple="multiple" style="width: 100%;">
                @foreach($estadosCiviles as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
          </div>
        </div>
        <div class="mb-3 pt-3 border-top">
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Actualizar</button>
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
  @endsection
