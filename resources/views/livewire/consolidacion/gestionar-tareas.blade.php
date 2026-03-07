<div>






  <!-- crear y editar tarea  -->
  <form id="crearEditarTarea" role="form" class="forms-sample" wire:submit.prevent="guardarTarea" enctype="multipart/form-data">
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar"  tabindex="-1" id="modalcrearEditarTarea" aria-labelledby="modalcrearEditarTareaLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalcrearEditarTareaLabel">
              @if ($modoEdicion) Editar tarea @else Agregar tarea @endif
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
            <div class="mb-4">
                <span class="text-black ti-14px mb-4">Por favor ingresa toda la información.</span>
            </div>
            @csrf

            <div class="pt-3">

              <!-- tarea -->
              @if(!$modoEdicion)
              <div class="mb-3 col-12">
                <label for="tareas" class="form-label">¿Qué tarea deseas agregar?</label>
                <select id="tareas" name="tareas" wire:model.defer="tareaSeleccionada" class="form-select" >
                  <option value="">Selecciona una tarea</option>
                  @foreach ($tareas as $tareaItem )
                  <option value="{{ $tareaItem->id }}" >{{ $tareaItem->nombre }} </option>
                  @endforeach
                </select>
                @if($errors->has('tareaSeleccionada'))
                  <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $errors->first('tareaSeleccionada') }}
                  </div>
                @endif
              </div>
              @endif
              <!-- /tarea -->

              <!-- estado -->
              <div class="mb-3 col-12">
                <label for="estados" class="form-label">Selecciona el estado</label>
                <select id="estados" name="estados" wire:model.defer="estadoSeleccionado" class="form-select" >
                  <option value="">Selecciona un estado</option>
                  @foreach ($estados as $estado )
                  <option value="{{ $estado->id }}" >{{ $estado->nombre }} </option>
                  @endforeach
                </select>
                @if($errors->has('estadoSeleccionado'))
                  <div class="text-danger ti-12px mt-2">
                    <i class="ti ti-circle-x"></i> {{ $errors->first('estadoSeleccionado') }}
                  </div>
                @endif
              </div>
              <!-- /estado -->

              <div wire:ignore>
                <label for="fecha_tarea_livewire" class="form-label">Fecha</label>
                <input type="text" class="form-control" placeholder="YYYY-MM-DD" id="fecha_tarea_livewire" name="fecha_tarea"/>
              </div>
              @error('fecha_tarea')
                <div class="text-danger ti-12px mt-2">
                  <i class="ti ti-circle-x"></i> {{ $errors->first('fecha_tarea') }}
                </div>
              @enderror


              @if(!$modoEdicion)
              <hr>
              <div class="mb-4">
                <label for="detalle_tarea" class="form-label">Primera historia</label>
                <textarea class="form-control" id="detalle_tarea" name="detalle_tarea" wire:model="detalle_tarea" rows="3" placeholder="Añade una descripción o nota..."></textarea>
              </div>
              @endif

            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

  <!-- crear historia  -->
    <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar"  tabindex="-1" id="modalVerHistorial" aria-labelledby="modalVerHistorialLabel">
          <div class="offcanvas-header my-1 px-8">
              <div>
                  <h4 class="offcanvas-title fw-bold text-primary">Historia</h4>
                  @if($tareaActiva)
                      <small>{{ $tareaActiva->nombre }}</small>
                  @endif
              </div>
              <button type="button" class="btn-close text-reset" wire:click="cerrarHistorial"></button>
          </div>

          <div class="offcanvas-body pt-6 px-8">
              @forelse ($historialActivo as $registro)
                  <div class="d-flex justify-content-between align-items-start mb-4">
                      <div class="d-flex align-items-start">
                          <div class="me-3">
                              <i class="ti ti-timeline-event-text text-primary fs-3"></i>
                          </div>
                          <div>
                              <p class="mb-0 fw-semibold text-black">{{ $registro->detalle }}</p>
                              <small class="text-muted">
                                  {{ $registro->fecha }}
                                  @if($registro->creador)
                                      - por {{ $registro->creador->nombre(1) }}
                                  @endif
                              </small>
                          </div>
                      </div>

                      <div class="ms-3">
                          <a href="javascript:void(0);"
                            class="btn btn-sm btn-icon"
                            wire:click="$dispatch('confirmarEliminacionHistorial', { id: {{ $registro->id }} })"
                            title="Eliminar registro">
                            <i class="ti ti-trash text-danger"></i>
                          </a>
                      </div>
                  </div>
              @empty
                  <p class="text-muted">No hay registros de historial para esta tarea.</p>
              @endforelse
          </div>

          <div class="offcanvas-footer p-4 border-top">
              <form wire:submit.prevent="agregarNuevoHistorial">

                  <div wire:ignore class="mb-3">
                    <label for="historial_fecha_livewire" class="form-label">Fecha</label>
                    <input type="text" class="form-control" placeholder="YYYY-MM-DD" id="historial_fecha_livewire" />
                  </div>
                  @error('historialFecha')
                    <div class="text-danger ti-12px mt-2">
                      <i class="ti ti-circle-x"></i> {{ $errors->first('historialFecha') }}
                    </div>
                  @enderror

                  <div class="mb-3">
                     <label class="form-label">Describe la historia </label>
                      <textarea class="form-control"
                        wire:model="nuevoDetalleHistorial"
                        placeholder="Describe la historia..."
                        rows="2"></textarea>
                  </div>
                  @error('nuevoDetalleHistorial') <div class="text-danger ti-12px mt-1">{{ $message }}</div> @enderror
<div class="d-grid gap-2">
                      <button class="btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light" type="submit">
                        <i class="ti ti-send"></i>
                        <div wire:loading wire:target="agregarNuevoHistorial" class="spinner-border spinner-border-sm" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div>
                      </button>
</div>
              </form>
          </div>
        </div>



</div>

@script
<script>
    // Esperamos a que Livewire esté completamente cargado e inicializado
    document.addEventListener('livewire:initialized', () => {

        let flatpickrInstanceTarea;
        let flatpickrInstanceHistoria;

        const initFlatpickr = () => {
            // Inicializa la instancia para el modal de TAREAS
            flatpickrInstanceTarea = flatpickr("#fecha_tarea_livewire", {
                dateFormat: "Y-m-d",
                onClose: function(selectedDates, dateStr, instance) {
                    if (dateStr) {
                        @this.set('fecha_tarea', dateStr);
                    }
                }
            });

            // Inicializa la instancia para el modal de HISTORIAL
            flatpickrInstanceHistoria = flatpickr("#historial_fecha_livewire", {
                dateFormat: "Y-m-d",
                defaultDate: "today", // Por defecto, la fecha de hoy
                onClose: function(selectedDates, dateStr, instance) {
                    if (dateStr) {
                        @this.set('historialFecha', dateStr);
                    }
                }
            });
        };

        // Inicializamos al cargar la página
        initFlatpickr();

        $wire.on('abrirModal', data => {

           // Usamos la fecha que viene del backend para el modal de TAREAS
            if (flatpickrInstanceTarea && data.fecha) {
                // El 'true' al final dispara onClose, actualizando la propiedad en Livewire
                flatpickrInstanceTarea.setDate(data.fecha, true);
            }


          if (flatpickrInstanceHistoria) {
              // 1. Pone la fecha que viene del SERVIDOR.
              // 2. El 'true' dispara onClose, actualizando la propiedad en Livewire.
              flatpickrInstanceHistoria.setDate(data.fecha, true);
          }

          // Agregar backdrop
          const backdrop = document.createElement('div');
          backdrop.className = 'offcanvas-backdrop fade show';
          document.body.appendChild(backdrop);

          var offcanvasElement = document.getElementById(event.detail.nombreModal);
          var offcanvas = new bootstrap.Offcanvas(offcanvasElement, {
            backdrop: true
          });
          offcanvas.show();

          // Remover backdrop al cerrar
          offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
            backdrop.remove();
          });
        });

        $wire.on('cerrarModal', (event) => {
            const offcanvasElement = document.getElementById(event.nombreModal);
            if(offcanvasElement) {
                const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                if (offcanvas) {
                    offcanvas.hide();
                }
            }
        });

        // Ahora definimos todos tus listeners de Livewire
        $wire.on('msn', data => {
            Swal.fire({
                title: event.detail.msnTitulo,
                html: event.detail.msnTexto,
                icon: event.detail.msnIcono,
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        });

        $wire.on('confirmarEliminacionHistorial', (event) => {
          Swal.fire({
              title: '¿Estás seguro?',
              text: "¡No podrás revertir esta acción!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Sí, ¡eliminar!',
              cancelButtonText: 'Cancelar',
              customClass: {
                  confirmButton: 'btn btn-primary me-3',
                  cancelButton: 'btn btn-label-secondary'
              },
              buttonsStyling: false
          }).then((result) => {
              // Si el usuario confirma...
              if (result.isConfirmed) {
                  // ...enviamos el evento final al backend para que elimine el registro.
                  $wire.dispatch('eliminarHistorialConfirmado', { id: event.id });
              }
          });
        });

        window.addEventListener('confirmarEliminacionTarea', (event) => {
            Swal.fire({
                title: '¿Estás seguro de eliminar esta tarea?',
                text: "Se eliminarán también todos sus historiales. ¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger me-3', // Usamos color rojo para más énfasis
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si confirma, enviamos el evento final al componente
                    $wire.dispatch('eliminarTareaConfirmada', { id: event.detail.id });
                }
            });
        });


    });
</script>
@endscript
